<?php
if (class_exists('WP_CLI_Command')) {

    /**
     * Build static files by StaticPress.
     */
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
                if ($result->result) {
                    foreach ($result->files as $file) {
                        WP_CLI::log($file->url);
                    }
                }
            } while($result->final === false);

            WP_CLI::log('Finalyze ======================');
            $processRun = WP_CLI::launch_self('staticpress finalyze', array(), array(), true, true);
            $result = json_decode($processRun->stdout);
            if (!$result->result) {
                WP_CLI::error('Finalyze failed.');
            }
            WP_CLI::log('done.');

            WP_CLI::success('Build complete.');
        }
    }

    WP_CLI::add_command('staticpress', 'StaticPress_Command');
}
