<?php
/**
 * Bootstrap Wrapper Plugin: Callout
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2020, Giuseppe Di Terlizzi
 */

class syntax_plugin_bootswrapper_card extends syntax_plugin_bootswrapper_bootstrap
{

    public $pattern_start  = '<card.*?>\s(?=.*?</card>)';
    public $pattern_end    = '</card>';
    public $tag_name       = 'card';
    public $tag_attributes = array(

        'type'  => array(
            'type'     => 'string',
            'values'   => array('default', 'primary', 'success', 'info', 'warning', 'danger', 'usyd'),
            'required' => true,
            'default'  => 'default'),

        'icon'  => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'title' => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'subtitle' => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'footer'   => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'no-body'  => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => false),

        'thumbnail' => array(
            'type'     => 'media',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'link'  => array(
            'type'     => 'link',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'color' => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'background' => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'align'  => array(
            'type'     => 'string',
            'values'   => array('start', 'center', 'end'),
            'required' => true,
            'default'  => 'center'),

    );

    public function render($mode, Doku_Renderer $renderer, $data)
    {
        if (empty($data)) {
            return false;
        }

        /** @var Doku_Renderer_xhtml $renderer */
        if ($mode !== 'xhtml') {
            return false;
        }

        list($state, $match, $pos, $attributes) = $data;

        global $no_body, $footer, $align;

        if ($state == DOKU_LEXER_ENTER) {
            $type       = $attributes['type'];
            $icon       = (isset($attributes['icon']) ? $attributes['icon'] : '');
            $color      = (isset($attributes['color']) ? $attributes['color'] : false);
            $title      = (isset($attributes['title']) ? $attributes['title'] : false);
            $footer   = (isset($attributes['footer']) ? $attributes['footer'] : false);
            $subtitle = (isset($attributes['subtitle']) ? $attributes['subtitle'] : false);
            $background = (isset($attributes['background']) ? $attributes['background'] : '');
            $no_body    = $attributes['no-body'];
            $thumbnail  = $attributes['thumbnail'];
            $link       = $attributes['link'];
            $align      = $attributes['align'];

            # Automatic settings of params by type
            $background_class = '';
            switch ($type) {
                case 'primary':
                    $background_class = 'bg-primary-light';
                    break;
                case 'usyd':
                    $background_class = 'bg-usyd-grey text-light';
                    break;
                default:
                    $background_class = "bg-$type";
                }

            $html_attributes            = $this->mergeCoreAttributes($attributes);
            $html_attributes['class'][] = "bs-wrap bs-wrap-card card card-$type";

            $align = "align-items-$align justify-content-$align";

            // Set the attributes
            # background 
            # Bg attribute 
            if (strtolower($background) == 'true') {
                $html_attributes['class'][] = $background_class;
            } elseif (!empty($background)) {
                $html_attributes['style']['background'] = hsc($background);
            }

            // Color
            if ($color) {
                $html_attributes['style']['color'] = hsc($color);
            }

            # link attribute
            if ($link) {
                $html_attributes['class'][] = "card-link";
            } 
            

            // Start generating HTML
            $markup = '<div ' . $this->buildAttributes($html_attributes) . '>';

            // Place the link
            if ($link) {
                list($url, $exists) = $this->resolveLinkUrl($link, $renderer);
                if ($exists) {
                    $attrs = 'class="wikilink1"';
                } else {

                    $attrs = 'rel="nofollow" ';

                    if (!$is_external) {
                        $attrs .= 'class="wikilink2"';
                    }

                }
                $markup .= '<a href="' . $url . '" '.$attrs.'></a>';     
            }

            // Place the thumbnail image
            if ($thumbnail) {
                list($url, $exists) = $this->resolveMediaUrl($thumbnail, $renderer);
                $markup .= '<span class="card-thumbnail"><img src="'.$url.'" class="media" alt=""></span>';
            }

            // Place the Title
            if ($title || $subtitle) {

                if ($icon) {
                    $title = '<i class="' . $icon . '"></i> ' . $title;
                }

                $markup .= '<div class="card-heading '.$align.'"><h4 class="card-title" >' . $title . '</h4>'.$subtitle.'</div>';
            } 

            if (!$no_body) {
                $markup .= '<div class="card-body">';
            }

            // Place the edit buttons 
            if (defined('SEC_EDIT_PATTERN')) { // for DokuWiki Greebo and more recent versions
                $renderer->startSectionEdit($pos, array('target' => 'plugin_bootswrapper_card', 'name' => $state));
            } else {
                $renderer->startSectionEdit($pos, 'plugin_bootswrapper_card', $state);
            }

            $renderer->doc .= $markup;
            return true;
        }

        if ($state == DOKU_LEXER_EXIT) {
            $markup = '';

            if (!$no_body) {
                $markup .= '</div>';
            }

            if ($footer) {
                $markup .= '<div class="card-footer '.$align.'">' . $footer . '</div>';
            }

            $markup .= '</div>';

            $renderer->doc .= $markup;

            $renderer->finishSectionEdit($pos + strlen($match));

            return true;
        }

        return true;
    }
}
