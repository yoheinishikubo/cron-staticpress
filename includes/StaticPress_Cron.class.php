<?php
class StaticPress_Cron {
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    /**
     * Schedule recurrence values.
     */
    private $schedules;

    const TEXT_DOMAIN = 'staticpress-cron';
    const SCHEDULE_EVENT = 'staticpress_build';
    const OPTION_NAME = 'staticpress_cron_option';

    /**
     * Start up
     */
    public function __construct() {
        $this->schedules = array(
            'hourly' => __('Horly', self::TEXT_DOMAIN),
            'daily' => __('Daily', self::TEXT_DOMAIN),
            'twicedaily' => __('Twice daily', self::TEXT_DOMAIN),
        );
        $this->options = get_option(self::OPTION_NAME);
        add_action('admin_menu', array( $this, 'add_plugin_page'));
        add_action('admin_init', array( $this, 'page_init'));
        add_action(self::SCHEDULE_EVENT, array($this, 'execute_build'));
    }

    /**
     * Add options page
     */
    public function add_plugin_page() {
        add_submenu_page(
            'static-press',
            __('Cron setting', self::TEXT_DOMAIN),
            __('Cron setting', self::TEXT_DOMAIN),
            'manage_options',
            'static-press-cron-options',
            array($this, 'create_admin_page')
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page() {
        ?>
        <div class="wrap">
            <h2><?php echo __('StaticPress Cron setting', self::TEXT_DOMAIN); ?></h2>
            <?php settings_errors(); ?>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'staticpress_cron_group' );
                do_settings_sections( 'staticpress_cron_admin' );
                submit_button();
            ?>
            </form>
            <h2>Advanced usage</h2>
            <p><?php _e('You can call build from console or crontab.', self::TEXT_DOMAIN); ?></p>
            <p><?php _e('The user option required.'); ?></p>
            <p><code>$ wp staticpress build --user=&lt;user_id/username/email&gt;</code></p>
            <p><?php _e('When your WordPress is multisite, you can specify a blog.'); ?></p>
            <p><code>$ wp staticpress build --user=&lt;user_id/username/email&gt; --url=&lt;site_url&gt;</code></p>

        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init() {
        register_setting(
            'staticpress_cron_group', // Option group
            self::OPTION_NAME, // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'staticpress_section', // ID
            '', // Title
            null, // Callback
            'staticpress_cron_admin' // Page
        );

        add_settings_field(
            'enabled',
            __('Auto build', self::TEXT_DOMAIN),
            array( $this, 'enabled_callback' ),
            'staticpress_cron_admin',
            'staticpress_section'
        );

        add_settings_field(
            'command',
            __('WP-CLI Path', self::TEXT_DOMAIN),
            array( $this, 'command_callback' ),
            'staticpress_cron_admin',
            'staticpress_section'
        );

        add_settings_field(
            'schedule',
            __('Schedule', self::TEXT_DOMAIN),
            array( $this, 'schedule_callback' ),
            'staticpress_cron_admin',
            'staticpress_section'
        );

        add_action('update_option_'.self::OPTION_NAME, function($old_value, $value) {
            $schedule_args = array(
                get_current_user_id(),
            );
            if (!empty($value['enabled'])) {
                if ($value['schedule'] == 'hourly') {
                    $next_time = strtotime(date('Y-m-d H:00:00', strtotime('next hour')));
                } else if ($value['schedule'] == 'daily') {
                    $next_time = strtotime('next day midnight') - (get_option('gmt_offset') * 60 * 60);
                } else if ($value['schedule'] == 'twicedaily') {
                    $next_time = strtotime('noon') - (get_option('gmt_offset') * 60 * 60);
                    if ($next_time < time()) {
                        $next_time = strtotime('next day midnight', $next_time) - (get_option('gmt_offset') * 60 * 60);
                    }
                }
                wp_clear_scheduled_hook(self::SCHEDULE_EVENT, $schedule_args);
                wp_schedule_event($next_time, $value['schedule'], self::SCHEDULE_EVENT, $schedule_args);
            } else {
                wp_clear_scheduled_hook(self::SCHEDULE_EVENT, $schedule_args);
            }
       }, 10, 2);
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input) {
        $new_input = array();
        if(isset($input['enabled'])) {
            $new_input['enabled'] = sanitize_text_field($input['enabled']);
        }
        if(isset($input['command'])) {
            $new_input['command'] = sanitize_text_field($input['command']);
            if (!file_exists($new_input['command'])) {
                $message = __('The specified WP-CLI command path was not found.', self::TEXT_DOMAIN);
                if (!empty($new_input['enabled'])) {
                    $message .= ' ' . __('Enabling cron failed.', self::TEXT_DOMAIN);
                    $new_input['enabled'] = '';
                }
                add_settings_error(
                    'command',
                    'command',
                    $message,
                    'error'
                );
            }
        }
        if(isset($input['schedule'])) {
            $new_input['schedule'] = sanitize_text_field($input['schedule']);
        }
        return $new_input;
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function enabled_callback() {
        printf(
            '<label><input type="checkbox" id="enabled" name="'.self::OPTION_NAME.'[enabled]" value="1" %s/>%s</label>',
            !empty( $this->options['enabled'] ) ? 'checked' : '', __('Enabled', self::TEXT_DOMAIN)
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function command_callback() {
        printf(
            '<input type="text" id="command" name="'.self::OPTION_NAME.'[command]" size="50" value="%s" />',
            isset( $this->options['command'] ) ? esc_attr( $this->options['command']) : ''
        );
        echo '<br><p class="description">If you don\'t have WP-CLI, you need to <a href="http://wp-cli.org" target="_blank">install</a>.</p>';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function schedule_callback() {
        echo '<select id="schedule" name="'.self::OPTION_NAME.'[schedule]">';
        foreach ($this->schedules as $value => $label)  {
            $selected = (!empty($this->options['schedule']) && $this->options['schedule'] == $value) ? ' selected' : '';
            printf('<option value="%s"%s>%s</option>', $value, $selected, $label);
        }
        echo '</select>';
    }

    /**
     * Execute build.
     */
    public function execute_build($user) {
        $site_url = get_site_url();
        $command = sprintf('%s staticpress build --user=%d --blog=%s', $this->options['command'], $user, $site_url);
        $result = '';
        exec($command, $result);
        echo join("\n", $result);
    }
}

if(is_admin() || defined('DOING_CRON')) {
    $staticpress_cron = new StaticPress_Cron();
}