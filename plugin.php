<?php
/*
Plugin Name: StaticPress Cron
Author: yu-ji
Plugin URI: http://github.com/yujiod/staticpress-cron
Description: This plugin provides scheduled build and WP-CLI command for build from console/cron.
Version: 1.0.0
Author URI: http://factage.com
License: GPL2
*/

require dirname(__FILE__).'/includes/StaticPress_Command.class.php';
require dirname(__FILE__).'/includes/StaticPress_Cron.class.php';

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

// Install plugin hook
register_activation_hook(__FILE__, function () {
    add_option('staticpress_cron_option', array(
        'command' => '/usr/local/bin/wp',
        'enabled' => '',
        'schedule' => 'hourly',
    ));
});

// Uninstall plugin hook
register_deactivation_hook(__FILE__, function () {
    delete_option('staticpress_cron_option');
    wp_clear_scheduled_hook(StaticPress_Cron::SCHEDULE_EVENT, array(get_current_user_id()));
});


// Check required plugins
if (!class_exists('static_press')) {
    if (file_exists(wp_normalize_path(WP_PLUGIN_DIR) . '/staticpress')) {
        add_action('admin_notices', function () {
            echo '<div class="error"><p><strong>StaticPress Cron</strong><br><a href="'.admin_url().'plugins.php?plugin_status=all&paged=1&s">Please activate StaticPress</a></p></div>';
        });
    } else {
        add_action('admin_notices', function () {
            echo '<div class="error"><p><strong>StaticPress Cron</strong><br><a href="'.admin_url().'plugin-install.php?s=staticpress&tab=search&type=term">Please install StaticPress</a></p></div>';
        });
    }
}
