<?php
/**
 * Bootstrap Wrapper Plugin: Thumbnail
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2020, Giuseppe Di Terlizzi
 */

class syntax_plugin_bootswrapper_thumbnail extends syntax_plugin_bootswrapper_bootstrap
{

    public $p_type         = 'normal';
    public $pattern_start  = '<thumbnail>';
    public $pattern_end    = '</thumbnail>';
    public $tag_name       = 'thumbnail';

     /**
     * Static variables set to keep track when scope is left.
     */
    private static $_incaption = false;

    public function postConnect() {
        parent::postConnect();
        $this->Lexer->addPattern('<caption>', 'plugin_bootswrapper_thumbnail');
        $this->Lexer->addPattern('</caption>', 'plugin_bootswrapper_thumbnail');
    }

    public function render($mode, Doku_Renderer $renderer, $data) {

        if (empty($data)) {
            return false;
        }

        if ($mode !== 'xhtml') {
            return false;
        }

        // * @var Doku_Renderer_xhtml $renderer 
        list($state, $match) = $data;

        if ($state == DOKU_LEXER_ENTER) {
            $renderer->doc .= '<div class="bs-wrap bs-wrap-thumbnail thumbnail">';
            return true;  
        }

        if ($state == DOKU_LEXER_MATCHED) {
            $this::$_incaption = !$this::$_incaption;
            // Rendering a caption
            if ($this::$_incaption) {
                $renderer->doc .= '<div class="bs-wrap bs-wrap-caption caption">';
            } 
            else {
                $renderer->doc .= '</div>';
            }
            return true;
        }

        if ($state == DOKU_LEXER_EXIT) {
            $renderer->doc .= '</div>';
            return true;
        }
    }

}
