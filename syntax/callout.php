<?php
/**
 * Bootstrap Wrapper Plugin: Callout
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2020, Giuseppe Di Terlizzi
 */

class syntax_plugin_bootswrapper_callout extends syntax_plugin_bootswrapper_bootstrap
{

    public $p_type         = 'block';
    public $pattern_start  = '<callout.*?>(?=.*?</callout>)';
    public $pattern_end    = '</callout>';
    public $tag_name       = 'callout';
    public $tag_attributes = array(

        'type'  => array(
            'type'     => 'string',
            'values'   => array('default', 'primary', 'success', 'info', 'warning', 'danger', 'question', 'tip'),
            'required' => true,
            'default'  => 'default'),

        'title' => array(
            'type'     => 'string',
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

        'icon'  => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),

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

        global $icon;

        if ($state == DOKU_LEXER_ENTER) {
            $type       = $attributes['type'];
            $icon       = (isset($attributes['icon']) ? $attributes['icon'] : '');
            $color      = (isset($attributes['color']) ? $attributes['color'] : false);
            $title      = (isset($attributes['title']) ? $attributes['title'] : false);
            $background = (isset($attributes['background']) ? $attributes['background'] : '');

            // Match the Icons plugin's ?pixel-size shorthand, including true?64.
            [$icon, $requested_size] = array_pad(explode('?', $icon, 2), 2, '');
            $icon_size = filter_var($requested_size, FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1, 'max_range' => 512]]) ?: 32;

            $icon_class       = '';
            $text_color       = '';
            $callout_color    = '';
            $background_class = '';

            $html_attributes            = $this->mergeCoreAttributes($attributes);
            $html_attributes['class'][] = 'bs-wrap bs-callout';

            # Automatic settings of params by type
            switch ($type) {
                case 'success':
                    $icon_class = 'circle-check';
                    $background_class = 'success';
                    break;

                case 'info':
                    $icon_class = 'circle-info';
                    $background_class = 'info';
                    break;

                case 'danger':
                    $icon_class = 'circle-minus';
                    $background_class = 'danger';
                    break;

                case 'primary':
                    $icon_class = 'circle-exclamation';
                    $background_class = 'primary-light';
                    break;

                case 'warning':
                    $icon_class = 'triangle-exclamation';
                    $background_class = 'warning';
                    break;

                // Extra
                case 'question':
                    $type       = 'primary';
                    $icon_class = 'circle-question';
                    $background_class = 'primary-light';
                    break;

                case 'tip':
                    $type       = 'warning';
                    $icon_class = 'lightbulb';
                    $background_class = 'warning';
                    break;

                default:
                    $icon_class = '';
                }

            # Icon attribute empty so defualt is to show type icon
            if (strtolower($icon) == 'true') {
                $icon_class = $icon_class !== '' ? "fa:$icon_class" : '';
            } else {
                $icon_class = $icon;
            }

            // Only close the icon columns when an icon was actually added.
            $icon = $icon_class;


            # Bg attribute 
            if (strtolower($background) == 'true') {
                $html_attributes['class'][] = "bg-$background_class";
            } elseif (!empty($background)) {
                $html_attributes['style']['background'] = hsc($background);
            }

            if ($color) {
                $html_attributes['style']['border-left-color'] = hsc($color);
                $text_color                                    = ' style="color:' . hsc($color) . '"';
            }

            $html_attributes['class'][] = "bs-callout-$type";

            $markup = '<div ' . $this->buildAttributes($html_attributes) . '>';


            if ($icon && $icon_class) {
                $icon_markup = $this->renderIcon($icon_class, $renderer, 'bs-callout-icon', $icon_size . 'px', $color,
                    $this->getPdfContextColor($type));
                $icon_style = $body_style = '';
                if ($renderer instanceof renderer_plugin_dw2pdf) {
                    if ($icon_size !== 32) {
                        $icon_style = ' style="width:' . $icon_size . 'px"';
                        $body_style = ' style="float:none;width:auto;margin-left:' . ($icon_size + 8) . 'px"';
                    }
                }
                $markup .= '<div class="row"><div class="col-xs-1"' . $icon_style . '>'
                    . $icon_markup . '</div><div class="col-xs-11"' . $body_style . '>';
            }

            if ($title) {
                $markup .= '<h4' . $text_color . '>' . $title . '</h4>';
            }

            $renderer->doc .= $markup;
            return true;
        }

        if ($state == DOKU_LEXER_EXIT) {
            $markup = '</div>';

            if ($icon) {
                $markup .= '</div></div>';
            }

            $renderer->doc .= $markup;
            return true;
        }

        return true;
    }
}
