<?php
/**
 * Bootstrap Wrapper Plugin: Panel
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2020, Giuseppe Di Terlizzi
 */

class syntax_plugin_bootswrapper_panel extends syntax_plugin_bootswrapper_bootstrap
{

    public $pattern_start  = '<panel.*?>(?=.*?</panel>)';
    public $pattern_end    = '</panel>';
    public $tag_name       = 'panel';
    public $tag_attributes = array(

        'type'     => array(
            'type'     => 'string',
            'values'   => array('default', 'primary', 'success', 'info', 'warning', 'danger', 'usyd'),
            'required' => true,
            'default'  => 'default'),

        'title'    => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'footer'   => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'subtitle' => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'icon'     => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'no-body'  => array(
            'type'     => 'boolean',
            'values'   => array(0, 1),
            'required' => false,
            'default'  => false),

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
            'values'   => array('left', 'center', 'right'),
            'required' => true,
            'default'  => 'left'),

    );

    public function render($mode, Doku_Renderer $renderer, $data)
    {

        if (empty($data)) {
            return false;
        }

        if ($mode !== 'xhtml') {
            return false;
        }

        /** @var Doku_Renderer_xhtml $renderer */
        list($state, $match, $pos, $attributes) = $data;

        global $nobody, $footer, $align;

        if ($state == DOKU_LEXER_ENTER) {
            $type     = $attributes['type'];
            $title    = (isset($attributes['title']) ? $attributes['title'] : false);
            $footer   = (isset($attributes['footer']) ? $attributes['footer'] : false);
            $subtitle = (isset($attributes['subtitle']) ? $attributes['subtitle'] : false);
            $icon     = (isset($attributes['icon']) ? $attributes['icon'] : false);
            $nobody   = (isset($attributes['no-body']) ? $attributes['no-body'] : false);
            $color      = (isset($attributes['color']) ? $attributes['color'] : false);
            $background = (isset($attributes['background']) ? $attributes['background'] : '');
            $align      = $attributes['align'];
            
            $align = "text-$align";

            # Automatic settings of params by type
            $background_class = '';
            switch ($type) {
                case 'primary':
                    $background_class = 'bg-primary-callout';
                    break;
                case 'usyd':
                    $background_class = 'bg-usyd-grey text-light';
                    break;
                default:
                    $background_class = "bg-$type";
                }

            // Set the attributes
            $html_attributes            = $this->mergeCoreAttributes($attributes);
            $html_attributes['class'][] = "bs-wrap bs-wrap-panel panel panel-$type";

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

            // Prepare the section edit
            if (defined('SEC_EDIT_PATTERN')) { // for DokuWiki Greebo and more recent versions
                $secidclass = $renderer->startSectionEdit($pos, array('target' => 'plugin_bootswrapper_panel', 'name' => $state));
            } else {
                $secidclass = $renderer->startSectionEdit($pos, 'plugin_bootswrapper_panel', $state);
            }
            $html_attributes['class'][] = $secidclass; 

            // Start generating HTML
            $markup = '<div ' . $this->buildAttributes($html_attributes) . '>';


            if ($title || $subtitle) {

                if ($icon) {
                    $title = '<i class="' . $icon . '"></i> ' . $title;
                }

                $markup .= '<div class="panel-heading '.$align.'"><h4 class="panel-title">' . $title . '</h4>' . $subtitle . '</div>';

            }

            if (!$nobody) {
                $markup .= '<div class="panel-body">';
            }

            $renderer->doc .= $markup;

            return true;
        }

        if ($state == DOKU_LEXER_EXIT) {
            $markup = '';

            if (!$nobody) {
                $markup = '</div>';
            }

            if ($footer) {
                $markup .= '<div class="panel-footer '.$align.'">' . $footer . '</div>';
            }

            $markup .= '</div>';
            $renderer->doc .= $markup;

            $renderer->finishSectionEdit($pos + strlen($match));

            return true;
        }

        return true;
    }
}
