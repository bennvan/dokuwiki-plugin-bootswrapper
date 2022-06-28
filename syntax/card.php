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
            'values'   => array('default', 'primary', 'success', 'info', 'warning', 'danger', 'dark', 'usyd', 'usyd-dark'),
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

        'no-border' => array(
            'type'     => 'boolean',
            'values'   => array(0, 1),
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

        'header-background' => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'header-color' => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'border-color' => array(
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
            'values'   => array('left', 'center', 'right'),
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

        global $no_body, $footer, $footer_attributes;

        if ($state == DOKU_LEXER_ENTER) {
            $type       = $attributes['type'];
            $icon       = (isset($attributes['icon']) ? $attributes['icon'] : '');
            $color      = (isset($attributes['color']) ? $attributes['color'] : false);
            $header_background = (isset($attributes['header-background']) ? $attributes['header-background'] : false);
            $header_color = (isset($attributes['header-color']) ? $attributes['header-color'] : false);
            $border_color = (isset($attributes['border-color']) ? $attributes['border-color'] : false);
            $title      = (isset($attributes['title']) ? $attributes['title'] : false);
            $footer   = (isset($attributes['footer']) ? $attributes['footer'] : false);
            $subtitle = (isset($attributes['subtitle']) ? $attributes['subtitle'] : false);
            $background = (isset($attributes['background']) ? $attributes['background'] : '');
            $no_body    = $attributes['no-body'];
            $thumbnail  = $attributes['thumbnail'];
            $link       = $attributes['link'];
            $align      = $attributes['align'];
            $no_border  = $attributes['no-border'];
            
            // set align attribute
            switch ($align) {
                case 'left':
                    $align = 'start';
                    break;
                case 'right':
                    $align = 'end';
                    break;
                default:
                    break;
            }

            # Automatic settings of params by type
            $background_class = '';
            switch ($type) {
                case 'primary':
                    $background_class = 'bg-primary-light';
                    break;
                case 'dark':
                    $background_class = '';
                    break;
                case 'usyd':
                    $background_class = 'bg-usyd-grey text-light';
                    break;
                case 'usyd-dark':
                    $background_class = '';
                default:
                    $background_class = "bg-$type";
                }

            # Set icon attribute manually or by context
            if (strtolower($icon) == 'true') {
                $icon_class = $this->getContextIcon($type);
            } else {
                $icon_class = $icon;
            }

            $html_attributes            = $this->mergeCoreAttributes($attributes);
            $html_attributes['class'][] = "bs-wrap bs-wrap-card card card-$type";

            $align = "align-items-$align justify-content-$align";

            $header_attributes['class'][] = 'card-heading '.$align;
            $footer_attributes['class'][] = 'card-footer '.$align;
            $thumbnail_attributes = [];  

            // Set the attributes
            #border
            if ($no_border) $html_attributes['class'][] = 'no-border';
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
            if ($header_background) {
                $header_attributes['style']['background'] = hsc($header_background);
                $footer_attributes['style']['background'] = hsc($header_background);
                if (!$border_color) {
                    $header_attributes['style']['border-color'] = hsc($header_background);
                    $footer_attributes['style']['border-color'] = hsc($header_background);
                    $thumbnail_attributes['style']['border-color'] = hsc($header_background);
                }
            }
            if ($header_color) {
                $header_attributes['style']['color'] = hsc($header_color);
                $footer_attributes['style']['color'] = hsc($header_color);
            }
            if ($border_color) {
                $html_attributes['style']['border-color'] = hsc($border_color);
                $header_attributes['style']['border-color'] = hsc($border_color);
                $footer_attributes['style']['border-color'] = hsc($border_color);
                $thumbnail_attributes['style']['border-color'] = hsc($border_color);
            }

            # link attribute
            if ($link) {
                $html_attributes['class'][] = "card-link";
            }  

            // Prepare the section edit
            if (defined('SEC_EDIT_PATTERN')) { // for DokuWiki Greebo and more recent versions
                $secidclass = $renderer->startSectionEdit($pos, array('target' => 'plugin_bootswrapper_card', 'name' => $state));
            } else {
                $secidclass = $renderer->startSectionEdit($pos, 'plugin_bootswrapper_card', $state);
            }
            $html_attributes['class'][] = $secidclass;           

            // Start generating HTML
            $markup = '<div ' . $this->buildAttributes($html_attributes) . '>';

            // Place the link
            if ($link) {
                list($url, $exists) = $this->resolveLinkUrl($link, $renderer);
                if ($exists) {
                    $attrs = 'class="wikilink1" ';
                    if ($link['type'] == 'externallink') {
                        $attrs .= 'target="_blank" rel="ugc nofollow noopener"';
                    }
                } else {
                    $attrs = 'rel="ugc nofollow noopener" ';
                    if ($link['type'] != 'externallink') {
                        $attrs .= 'class="wikilink2"';
                    }

                }
                $markup .= '<a href="' . $url . '" '.$attrs.'></a>';     
            }

            // Place the thumbnail image
            if ($thumbnail) {
                list($url, $exists) = $this->resolveMediaUrl($thumbnail, $renderer);
                $markup .= '<span class="card-thumbnail"><img src="'.$url.'" class="media" alt="" '.$this->buildAttributes($thumbnail_attributes).' ></span>';
            }

            // Place the Title
            if ($title || $subtitle) {

                if ($icon && $icon_class) {
                    $title = '<i class="iconify" data-icon="' . $icon_class . '"></i> ' . $title;
                }

                $markup .= '<div ' . $this->buildAttributes($header_attributes) . '><h4 class="card-title" >' . $title . '</h4>'.$subtitle.'</div>';
            } 

            if (!$no_body) {
                $markup .= '<div class="card-body">';
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
                $markup .= '<div ' . $this->buildAttributes($footer_attributes) . '><h4 class="card-title" >' . $footer . '</h4></div>';
            }

            $markup .= '</div>';
            $renderer->doc .= $markup;
            $renderer->finishSectionEdit($pos + strlen($match));

            return true;
        }

        return true;
    }
}
