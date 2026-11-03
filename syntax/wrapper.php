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

    // Match a real inline attribute, skipping quoted values of earlier attributes.
    const INLINE_ATTRIBUTES = '(?:\s+[\w:-]+\s*=\s*(?:"[^"]*"|\'[^\']*\'))*?\s+inline\s*=\s*(?:"(?i:true|1)"|\'(?i:true|1)\')(?=\s|>)';

    public $pattern_start = '<wrapper(?!' . self::INLINE_ATTRIBUTES . ')(?:\s[^>]*)?>(?=.*?</wrapper>)';
    public $pattern_end   = '</wrapper>';
    public $tag_name      = 'wrapper';
    protected $html_tag   = 'div';

    public $tag_attributes = array(

        'inline' => array(
            'type'     => 'boolean',
            'values'   => array(0, 1),
            'required' => false,
            'default'  => false),

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

    public function accepts($mode)
    {
        // Syntax plugins normally exclude themselves from nested content.
        return $mode === 'plugin_bootswrapper_' . $this->getPluginComponent() || parent::accepts($mode);
    }

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

        if ($state == DOKU_LEXER_ENTER) {
            $html_attributes                = $this->mergeCoreAttributes($attributes);
            $html_attributes['class'][]     = 'bs-wrapper';
            $markup             = '<'.$this->html_tag.' '.$this->buildAttributes($html_attributes).'>';

            $renderer->doc .= $markup;

            return true;
        }

        if ($state == DOKU_LEXER_EXIT) {
            $renderer->doc .= '</'.$this->html_tag.'>';
            return true;
        }

        return false;
    }
}
