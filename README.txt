=== StaticPress Auto Builder ===
Contributors: yuji.od
Tags: static, cli
Requires at least: 4.9
Tested up to: 4.9.4
Stable tag: 4.9
Requires PHP: 5.6
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin provides scheduled build and WP-CLI command for build from console/cron.

== Description ==

=== Requirements ===

* StaticPress
    * https://wordpress.org/plugins/staticpress/
* WP-CLI
    * http://wp-cli.org

=== WP-CLI command usage ===

The user option required.

    $ wp staticpress build --user=<user_id/username/email>

When your WordPress is multisite, you can specify a blog.

    $ wp staticpress build --user=<user_id/username/email> --url=<url>

== Installation ==

1. Install WP-CLI to server, example location to `/usr/local/bin/wp`.
1. Upload the plugin files to the `/wp-content/plugins/cron-staticpress` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress.

== Screenshots ==

1. Cron setting page, this cron is executed from WP-Cron.
1. Build from WP-CLI.

== Changelog ==

= 1.1.0 =
* The plugin name is changed to `StaticPress Auto Builder`.
* Added auto build when `publish`, `unpublish` and `update` published post.

= 1.0.0 =
* First release.
