<?php
/**
 * Bootstrap Wrapper Plugin: Callout
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Ben van Magill, 
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2020, Giuseppe Di Terlizzi
 */

class syntax_plugin_bootswrapper_ribbon extends syntax_plugin_bootswrapper_bootstrap
{

    public $p_type         = 'block';
    public $pattern_start  = '<ribbon.*?>(?=.*?</ribbon>)';
    public $pattern_end    = '</ribbon>';
    public $tag_name       = 'ribbon';
    public $tag_attributes = array(

        'type'  => array(
            'type'     => 'string',
            'values'   => array('flat', 'up', 'down', 'slant-up', 'slant-down', 'check', 'primary', 'success', 'info', 'warning', 'danger', 'tip', 'question', 'cogs'),
            'required' => true,
            'default'  => 'up'),

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

        'top'  => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'right'  => array(
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
            $background = (isset($attributes['background']) ? $attributes['background'] : '');
            $top        = (isset($attributes['top']) ? $attributes['top'] : false);
            $right      = (isset($attributes['right']) ? $attributes['right'] : false);

            $icon_class       = '';
            $text_color       = '';
            $inner_text       = '';

            $html_attributes            = $this->mergeCoreAttributes($attributes);
            $content_attributes         = array();
            $html_attributes['class'][] = 'ribbon';
            $content_attributes['class'][] = 'content';

            # Automatic settings of params by type
            switch ($type) {
                case 'primary':                
                    $type = 'up';
                    $icon_class = 'exclamation-circle';
                    $background = '#428bca';
                    break;
                case 'success':
                    $type = 'check';
                    $icon_class = 'check';
                    $background = 'green'; //'#41ad49';
                    break;
                case 'info':
                    $type = 'up';
                    $icon_class = 'info-circle';
                    $background = '#5bc0de';
                    break;
                case 'warning':
                    $type = 'up';
                    $icon_class = 'exclamation-triangle';
                    $background = '#f0ad4e';
                    break;
                case 'danger':
                    $type = 'up';
                    $icon_class = 'minus-circle';
                    $background = '#d9534f';
                    break;
                case 'question':
                    $type       = 'up';
                    $icon_class = 'question-circle';
                    $background = '#428bca';
                    break;
                case 'tip':
                    $type       = 'up';
                    $icon_class = 'lightbulb-o';
                    $background = '#f0ad4e';
                    break;
                case 'cogs':
                    $type = 'up';
                    $icon_class = 'cogs';
                    $background = 'orange';
                    break;

                default:
                    break;
                }

            # If icon is auto set or present
            if (strtolower($icon) == 'true') {
                $icon_class = "fa:$icon_class";
            }
            else {
                $icon_class = $icon;
            }

            # Bg attribute 
            if ($background) {
                $content_attributes['style']['background'] = hsc($background) .';';
            }

            if ($color) {
                $text_color = ' style="color:' . hsc($color) . '"';
                $content_attributes['style']['color'] = hsc($color) .';';
            }

            if ($right) {
                $html_attributes['style']['right'] = hsc($right) .';';
            }

            if ($top) {
                $html_attributes['style']['top'] = hsc($top) .';';
            }

            $html_attributes['class'][] = $type;

            $markup = '<div ' . $this->buildAttributes($html_attributes) . '><div '. $this->buildAttributes($content_attributes) . '>';

            if ($icon_class) {
                $markup .= '<span><i class="iconify" data-icon="' . $icon_class . '"' . $text_color . '></i></span></br>';
            }

            $renderer->doc .= $markup;
            return true;
        }

        if ($state == DOKU_LEXER_EXIT) {
            $markup = '</div></div>';

            $renderer->doc .= $markup;
            return true;
        }

        return true;
    }
}
