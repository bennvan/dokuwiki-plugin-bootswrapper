<?php
/**
 * Bootstrap Wrapper Plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2019, Giuseppe Di Terlizzi
 */


class syntax_plugin_bootswrapper_bootstrap extends dokuwiki\Extension\SyntaxPlugin
{

    public $p_type           = 'stack';
    public $t_type           = 'formatting';
    public $pattern_start    = '<BOOTSTRAP.+?>';
    public $pattern_end      = '</BOOTSTRAP>';
    public $pattern_special  = null;
    public $template_start   = '<div class="%s">';
    public $template_content = '%s';
    public $template_end     = '</div>';
    public $header_pattern   = '[ \t]*={2,}[^\n]+={2,}[ \t]*(?=\n)';
    public $tag_attributes   = array();
    public $tag_name         = null;

    // HTML core/global attribute
    public $core_attributes = array(
        'id'    => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),
        'class' => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),
        'style' => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),
        'title' => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),
        'lang'  => array(
            'type'     => 'string',
            'values'   => null,
            'required' => false,
            'default'  => null),
        'dir'   => array(
            'type'     => 'string',
            'values'   => array('ltr', 'rtl'),
            'required' => false,
            'default'  => null),
    );

    /**
     * Check default and user attributes
     *
     * @param   array  $attributes
     */
    protected function checkAttributes($attributes = array(), Doku_Handler $handler)
    {

        global $ACT;

        $default_attributes = array();
        $merged_attributes  = array();
        $checked_attributes = array();

        if ($ACT == 'preview') {
            $msg_title = '<strong>Bootstrap Wrapper Plugin - ' . (ucfirst(str_replace('syntax_plugin_bootswrapper_',
                '', get_class($this)))) . '</strong>';
        }

        $tag_attributes = array_merge($this->core_attributes, $this->tag_attributes);

        // Save the default values of attributes
        foreach ($tag_attributes as $attribute => $item) {
            $default_attributes[$attribute] = $item['default'];
        }

        foreach ($attributes as $name => $value) {

            if (!isset($tag_attributes[$name])) {
                if ($ACT == 'preview') {
                    msg("$msg_title Unknown attribute <code>$name</code>", -1);
                }
                continue;
            }

            $item = $tag_attributes[$name];

            $required = isset($item['required']) ? $item['required'] : false;
            $values   = isset($item['values']) ? $item['values'] : null;
            $default  = isset($item['default']) ? $item['default'] : null;

            // Normalize boolean value
            if ($item['type'] == 'boolean') {
                switch ($value) {
                    case 'false':
                    case 'FALSE':
                        $value = false;
                        break;
                    case 'true':
                    case 'TRUE':
                        $value = true;
                        break;
                }
            } 

            if ($name == 'class') {
                $value = explode(' ', $value);
            }

            $checked_attributes[$name] = $value;

            // Set the default value when the user-value is empty
            if ($required && empty($value)) {
                $checked_attributes[$name] = $default;

                // Check if the user attribute have a valid range values (single value)
            } elseif ($item['type'] !== 'multiple' && is_array($values) && !in_array($value, $values)) {
                if ($ACT == 'preview') {
                    msg("$msg_title Invalid value (<code>$value</code>) for <code>$name</code> attribute. It will apply the default value <code>$default</code>", 2);
                }

                $checked_attributes[$name] = $default;

                // Check if the user attribute have a valid range values (multiple values)
            } elseif ($item['type'] == 'multiple') {

                $multitple_values = explode(' ', $value);
                $check            = 0;

                foreach ($multitple_values as $single_value) {
                    if (!in_array($single_value, $values)) {
                        $check = 1;
                    }
                }

                if ($check) {
                    if ($ACT == 'preview') {
                        msg("$msg_title Invalid value (<code>$value</code>) for <code>$name</code> attribute. It will apply the default value <code>$default</code>", 2);
                    }
                    $checked_attributes[$name] = $default;
                }
            }

            // Handle parse and metadata if type is media or link
            if ($item['type'] == 'media' && $value) {
                $p = Bootstrap_Handler_Parse_Media($value, $handler);
                $checked_attributes[$name] = $p;

            } elseif ($item['type'] == 'link' && $value) {
                $p = Bootstrap_Handler_Parse_Link($value, $handler);
                $checked_attributes[$name] = $p;
            }
        }

        // Merge attributes (default + user)
        $merged_attributes = array_merge($default_attributes, $checked_attributes);

        // Remove empty attributes
        foreach ($merged_attributes as $attribute => $value) {
            if (empty($value)) {
                unset($merged_attributes[$attribute]);
            }
        }

        // Uncomment for debug
        // msg("$msg_title " . print_r($merged_attributes, 1));

        return $merged_attributes;

    }

    public function getType()
    {
        return $this->t_type;
    }

    public function getAllowedTypes()
    {
        return array('container', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs');
    }

    public function getPType()
    {
        return $this->p_type;
    }

    public function getSort()
    {
        return 195;
    }

    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern($this->pattern_start, $mode, 'plugin_bootswrapper_' . $this->getPluginComponent());
        if ($this->pattern_special) $this->Lexer->addSpecialPattern($this->pattern_special, $mode, 'plugin_bootswrapper_' . $this->getPluginComponent());
    }

    public function postConnect()
    {
        $this->Lexer->addExitPattern($this->pattern_end, 'plugin_bootswrapper_' . $this->getPluginComponent());
        $this->Lexer->addPattern($this->header_pattern, 'plugin_bootswrapper_' . $this->getPluginComponent());
    }

    public function handle($match, $state, $pos, Doku_Handler $handler)
    {

        switch ($state) {
            case DOKU_LEXER_MATCHED:
                // Return match if not a header
                if (!preg_match('/={2,}[^\n]+={2,}/', $match)) return array($state, $match, $pos);

                $title = trim($match);
                $level = 7 - strspn($title, '=');
                if ($level < 1) {
                    $level = 1;
                }

                $title = trim($title, '=');
                $title = trim($title);
                $handler->_addCall('header', array($title, $level, $pos), $pos);
                break;

            case DOKU_LEXER_ENTER:
                $attributes = array();
                // & is not allowed in attribute but may sometimes be used. We will encode for only this case and use php urldecode() when needed.
                $match = str_replace('&', '&amp;', $match);
                $xml        = simplexml_load_string(str_replace('>', '/>', $match));

                if (!is_object($xml)) {
                    $xml = simplexml_load_string('<foo />');

                    global $ACT;

                    if ($ACT == 'preview') {
                        msg('<strong>Bootstrap Wrapper</strong> - Malformed tag (<code>' . hsc($match) . '</code>). Please check your code!', -1);
                    }
                }

                $tag = $xml->getName();

                foreach ($xml->attributes() as $key => $value) {
                    $attributes[$key] = (string) $value;
                }

                if ($tag == strtolower($tag)) {
                    $is_block = false;
                }

                if ($tag == strtoupper($tag)) {
                    $is_block = true;
                }

                $checked_attributes = $this->checkAttributes($attributes, $handler);

                return array($state, $match, $pos, $checked_attributes, $is_block);

            case DOKU_LEXER_UNMATCHED:
                $handler->addCall('cdata', array($match), $pos, null);
                break;

            case DOKU_LEXER_EXIT:
                return array($state, $match, $pos, null);
        }

        return array();
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
        list($state, $match) = $data;

        if ($state == DOKU_LEXER_ENTER) {
            $renderer->doc .= $this->template_start;
            return true;
        }

        if ($state == DOKU_LEXER_EXIT) {
            $renderer->doc .= $this->template_end;
            return true;
        }

        return true;
    }

    protected function mergeCoreAttributes($attributes)
    {

        $core_attributes = array();

        foreach (array_keys($this->core_attributes) as $attribute) {
            if (isset($attributes[$attribute])) {
                $core_attributes[$attribute] = $attributes[$attribute];
            }
        }

        return $core_attributes;
    }

    protected function buildAttributes($attributes, $override_attributes = array())
    {

        $attributes      = array_merge_recursive($attributes, $override_attributes);
        $html_attributes = array();

        foreach ($attributes as $attribute => $value) {
            if ($attribute == 'class') {
                $value = trim(implode(' ', array_unique($value)));
            }

            if ($attribute == 'style') {
                $tmp = '';
                if (is_array($value)){
                    foreach ($value as $property => $val) {
                        $tmp .= "$property:$val;";
                    }
                    $value = $tmp;
                }  
            }

            if ($value) {
                $html_attributes[] = "$attribute=\"$value\"";
            }
        }

        return implode(' ', $html_attributes);

    }

    public function resolveLinkUrl($link, Doku_Renderer $renderer)
    {
        global $ID;
        global $conf;
        global $INFO;

        $type = $link['type'];
        $id = $link['src'];

        if ($type == 'externallink') {
            // Just return the original
            return array($id, true);
        }

        if ($type == 'interwikilink') {
            //get interwiki URL
            $exists = null;
            $url    = $renderer->_resolveInterWiki($link['wikiname'], $link['wikiuri'], $exists);
            return array($url, $exists);
        }

        if ($type == 'emaillink') {
            // escape characters and return mailto
            $address = $renderer->_xmlEntities($id);
            $address = obfuscate($address);
            if($conf['mailguard'] == 'visible') $address = rawurlencode($address);

            return array('mailto:'.$address, true);
        }

        if ($type == 'locallink') {
            // just return the hash as is
            return array($id, true);
        }

        // Render an internallink

        $params = '';
        $parts  = explode('?', $id, 2);
        if(count($parts) === 2) {
            $id     = $parts[0];
            $params = $parts[1];
        }

        // For empty $id we need to know the current $ID
        // We need this check because _simpleTitle needs
        // correct $id and resolve_pageid() use cleanID($id)
        // (some things could be lost)
        if($id === '') {
            $id = $ID;
        }

        // now first resolve and clean up the $id
        $exists = null;
        resolve_pageid(getNS($ID), $id, $exists, $renderer->date_at, true);

        //keep hash anchor
        @list($id, $hash) = explode('#', $id, 2);
        if(!empty($hash)) $hash = $renderer->_headerToLink($hash);

        if($renderer->date_at) {
            $params = $params.'&at='.rawurlencode($renderer->date_at);
        }

        // Build url
        $url = wl($id, $params);
        //keep hash
        if($hash) $url .= '#'.$hash;
        return array($url, $exists);
    }

    public function resolveMediaUrl($media, Doku_Renderer $renderer)
    {
        global $ID;

        $src = $media['src'];
        $width = $media['width'];
        $height = $media['height'];
        $cache = $media['cache'];
        $exists = null;

        if (strpos($src, '#') !== false) {
                list($src, $hash) = explode('#', $src, 2);
            }

        if ($media['type'] == 'internalmedia') {
            resolve_mediaid(getNS($ID), $src, $exists, $renderer->date_at, true);
        }

        $url = ml(
            $src,
            array(
                'w' => $width,
                'h' => $height,
                'cache' => $cache,
            ));

        if ($hash){
            $url .= '#'.$hash;
        }

        // If exists is null it was an external link. Set to true.
        $exists = ($exists === null ? true : false);
        return array($url, $exists);
    }

    public function getContextIcon($type) {
    # Automatic settings of icon by context
        switch ($type) {
            case 'primary':
                $icon_class = 'exclamation-circle';
                break;
                
            case 'success':
                $icon_class = 'check-circle';
                break;

            case 'info':
                $icon_class = 'info-circle';
                break;

            case 'warning':
                $icon_class = 'exclamation-triangle';
                break;

            case 'danger':
                $icon_class = 'minus-circle';
                break;

            default:
                $icon_class = '';
            }
        return "fa:$icon_class";
    } 

}


// Media and link handler modified from dokwuiki/inc/parser/handler.php
// These are needed to set the correct metatdata later.
//------------------------------------------------------------------------
function Bootstrap_Handler_Parse_Media($match, $handler=null) {

    $link = $match;

    // Split title from URL
    $link = explode('|',$link,2);

    // The title...
    if ( !isset($link[1]) ) {
        $link[1] = null;
    }

    //remove any spaces
    $link[0] = trim($link[0]);

    //split into src and parameters (using the very last questionmark)
    $pos = strrpos($link[0], '?');
    if($pos !== false){
        $src   = substr($link[0],0,$pos);
        $param = substr($link[0],$pos+1);
    }else{
        $src   = $link[0];
        $param = '';
    }

    //parse width and height
    if(preg_match('#(\d+)(x(\d+))?#i',$param,$size)){
        !empty($size[1]) ? $w = $size[1] : $w = null;
        !empty($size[3]) ? $h = $size[3] : $h = null;
    } else {
        $w = null;
        $h = null;
    }

    //get linking command
    if(preg_match('/nolink/i',$param)){
        $linking = 'nolink';
    }else if(preg_match('/details/i',$param)){
        $linking = 'details';
    }else if(preg_match('/linkonly/i',$param)){
        $linking = 'linkonly';
    }else{
        $linking = 'direct';
    }

    //get caching command
    if (preg_match('/(nocache|recache)/i',$param,$cachemode)){
        $cache = $cachemode[1];
    }else{
        $cache = 'cache';
    }

    // Check whether this is a local or remote image or interwiki
    if (media_isexternal($src) || link_isinterwiki($src)){
        $call = 'externalmedia';
    } else {
        $call = 'internalmedia';
    }

    $p = array(
        'type'=>$call,
        'src'=>$src,
        'title'=>$link[1],
        'align'=>$align,
        'width'=>$w,
        'height'=>$h,
        'cache'=>$cache,
        'linking'=>$linking,
        'param'=>$param,
    );

    if ($handler) {
        // Add calls to insert media into metadata (but dont render)
        $handler->addCall(
            $p['type'],
            array($p['src'], $p['title'], $p['align'], $p['width'],
            $p['height'], $p['cache'], $p['linking'], true),
            null
        );
    }

    return $p;
}

/**
    * @param string $match matched syntax
    * @param Doku handler
    */
function Bootstrap_Handler_Parse_Link($match, $handler=null) {

    $link = $match;
    // Split title from URL
    $link = explode('|',$link,2);
    if ( !isset($link[1]) ) {
        $link[1] = null;
    } 

    $link[0] = trim($link[0]);

    //decide which kind of link it is
    if ( link_isinterwiki($link[0]) ) {
        // Interwiki
        $type = 'interwikilink';
        $interwiki = explode('>',$link[0],2);
        $wikiname = strtolower($interwiki[0]);
        $wikiuri = $interwiki[1];
        if ($handler) $handler->addCall($type,array($link[0],$link[1],$wikiname,$wikiuri,true),null);

    }elseif ( preg_match('/^\\\\\\\\[^\\\\]+?\\\\/u',$link[0]) ) {
        // Windows Share
        $type = 'windowssharelink';  
    }elseif ( preg_match('#^([a-z0-9\-\.+]+?)://#i',$link[0]) ) {
        // external link (accepts all protocols)
        $type = 'externallink';    
    }elseif ( preg_match('<'.PREG_PATTERN_VALID_EMAIL.'>',$link[0]) ) {
        // E-Mail (pattern above is defined in inc/mail.php)
        $type = 'emaillink';
        if ($handler) $handler->addCall($type,array($link[0],$link[1], true),null);
    }elseif ( preg_match('!^#.+!',$link[0]) ){
        // local link
        $type = 'locallink';
        if ($handler) $handler->addCall($type,array(substr($link[0],1),$link[1],true),null);
    }else{
        // internal link
        $type = 'internallink';
        if ($handler) $handler->addCall($type,array($link[0],$link[1], null, true),null);      
    }

    $params = array(
        'type'=>$type,
        'src'=>$link[0],
        'title'=>$link[1],
        'wikiname'=> (isset($wikiname) ? $wikiname : null),
        'wikiuri'=> (isset($wikiuri) ? $wikiuri : null)
    );

    return $params;
}