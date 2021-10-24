<?php
/**
 * Bootstrap Wrapper Plugin: Badge
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2020, Giuseppe Di Terlizzi
 */

class syntax_plugin_bootswrapper_badge extends syntax_plugin_bootswrapper_bootstrap
{

    public $p_type         = 'normal';
    public $pattern_start  = '<(?:BADGE|badge).*?>(?=.*?</(?:BADGE|badge)>)';
    public $pattern_end    = '</(?:BADGE|badge)>';
    public $tag_name       = 'badge';
    public $tag_attributes = array(

        'type'  => array(
            'type'     => 'string',
            'values'   => array('default', 'primary', 'success', 'info', 'warning', 'danger', 'light', 'dark'),
            'required' => true,
            'default'  => 'default'),

        'icon' => array(
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

        global $badge_tag;

        if ($state == DOKU_LEXER_ENTER) {
            $badge_tag  = (($is_block) ? 'div' : 'span');
            $type       = $attributes['type'];
            $color      = (isset($attributes['color']) ? $attributes['color'] : false);
            $background = (isset($attributes['background']) ? $attributes['background'] : '');
            $icon       = $attributes['icon'];

            $html_attributes            = $this->mergeCoreAttributes($attributes);
            $html_attributes['class'][] = 'bs-wrap bs-wrap-badge badge badge-'.$type;

            # Bg attribute 
            if ($background) {
                $html_attributes['style']['background-color'] = hsc($background) .';';
            }

            # text color
            if ($color) {
                $html_attributes['style']['color'] = hsc($color) . ';';
            }

            $markup = '<'.$badge_tag . ' ' . $this->buildAttributes($html_attributes) . '>';

            if ($icon) {
                $markup .= '<i class="' . $icon . '"></i> ';
            }

            $renderer->doc .= $markup;
            return true;
        }

        if ($state == DOKU_LEXER_EXIT) {
            $markup = "</$badge_tag>";

            $renderer->doc .= $markup;
            return true;
        }

        return true;
    }

}
