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
    public $pattern_start    = '<page-nav.*?>\s(?=.*?</page-nav>)';
    public $pattern_end      = '</page-nav>';
    public $tag_name         = 'page-nav';
    public $tag_attributes   = array(

        'type'      => array(
            'type'     => 'integer',
            'values'   => array(2, 4, 6, 8, 10, 12),
            'required' => true,
            'default'  => 14),

        'prev'  => array(
            'type'     => 'link',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'next'  => array(
            'type'     => 'link',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'regex'  => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => false),

    );

     /** @inheritDoc */
    public function render($format, Doku_Renderer $renderer, $data)
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

            $glob = $attributes['regex'];
            $mode = $attributes['type'];

            $glob = preg_quote($glob, '/');


            // get all files in current namespace
            static $list = null; // static to reuse the array for multiple calls.
            if (is_null($list)) {
                $list = array();
                $ns = str_replace(':', '/', getNS($INFO['id']));
                search($list, $conf['datadir'], 'search_list', array(), utf8_encodeFN($ns));
            }
            $id = $INFO['id'];

            // find the start page
            $exist = false;
            $start = getNS($INFO['id']) . ':';
            resolve_pageid('', $start, $exist);

            $cnt = count($list);
            if ($cnt < 2) return true; // there are no other doc in this namespace

            $first = '';
            $prev = '';
            $last = '';
            $next = '';
            $self = false;

            // we go through the list only once, handling all options and globs
            // only for the 'last' command the whole list is iterated
            for ($i = 0; $i < $cnt; $i++) {
                if ($list[$i]['id'] == $id) {
                    $self = true;
                } else {
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
                        if (!$first) {
                            $first = $list[$i]['id'];
                        }
                        $prev = $list[$i]['id'];
                    }
                }
            }

            $renderer->doc .= '<p class="plugin__pagenav">';
            if ($mode & 4) $renderer->doc .= $this->buildImgLink($first, 'first');
            if ($mode & 2) $renderer->doc .= $this->buildImgLink($prev, 'prev');
            if ($mode & 8) $renderer->doc .= $this->buildImgLink($start, 'up');
            if ($mode & 2) $renderer->doc .= $this->buildImgLink($next, 'next');
            if ($mode & 4) $renderer->doc .= $this->buildImgLink($last, 'last');
            $renderer->doc .= '</p>';
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
    protected function buildImgLink($page, $cmd)
    {
        $img = inlineSVG(__DIR__ . '/img/' . $cmd . '.svg');

        // no page, gray out item
        if (blank($page)) {
            return '<span class="' . $cmd . '">' . $img . '</span>';
        }


        $title = p_get_first_heading($page);
        $attr = [
            'href' => wl($page),
            'title' => $this->getLang($cmd) . ': ' . hsc($title),
            'class' => 'wikilink1 ' . $cmd,
        ];
        return '<a ' . buildAttributes($attr) . '>' . $img . '</a>';
    }
}
