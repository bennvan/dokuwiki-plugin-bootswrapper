<?php
/**
 * Bootstrap Wrapper Plugin: Caption
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2020, Giuseppe Di Terlizzi
 */

class syntax_plugin_bootswrapper_bscaption extends syntax_plugin_bootswrapper_bootstrap
{
    public $p_type         = 'block';
    public $pattern_start  = '<bscaption>';
    public $pattern_end    = '</bscaption>';
    public $template_start = '<div class="bs-wrap bs-wrap-caption caption">';
    public $template_end   = '</div>';
    public $tag_name       = 'bscaption';
}
