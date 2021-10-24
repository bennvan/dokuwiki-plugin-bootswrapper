<?php
/**
 * Bootstrap Wrapper Plugin: Generic Wrapper (span or div)
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2020, Giuseppe Di Terlizzi
 */

class syntax_plugin_bootswrapper_wrapper extends syntax_plugin_bootswrapper_bootstrap
{

    public $pattern_start = '<(?:WRAPPER|wrapper).*?>(?=.*?</(?:WRAPPER|wrapper)>)';
    public $pattern_end   = '</(?:WRAPPER|wrapper)>';
    public $tag_name      = 'wrapper';

    public $tag_attributes = array(

        'screen' => array(
            'type'     => 'boolean',
            'values'   => array(0, 1),
            'required' => false,
            'default'  => false),

        'print'  => array(
            'type'     => 'boolean',
            'values'   => array(0, 1),
            'required' => false,
            'default'  => false),

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
        list($state, $match, $pos, $attributes, $is_block) = $data;

        global $wrapper_tag;

        if ($state == DOKU_LEXER_ENTER) {
            $wrapper_tag                    = ($is_block) ? 'div' : 'span';
            $html_attributes                = $this->mergeCoreAttributes($attributes);
            $html_attributes['class'][]     = 'bs-wrapper';
            $styles                         = (($attributes['style']) ? 'style="'.$attributes['style'].'"' : '');
            $markup             = '<'.$wrapper_tag.' '.$this->buildAttributes($html_attributes).' '.$styles.'>';

            $renderer->doc .= $markup;

            return true;
        }

        if ($state == DOKU_LEXER_EXIT) {
            $renderer->doc .= "</$wrapper_tag>";
            return true;
        }

        return false;
    }
}
