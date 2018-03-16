=== StaticPress WP-CLI Integration ===
Contributors: yuji.od
Tags: static, cli
Requires at least: 4.9
Tested up to: 4.9.4
Stable tag: 4.9
Requires PHP: 7.1.7
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin provides WP-CLI command for build static files from console/cron.

== Description ==

=== Requirments ===

* StaticPress
    * https://wordpress.org/plugins/staticpress/
* WP-CLI
    * http://wp-cli.org

=== Usage ===

The user option required.

```
wp-cli staticpress build --user=<user_id/username/email>
```

When your WordPress is multisite, you can specify a blog.

```
wp-cli staticpress build --user=<user_id/username/email> --url=<url>
```

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/staticpress-wpcli` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress

== Changelog ==

= 1.0.0 =
* First release.
