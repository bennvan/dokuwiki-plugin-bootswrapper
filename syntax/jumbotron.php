<?php
/**
 * Bootstrap Wrapper Plugin: Jumbotron
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2020, Giuseppe Di Terlizzi
 */

class syntax_plugin_bootswrapper_jumbotron extends syntax_plugin_bootswrapper_bootstrap
{

    public $pattern_start  = '<(?:JUMBOTRON|jumbotron).*?>(?=.*?</(?:JUMBOTRON|jumbotron)>)';
    public $pattern_end    = '</(?:JUMBOTRON|jumbotron)>';
    public $tag_name       = 'jumbotron';
    public $tag_attributes = array(

        'background-image' => array(
            'type'     => 'media',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'background' => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'color'      => array(
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

        list($state, $match, $pos, $attributes, $is_block) = $data;

        /** @var Doku_Renderer_xhtml $renderer */
        if ($state == DOKU_LEXER_ENTER) {
            $background = $attributes['background'];
            $background_image = $attributes['background-image'];
            $color      = $attributes['color'];
            $class = (isset($attributes['class']) ? $attributes['class'] : 'bg-grey');

            $html_attributes = $this->mergeCoreAttributes($attributes);
            $html_attributes['class'][] = 'bs-wrap bs-wrap-jumbotron jumbotron '.$class ;

            if ($background_image) {
                list($url, $exists) = $this->resolveMediaUrl($background_image, $renderer);
                // Pass url even if doesnt exists, so user can see/fix issue.
                $html_attributes['style']['background-image'] = 'url(' . $url . ')';
                $html_attributes['style']['background-size'] = 'cover';
            } elseif ($background) {
                $html_attributes['style']['background'] = $background;
            }

            if ($color) {
                $html_attributes['style']['color'] = hsc($color);
            }

            $markup = '<div '. $this->buildAttributes($html_attributes) .'>';

            $renderer->doc .= $markup;

            return true;
        }

        if ($state == DOKU_LEXER_EXIT) {
            $renderer->doc .= '</div>';
            return true;
        }

        return true;
    }
}
