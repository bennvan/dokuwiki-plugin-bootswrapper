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
        
        // Permissions are recalculated on the next request; retain cached assets.
        send_redirect(wl($ID, [], true, '&'));
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
        global $conf;
        $plugin = plugin_load('syntax', $pluginname);
        if (!$plugin) return array();
        $handler = class_exists(\dokuwiki\Parsing\ModeRegistry::class)
            ? new Doku_Handler(new \dokuwiki\Parsing\ModeRegistry($conf['syntax']))
            : new Doku_Handler();
        $data = $plugin->handle($match, $state, $pos, $handler);
        return $data;
    }

    // Rewrite syntax on doku lexer enter
    public function _handle_move_replace_attr($pluginname, $match, $attributes, $attr, $type, $handler) {
        if (empty($attributes[$attr]['src'])) return $match;

        // Replace only the target, keeping quotes, whitespace, parameters and titles.
        $pattern = '/(\s([^\s=<>]+)\s*=\s*)([\'"])(.*?)\3/s';
        return preg_replace_callback($pattern, function ($parts) use ($attr, $type, $handler) {
            if ($parts[2] !== $attr) return $parts[0];
            $value = $parts[4];
            $content = trim($value);
            $offset = strpos($value, $content);
            if ((substr($content, 0, 2) === '[[' && substr($content, -2) === ']]') ||
                (substr($content, 0, 2) === '{{' && substr($content, -2) === '}}')) {
                $content = substr($content, 2, -2);
                $offset += 2;
            }
            $old = trim(substr($content, 0, strcspn($content, '?#|')));
            if ($old === '') return $parts[0];
            if ($type === 'page') {
                $link = Bootstrap_Handler_Parse_Link($old);
                if ($link['type'] !== 'internallink') return $parts[0];
            } elseif (media_isexternal($old) || link_isinterwiki($old)) {
                return $parts[0];
            }

            $new = $handler->resolveMoves($old, $type);
            $new = $handler->relativeLink($old, $new, $type);
            if ($new === $old) return $parts[0];
            $value = substr_replace($value, $new, $offset + strpos($content, $old), strlen($old));
            return $parts[1] . $parts[3] . $value . $parts[3];
        }, $match);
    }

    public function handle_move_register(Doku_Event $event, $params) {
        $event->data['handlers']['bootswrapper_card'] = array($this, 'rewrite_card');
        $event->data['handlers']['bootswrapper_jumbotron'] = array($this, 'rewrite_jumbotron');
        $event->data['handlers']['bootswrapper_pagenav'] = array($this, 'rewrite_pagenav');
        $event->data['handlers']['bootswrapper_button'] = array($this, 'rewrite_button');
    }

    public function rewrite_card($match, $state, $pos, $pluginname, helper_plugin_move_handler $handler) {
        // Dont care unless lexer enter
        if ($state !== DOKU_LEXER_ENTER) {
            return $match;
        }
        // let plugin handle data parse to get attributes
        $data = $this->_get_plugin_handle_data($pluginname, $match, $state, $pos);

        // failsafe
        if (empty($data)) {
            return $match;
        }
        $attributes = $data[3];

        // functions to really change the match data
        $match = $this->_handle_move_replace_attr($pluginname, $match, $attributes, 'thumbnail', 'media', $handler);
        $match = $this->_handle_move_replace_attr($pluginname, $match, $attributes, 'link', 'page', $handler);
        
        return $match;
    }

    public function rewrite_jumbotron($match, $state, $pos, $pluginname, helper_plugin_move_handler $handler) {
        // Dont care unless lexer enter
        if ($state !== DOKU_LEXER_ENTER) {
            return $match;
        }
        // let plugin handle data parse to get attributes
        $data = $this->_get_plugin_handle_data($pluginname, $match, $state, $pos);

        // failsafe
        if (empty($data)) {
            return $match;
        }
        $attributes = $data[3];

        // functions to really change the match data
        $match = $this->_handle_move_replace_attr($pluginname, $match, $attributes, 'background-image', 'media', $handler);
        
        return $match;
    }

    public function rewrite_pagenav($match, $state, $pos, $pluginname, helper_plugin_move_handler $handler) {
        // Dont care unless lexer enter
        if ($state !== DOKU_LEXER_ENTER) {
            return $match;
        }
        // let plugin handle data parse to get attributes
        $data = $this->_get_plugin_handle_data($pluginname, $match, $state, $pos);

        // failsafe
        if (empty($data)) {
            return $match;
        }
        $attributes = $data[3];

        // functions to really change the match data
        $match = $this->_handle_move_replace_attr($pluginname, $match, $attributes, 'prev', 'page', $handler);
        $match = $this->_handle_move_replace_attr($pluginname, $match, $attributes, 'next', 'page', $handler);
        
        return $match;
    }

    public function rewrite_button($match, $state, $pos, $pluginname, helper_plugin_move_handler $handler) {
        // Dont care unless lexer enter
        if ($state !== DOKU_LEXER_ENTER) {
            return $match;
        }
        // let plugin handle data parse to get attributes
        $data = $this->_get_plugin_handle_data($pluginname, $match, $state, $pos);

        // failsafe
        if (empty($data)) {
            return $match;
        }
        $attributes = $data[3];

        // functions to really change the match data
        $match = $this->_handle_move_replace_attr($pluginname, $match, $attributes, 'link', 'page', $handler);
        
        return $match;
    }

}
