<?php
/**
 * Bootstrap Wrapper Plugin: Grid
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2020, Giuseppe Di Terlizzi
 */

class syntax_plugin_bootswrapper_grid extends syntax_plugin_bootswrapper_bootstrap
{

    public $p_type         = 'block';
    public $pattern_start  = '<grid>';
    public $pattern_end    = '</grid>';
    public $template_start = '<div class="bs-wrap bs-wrap-grid grid" >';
    public $template_end   = '</div>';
    public $tag_name       = 'grid';

    public function render($mode, Doku_Renderer $renderer, $data)
    {
        if ($mode === 'xhtml' && $renderer instanceof renderer_plugin_dw2pdf &&
            ($data[0] ?? null) === DOKU_LEXER_EXIT) {
            // mPDF supports floats, but not Bootstrap's clearfix pseudo-element.
            $renderer->doc .= '<div style="clear:both"></div></div>';
            return true;
        }
        return parent::render($mode, $renderer, $data);
    }

}
