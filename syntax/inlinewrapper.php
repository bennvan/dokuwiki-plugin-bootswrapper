<?php
/**
 * Inline parser mode for <wrapper inline="true">.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
class syntax_plugin_bootswrapper_inlinewrapper extends syntax_plugin_bootswrapper_wrapper
{
    public $p_type = 'normal';
    public $pattern_start = '<wrapper(?=' . self::INLINE_ATTRIBUTES . ')[^>]*>(?=.*?</wrapper>)';
    // The popup exposes the shared wrapper entry, not this internal mode.
    public $tag_name = null;
    protected $html_tag = 'span';

    public function getAllowedTypes()
    {
        return array('formatting', 'substition', 'disabled');
    }

    public function accepts($mode)
    {
        // A block wrapper cannot be contained by an inline span.
        return $mode !== 'plugin_bootswrapper_wrapper' && parent::accepts($mode);
    }

    public function postConnect()
    {
        // Inline content does not introduce block headings.
        $this->Lexer->addExitPattern($this->pattern_end, 'plugin_bootswrapper_inlinewrapper');
    }
}
