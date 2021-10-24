<?php
/**
 * Bootstrap Wrapper Plugin: Carousel
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2020, Giuseppe Di Terlizzi
 */

class syntax_plugin_bootswrapper_carousel extends syntax_plugin_bootswrapper_bootstrap
{

    public $p_type         = 'normal';
    public $pattern_start  = '<carousel.*?>(?=.*?</carousel>)';
    public $pattern_end    = '</carousel>';
    public $tag_name       = 'carousel';
    public $tag_attributes = array(

        'interval' => array(
            'type'     => 'integer',
            'values'   => null,
            'required' => false,
            'default'  => 5000),

        'pause'    => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => 'hover'),

        'wrap'     => array(
            'type'     => 'boolean',
            'values'   => null,
            'required' => false,
            'default'  => true),

        'keyboard' => array(
            'type'     => 'boolean',
            'values'   => null,
            'required' => false,
            'default'  => true),
        // Not data-
        'width'  => array(
            'type'     => 'string',
            'values'   => null,
            'required' => true,
            'default'  => 'fit-content'),

        'height'  => array(
            'type'     => 'string',
            'values'   => null,
            'required' => true,
            'default'  => 'fit-content'),

        'transition'  => array(
            'type'     => 'string',
            'values'   => array('none','slide', 'blend'),
            'required' => true,
            'default'  => 'blend'),

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

        if ($state == DOKU_LEXER_ENTER) {
            $width = $attributes['width'];
            unset($attributes['width']); 
            $height = $attributes['height'];
            unset($attributes['height']);
            $transition = $attributes['transition'];
            unset($attributes['transition']); 

            switch ($transition) {
                case 'blend':
                    $transition = "slide $transition";
                    break;
                case 'none':
                    $transition = '';
                    break;
                default:
                    break;
            }

            $html5_attributes = array();

            foreach ($attributes as $attribute => $value) {
                $html5_attributes[] = 'data-' . $attribute . '="' . $value . '"';
            }

            $markup  = '<div class="bs-wrap bs-wrap-carousel carousel '.$transition.'" ';
            $markup .= 'style="width:'.$width.';height:'.$height.';"';
            $markup .= 'data-ride="carousel" '.implode(' ',$html5_attributes).'><ol class="carousel-indicators"></ol><div class="carousel-inner" role="listbox">';

            $renderer->doc .= $markup;

            // Place the edit buttons 
            if (defined('SEC_EDIT_PATTERN')) { // for DokuWiki Greebo and more recent versions
                $renderer->startSectionEdit($pos, array('target' => 'plugin_bootswrapper_carousel', 'name' => $state));
            } else {
                $renderer->startSectionEdit($pos, 'plugin_bootswrapper_carousel', $state);
            }
            return true;
        }

        if ($state == DOKU_LEXER_EXIT) {
            $renderer->doc .= '</div>
  <a class="left carousel-control" href="#" role="button" data-slide="prev">
    <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
    <span class="sr-only">Previous</span>
  </a>
  <a class="right carousel-control" href="#" role="button" data-slide="next">
    <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
    <span class="sr-only">Next</span>
  </a></div>';

            $renderer->finishSectionEdit($pos + strlen($match));
            return true;
        }

        return true;
    }
}
