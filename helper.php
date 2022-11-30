<?php
/**
 * Bootstrap Wrapper Helper Plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Ben van Magill <ben.vanmagill16@gmail.com>
 * @copyright  (C) 2022-2027, Ben van Magill
 */


/**
 * Common functions
 */
class helper_plugin_bootswrapper extends \dokuwiki\Extension\Plugin {
    /**
         * Get persistent user metadata settings
         *
         * @param $key
         * @return array or null
         */
        public function get_user_settings($key='')
        {
            global $ID;
            global $INPUT;
            global $READONLY;

            // Readonly status is toggled and saved in user page metadata
            $user = $INPUT->server->str('REMOTE_USER');
            // Get the last value from metadata and toggle it
            $interwiki = getInterwiki();
            $user_url  = str_replace('{NAME}', $user, $interwiki['user'].':usr_settings');
            return p_get_metadata($user_url, $key);
        }

        /**
         * Get persistent user metadata settings
         *
         * @param $key
         * @return array or null
         */
        public function set_user_settings($data)
        {
            global $ID;
            global $INPUT;
            global $READONLY;

            // Readonly status is toggled and saved in user page metadata
            $user = $INPUT->server->str('REMOTE_USER');
            // Get the last value from metadata and toggle it
            $interwiki = getInterwiki();
            $user_url  = str_replace('{NAME}', $user, $interwiki['user'].':usr_settings');
            return p_set_metadata($user_url, $data);
        }
}