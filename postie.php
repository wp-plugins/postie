<?php
/*
Plugin Name: Postie
Plugin URI: http://blog.robfelty.com/plugins/postie
Description: Signifigantly upgrades the posting by mail features of Word Press (See <a href='options-general.php?page=postie/postie.php'>Settings and options</a>) to configure your e-mail settings. See the <a href='http://wordpress.org/extend/plugins/postie/other_notes'>Readme</a> for usage and also the <a href='http://wordpress.org/extend/plugins/postie/faq'>FAQ</a>. Visit the <a href='http://forum.robfelty.com/forum/postie'>postie forum</a> for support.
Version: 1.2.3
Author: Robert Felty
Author URI: http://blog.robfelty.com/
*/

/*
$Id$
* -= Requests Pending =-
* German Umlats don't work
* Problem with some mail server
* Multiple emails should tie to a single account
* Each user should be able to have a default category
* Test for calling from the command line
* Support userid/domain  as a valid username
* WP-switcher not compatiable http://www.alexking.org/index.php?content=software/wordpress/content.php#wp_120
* Test out a remote cron system
* Add support for http://unknowngenius.com/wp-plugins/faq.html#one-click
*    www.cdavies.org/code/3gp-thumb.php.txt
*    www.cdavies.org/permalink/watchingbrowserembeddedgpvideosinlinux.php
* Support private posts
* Make it possible to post without a script at all
*/

//Older Version History is in the HISTORY file


if (isset($_GET["postie_read_me"])) {
    include_once(ABSPATH . "wp-admin/admin.php");
    $title = __("Edit Plugins");
    $parent_file = 'plugins.php';
    include(ABSPATH . 'wp-admin/admin-header.php');
    postie_read_me();
    include(ABSPATH . 'wp-admin/admin-footer.php');
}
//Add Menu Configuration
if (is_admin()) {
  require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR ."postie-functions.php");
  add_action("admin_menu","PostieMenu");
  if(function_exists('load_plugin_textdomain')){
    $plugin_dir = WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__));
    function postie_load_domain() {
      load_plugin_textdomain( 'postie', $plugin_dir."/languages/",
      basename(dirname(__FILE__)). '/languages/');
    }
    add_action('init', 'postie_load_domain'); 
  }
}
/* Version info
$Id$
*/
?>
