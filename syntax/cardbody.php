<?php
/**
 * Bootstrap Wrapper Plugin: Panel Body
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2020, Giuseppe Di Terlizzi
 */

class syntax_plugin_bootswrapper_cardbody extends syntax_plugin_bootswrapper_bootstrap
{

    public $p_type         = 'block';
    public $pattern_start  = '<card-body>';
    public $pattern_end    = '</card-body>';
    public $template_start = '<div class="bs-wrap card-body">';
    public $template_end   = '</div>';
    public $tag_name       = 'card-body';

}
