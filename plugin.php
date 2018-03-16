<?php
/*
Plugin Name: StaticPress WP-CLI Integration
Author: yu-ji
Plugin URI: http://factage.com/staticpress-wpcli
Description: This plugin provides WP-CLI command for build static files from console/cron.
Version: 1.0.0
Author URI: http://factage.com
License: GPL2
*/
if (!class_exists('static_press')) {
    if (file_exists(wp_normalize_path(WP_PLUGIN_DIR) . '/staticpress')) {
        add_action('admin_notices', function () {
            echo '<div class="error"><p><strong>StaticPress WP-CLI Integration</strong><br><a href="'.admin_url().'plugins.php?plugin_status=all&paged=1&s">Please activate StaticPress</a></p></div>';
        });
    } else {
        add_action('admin_notices', function () {
            echo '<div class="error"><p><strong>StaticPress WP-CLI Integration</strong><br><a href="'.admin_url().'plugin-install.php?s=staticpress&tab=search&type=term">Please install StaticPress</a></p></div>';
        });
    }
}

// Load this plugin last place.
add_filter('pre_update_option_active_plugins', function ($active_plugins) {
    $this_plugin = str_replace(wp_normalize_path(WP_PLUGIN_DIR).'/', '', wp_normalize_path(__FILE__));
    foreach ($active_plugins as $no => $path) {
        if ($path == $this_plugin) {
            unset($active_plugins[$no]);
            $active_plugins[] = $this_plugin;
            break;
        }
    }
    return $active_plugins;
});

if (class_exists('WP_CLI_Command')) {

    class StaticPress_Command extends WP_CLI_Command {

        /**
         * (Internal command) Initialize process.
         */
        public function init () {
            $staticpress = static_press::$instance;
            $staticpress->ajax_init();
        }

        /**
         * (Internal command) Fetch process.
         */
        public function fetch () {
            $staticpress = static_press::$instance;
            $staticpress->ajax_fetch();
        }

        /**
         * (Internal command) Finalyze process.
         */
        public function finalyze () {
            $staticpress = static_press::$instance;
            $staticpress->ajax_finalyze();
        }

        /**
         * Build static files.
         */
        public function build () {
            if (!is_user_logged_in()) {
                WP_CLI::error('The user option required.');
            }

            WP_CLI::log('Initialize ====================');
            $processRun = WP_CLI::launch_self('staticpress init', array(), array(), true, true);
            $result = json_decode($processRun->stdout);
            if (!$result->result) {
                WP_CLI::error('Initialize failed.');
            }
            foreach ($result->urls_count as $url_count) {
                WP_CLI::log(sprintf('%s (%d)', $url_count->type, $url_count->count));
            }

            WP_CLI::log('Fetch =========================');
            $result = array();
            do {
                $processRun = WP_CLI::launch_self('staticpress fetch', array(), array(), true, true);
                $result = json_decode($processRun->stdout);
                if (!$result->result) {
                    WP_CLI::error('Fetch failed.');
                }
                foreach ($result->files as $file) {
                    WP_CLI::log($file->url);
                }
            } while($result->final === false);

            WP_CLI::log('Finalyze ======================');
            $processRun = WP_CLI::launch_self('staticpress finalyze', array(), array(), true, true);
            $result = json_decode($processRun->stdout);
            if (!$result->result) {
                WP_CLI::error('Finalyze failed.');
            }

            WP_CLI::success('Build complete.');
        }
    }

    WP_CLI::add_command('staticpress', 'StaticPress_Command');
}
