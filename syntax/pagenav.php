<?php
/**
 * Bootstrap Wrapper Plugin: Pagenav
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Ben van Magill <ben.vanmagill16@gmail.com>
 * @author     Andreas Gohr <gohr@cosmocode.de>
 */

class syntax_plugin_bootswrapper_pagenav extends syntax_plugin_bootswrapper_bootstrap
{

    public $p_type           = 'block';
    public $pattern_start    = '<page-nav.*?>\s*(?=.*?</page-nav>)';
    public $pattern_end      = '</page-nav>';
    public $tag_name         = 'page-nav';
    public $tag_attributes   = array(

        'start'        => array(
            'type'     => 'boolean',
            'values'   => array(0, 1),
            'required' => false,
            'default'  => true),

        'prev'  => array(
            'type'     => 'link',
            'values'   => null,
            'required' => true,
            'default'  => ''),

        'next'  => array(
            'type'     => 'link',
            'values'   => null,
            'required' => true,
            'default'  => ''),

        'regex'  => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => false),

    );

     /** @inheritDoc */
    public function render($mode, Doku_Renderer $renderer, $data)
    {
        global $INFO;
        global $conf;
        
        if (empty($data)) {
            return false;
        }

        /** @var Doku_Renderer_xhtml $renderer */
        if ($mode !== 'xhtml') {
            return false;
        }

        list($state, $match, $pos, $attributes) = $data;
        if ($state == DOKU_LEXER_ENTER) {

            $glob       = $attributes['regex'];
            $show_start = $attributes['start'];
            $next       = $attributes['next'];
            $prev       = $attributes['prev'];
            $last       = '';

            $glob = preg_quote($glob, '/');


            // get all files in current namespace
            static $list = null; // static to reuse the array for multiple calls.
            if (is_null($list)) {
                $list = array();
                $ns = str_replace(':', '/', getNS($INFO['id']));
                search($list, $conf['datadir'], 'search_list', array(), utf8_encodeFN($ns));
            }
            $id = $INFO['id'];

            // find the start page and prepend to list (start is always first)
            $exist = false;
            $ns    = getNS($INFO['id']);
            $start = $ns . ':';
            resolve_pageid($ns, $start, $exist);
            if ($exist) {
                array_unshift($list, array('id' => $start));
            }
            $cnt = count($list);

            // Set custom defined ID's
            $cust_prev = false;
            if ($next) {
                $next = $next['src'];
                resolve_pageid($ns, $next, $exists);
            }
            if ($prev) {
                $prev = $prev['src'];
                resolve_pageid($ns, $prev, $exists);
                $cust_prev = true;
            } else {
                $prev = $start;
            }

            // we go through the list only once, handling all options and globs
            // only for the 'last' command the whole list is iterated
            $self = false;
            for ($i = 0; $i < $cnt; $i++) {
                if ($list[$i]['id'] == $id) {
                    $self = true;
                    continue;
                }
                if ($glob && !preg_match('/' . $glob . '/', noNS($list[$i]['id']))) continue;
                if ($list[$i]['id'] == $start) continue;
                if (isHiddenPage($list[$i]['id'])) continue;

                if ($self) {
                    // we're after the current id
                    if (!$next) {
                        $next = $list[$i]['id'];
                    }
                    $last = $list[$i]['id'];
                } else {
                    // we're before the current id
                    if (!$start) {
                        $start = $list[$i]['id'];
                    }
                    if (!$cust_prev) {
                        $prev = ($id != $start)? $list[$i]['id'] : '';
                    }
                }
            }

            $start_txt = ($id == $start)? ' ('. $this->getLang('start') . ')' : '';
            $renderer->doc .= '<div class="bs-wrap-pagenav">';
            $renderer->doc .= '<div class="bs-wrap-pagenav-current"> <b>' . $this->getLang('current'). ':</b> ';
            $renderer->doc .= hsc(p_get_first_heading($id)) . $start_txt . '</div>';

            $renderer->doc                  .= $this->buildImgLink($prev, 'prev', 'arrow-left', true);
            if ($show_start) $renderer->doc .= $this->buildImgLink($start, 'start', 'bars', true);
            $renderer->doc                  .= $this->buildImgLink($next, 'next', 'arrow-right');
            $renderer->doc                  .= '</div>';
        }
        return true;
    }

    /**
     * Builds the link using an SVG image
     *
     * @param string $page page to link to
     * @param string $cmd
     * @return string
     */
    protected function buildImgLink($page, $cmd, $icon_cls, $reverse=false)
    {
        global $ID; 
        $icon = ($icon_cls)? '<i class="fa fa-fw fa-'.$icon_cls.'"></i>' : '';

        $txt = $this->getLang($cmd);
        $desc = '<span class="bs-wrap-pagenav-desc">'. $txt . '</span>';
        $desc = (!$reverse)  ? $desc . $icon : $icon . $desc;

        // no page, gray out item
        if (blank($page) || $page == $ID) {
            return '<button class="btn btn-default disabled ' . $cmd . '">' . $desc . '</button>';
        }


        $title = p_get_first_heading($page);
        
        $attr = [
            'href' => wl($page),
            'title' => $this->getLang($cmd) . ': ' . hsc($title),
            'class' => 'btn btn-primary wikilink1 ' . $cmd,
        ];
        if ($cmd == 'current') {
            $txt = $title;
            $attr['class'] = 'btn btn-default ' . $cmd;
            $attr['disabled'] = true; 
        }
        return '<a ' . buildAttributes($attr) . '>' . $desc . '</a>';
    }
}
