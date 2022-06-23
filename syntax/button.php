<?php
/**
 * Bootstrap Wrapper Plugin: Button
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2020, Giuseppe Di Terlizzi
 */

class syntax_plugin_bootswrapper_button extends syntax_plugin_bootswrapper_bootstrap
{

    public $p_type         = 'normal';
    public $pattern_start  = '<(?:btn|button).*?>(?=.*?</(?:btn|button)>)';
    public $pattern_end    = '</(?:btn|button)>';
    public $tag_name       = 'button';
    public $tag_attributes = array(

        'type'     => array(
            'type'     => 'string',
            'values'   => array('default', 'primary', 'success', 'info', 'warning', 'danger', 'link'),
            'required' => true,
            'default'  => 'default'),

        'size'     => array(
            'type'     => 'string',
            'values'   => array('lg', 'sm', 'xs'),
            'required' => false,
            'default'  => null),

        'icon'     => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'collapse' => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'modal'    => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'block'    => array(
            'type'     => 'boolean',
            'values'   => array(0, 1),
            'required' => false,
            'default'  => null),

        'disabled' => array(
            'type'     => 'boolean',
            'values'   => array(0, 1),
            'required' => false,
            'default'  => null),

        'title'    => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'link' => array(
            'type'     => 'link',
            'values'   => null,
            'required' => false,
            'default'  => null),

        'auto-heading' => array(
            'type'     => 'boolean',
            'values'   => array(0, 1),
            'required' => false,
            'default'  => false),

        'href'    => array(
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

        if ($state == DOKU_LEXER_ENTER) {
            // Wrapper
            $heading = '';
            $auto_heading = $attributes['auto-heading'];
            $type = $attributes['type'];
            $icon = $attributes['icon'];
            $link = $attributes['link'];
            $href = $attributes['href']; 
            $html_attributes            = $this->mergeCoreAttributes($attributes);
            $html_attributes['class'][] = 'bs-wrap bs-wrap-button';

            // Button
            $btn_attributes['class'][] = "btn btn-$type";
            $btn_attributes['class'][] = $attributes['size'] ? 'btn-'.$attributes['size'] : '';
            $btn_attributes['class'][] = $attributes['block'] ? 'btn-block' : '';
            $btn_attributes['class'][] = $attributes['disabled'] ? 'disabled' : '';
            $btn_attributes['title'] = $attributes['title'] ? $attributes['title'] : '';


            // Resolve the link
            if ($link) {
                list($url, $exists) = $this->resolveLinkUrl($link, $renderer);
                $btn_attributes['href'] = $url;
                if ($exists) {
                    $btn_attributes['class'][] ='wikilink1';
                    if ($link['type'] == 'externallink') {
                        $btn_attributes['target'] = '_blank';
                        $btn_attributes['rel'] = 'ugc nofollow noopener';
                    }
                    if ($link['type'] == 'internallink' && $auto_heading) {
                        $heading = p_get_first_heading($link['src']);
                    }
                } else {
                    $btn_attributes['rel'] = 'ugc nofollow noopener';
                    if ($link['type'] != 'externallink') {
                        $btn_attributes['class'][] = 'wikilink2';
                    } 
                }
            } else {
                $btn_attributes['href'] = $href ? $href : 'javascript:void(0)';    
            }

            if ($attributes['modal']) {
                $btn_attributes['data-toggle'] = 'modal';
                $btn_attributes['data-target'] = '#'.$attributes['modal'];
            }
            elseif ($attributes['collapse']) {
                $btn_attributes['data-toggle'] = 'collapse';
                $btn_attributes['data-target'] = '#'.$attributes['collapse'];
            }

            # Set icon attribute manually or by context
            if (strtolower($icon) == 'true') {
                $icon_class = $this->getContextIcon($type);
            } else {
                $icon_class = $icon;
            }

            foreach (array_keys($this->tag_attributes) as $attribute) {
                if (isset($attributes[$attribute])) {
                    $html_attributes["data-btn-$attribute"] = $attributes[$attribute];
                }
            }

            $markup  = '<span ' . $this->buildAttributes($html_attributes) . '>';
            $markup .= '<a ' . $this->buildAttributes($btn_attributes) . '>';

            if ($icon && $icon_class) {
                $markup .= '<i class="iconify" data-icon="'. $icon_class . '"></i>&nbsp;' . $heading;
            }

            $renderer->doc .= $markup;
            return true;
        }

        if ($state == DOKU_LEXER_EXIT) {
            $renderer->doc .= "</a></span>";
            return true;
        }

        return true;
    }
}
