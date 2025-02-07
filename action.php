<?php
/**
 * Bootstrap Wrapper Action Plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2020, Giuseppe Di Terlizzi
 */


/**
 * Bootstrap Wrapper Action Plugin
 *
 * Add external CSS file to DokuWiki
 */
class action_plugin_bootswrapper extends \dokuwiki\Extension\ActionPlugin
{

    /**
     * Syntax with section edit
     *
     * @var array
     */
    private $section_edit_buttons = array(
        'plugin_bootswrapper_pane',
        'plugin_bootswrapper_panel',
        'plugin_bootswrapper_card',
        'plugin_bootswrapper_carousel',
    );

    /**
     * Register events
     *
     * @param  Doku_Event_Handler  $controller
     */
    public function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('TOOLBAR_DEFINE', 'AFTER', $this, '_insert_button');
        $controller->register_hook('HTML_SECEDIT_BUTTON', 'BEFORE', $this, '_secedit_button');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handleDoReadOnly');
        $controller->register_hook('AUTH_ACL_CHECK', 'AFTER', $this, 'handleReadOnlyAcl');
        $controller->register_hook('PLUGIN_MOVE_HANDLERS_REGISTER', 'BEFORE', $this, 'handle_move_register');
    }

    /**
     * Readonly mode to couple with bootswrap3 plugin.
     *
     * @param Doku_Event $event
     * @return bool
     */
    public function handleDoReadOnly(Doku_Event $event)
    {
        global $ID;
        global $INFO;
        global $INPUT;

        if ($event->data != 'readonly') return true;

        // Readonly status is toggled and saved in user page metadata
        $user = $INPUT->server->str('REMOTE_USER');
        $is_user = auth_isMember('@user,@admin',$user,$INFO['userinfo']['grps']);

        // Dont allow non users to have settings
        if (!$is_user) send_redirect(wl($ID));

        $hlp = plugin_load('helper', 'bootswrapper');
        // Get the last value from metadata and toggle it
        $readonly = $hlp->get_user_settings('render readonly');

        // Set readonly state accordinly
        if ($readonly === null) {
        	// Turning on readonly for first time
        	$readonly = true;
        } else {
        	// Toggle the value
        	$readonly = !$readonly;
        }
        // Save the state in persistent metadata
        $hlp->set_user_settings(array('render' => array('readonly' => $readonly)));
        
        header('Clear-Site-Data: "cache"');
        send_redirect(wl($ID,['t'=>time()], true, '&'));
        return true; // never reached
    }

    /**
     * Readonly mode to couple with bootswrap3 plugin.
     *
     * @param Doku_Event $event
     * @return bool
     */
    public function handleReadOnlyAcl(Doku_Event $event)
    {
        global $ID;
        global $INPUT;
        global $READONLY;

        // Readonly status is loaded from metadata and applied
        if ($READONLY === null) {
        	$hlp = plugin_load('helper', 'bootswrapper');
        	$READONLY = $hlp->get_user_settings('render readonly');
        	// Still null or false?
        	if (!$READONLY) {
        		$READONLY = false;
        		return;
        	}
        }
        
        if (!$READONLY) return;
        // return if user does already has or lower than read permissions
        if ($event->result <= 1) return;

        // reach this if readonly is set true
        $event->result = 1;

        return true;
    }

    /**
     * Set Section Edit button
     *
     * @param  Doku_Event  &$event
     */
    public function _secedit_button(Doku_Event $event)
    {
        global $lang;

        if (!in_array($event->data['target'], $this->section_edit_buttons)) {
            return;
        }

        $event->data['name'] = $lang['btn_secedit'] . ' - ' . ucfirst(str_replace('plugin_bootswrapper_', '', $event->data['target']));
        // Use wikitext editor
        $event->data['target'] = 'section';
    }

    /**
     * Set toolbar button in edit mode
     *
     * @param  Doku_Event  &$event
     */
    public function _insert_button(Doku_Event $event, $param)
    {
        $event->data[] = array(
            'type'    => 'mediapopup',
            'title'   => 'Bootstrap Wrapper',
            'icon'    => '../../plugins/bootswrapper/images/bootstrap.png',
            'url'     => 'lib/plugins/bootswrapper/exe/popup.php?ns=',
            'name'    => 'bootstrap-wrapper',
            'options' => 'width=800,height=600,left=20,top=20,toolbar=no,menubar=no,scrollbars=yes,resizable=yes',
            'block'   => false,
        );
    }

    public function _get_plugin_handle_data($pluginname, $match, $state, $pos) {
        $plugin =& plugin_load('syntax', $pluginname);
        $data = $plugin->handle($match, $state, $pos, new Doku_Handler());
        return $data;
    }

    // Rewrite syntax on doku lexer enter
    public function _handle_move_replace_attr($pluginname, $match, $attributes, $attr, $type, $handler) {
        // Get the specific value from the attributes array
        $old = $attributes[$attr];
        $old_val = $old['src'];

        //extract hash anchor
        if (strpos($old_val, '#') !== false) {
            list($old_val, $hash) = explode('#', $old_val, 2);
        }
        if (substr($old_val, 0, 2) == '{{' || substr($old_val, 0, 2) == '[[') {
            $old_val = substr($old_val, 2, -2);
        }

        $new_val = $handler->resolveMoves($old_val, $type);
        $new_val = $handler->relativeLink($old_val, $new_val, $type);

        if ($new_val == $old_val) {
            // Nothing changed. Return original match
            return $match;
        } else {
            // check if there are old params or hash
            if ($old['param']) {
                $new_val .= '?'.$old['param'];
            }
            if ($hash) {
                $new_val .= '#'.$hash;
            }
            // preg_replace attribute in string. This preserves the order the user may type them.
            $reg = '/\s('.preg_quote($attr).')\s*=\s*"[^"]*\"/';
            $replace =  ' ${1}="'.$new_val.'"';
            // Find and replace the specific attribute in the text value in the tag.
            $result = preg_replace($reg, $replace, $match);

            if (!empty($result)){
                return $result;
            }
            // Something went wrong if here. Return original match.
            dbglog($pluginname . ' - FAILED REWRITE Pattern: ' . $reg . ', REPLACE: ' . $replace . ', GAVE NONE RESULT');
            return $match; 
        }
    }

    public function handle_move_register(Doku_Event $event, $params) {
        $event->data['handlers']['bootswrapper_card'] = array($this, 'rewrite_card');
        $event->data['handlers']['bootswrapper_jumbotron'] = array($this, 'rewrite_jumbotron');
        $event->data['handlers']['bootswrapper_pagenav'] = array($this, 'rewrite_pagenav');
        $event->data['handlers']['bootswrapper_button'] = array($this, 'rewrite_button');
    }

    public function rewrite_card($match, $state, $pos, $pluginname, helper_plugin_move_handler $handler) {
        // Dont care unless lexer enter
        if (!$state == DOKU_LEXER_ENTER) {
            return $match;
        }
        // let plugin handle data parse to get attributes
        $data = $this->_get_plugin_handle_data($pluginname, $match, $state, $pos);

        // failsafe
        if (empty($data)) {
            return $match;
        }
        list($state, $match, $pos, $attributes) = $data;

        // functions to really change the match data
        $match = $this->_handle_move_replace_attr($pluginname, $match, $attributes, 'thumbnail', 'media', $handler);
        $match = $this->_handle_move_replace_attr($pluginname, $match, $attributes, 'link', 'page', $handler);
        
        return $match;
    }

    public function rewrite_jumbotron($match, $state, $pos, $pluginname, helper_plugin_move_handler $handler) {
        // Dont care unless lexer enter
        if (!$state == DOKU_LEXER_ENTER) {
            return $match;
        }
        // let plugin handle data parse to get attributes
        $data = $this->_get_plugin_handle_data($pluginname, $match, $state, $pos);

        // failsafe
        if (empty($data)) {
            return $match;
        }
        list($state, $match, $pos, $attributes) = $data;

        // functions to really change the match data
        $match = $this->_handle_move_replace_attr($pluginname, $match, $attributes, 'background-image', 'media', $handler);
        
        return $match;
    }

    public function rewrite_pagenav($match, $state, $pos, $pluginname, helper_plugin_move_handler $handler) {
        // Dont care unless lexer enter
        if (!$state == DOKU_LEXER_ENTER) {
            return $match;
        }
        // let plugin handle data parse to get attributes
        $data = $this->_get_plugin_handle_data($pluginname, $match, $state, $pos);

        // failsafe
        if (empty($data)) {
            return $match;
        }
        list($state, $match, $pos, $attributes) = $data;

        // functions to really change the match data
        $match = $this->_handle_move_replace_attr($pluginname, $match, $attributes, 'prev', 'page', $handler);
        $match = $this->_handle_move_replace_attr($pluginname, $match, $attributes, 'next', 'page', $handler);
        
        return $match;
    }

    public function rewrite_button($match, $state, $pos, $pluginname, helper_plugin_move_handler $handler) {
        // Dont care unless lexer enter
        if (!$state == DOKU_LEXER_ENTER) {
            return $match;
        }
        // let plugin handle data parse to get attributes
        $data = $this->_get_plugin_handle_data($pluginname, $match, $state, $pos);

        // failsafe
        if (empty($data)) {
            return $match;
        }
        list($state, $match, $pos, $attributes) = $data;

        // functions to really change the match data
        $match = $this->_handle_move_replace_attr($pluginname, $match, $attributes, 'link', 'page', $handler);
        
        return $match;
    }

}
