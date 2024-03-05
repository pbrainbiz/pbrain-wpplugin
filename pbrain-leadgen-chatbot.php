<?php

/**
 * Plugin Name:       PBrain
 * Plugin URI:        https://www.pbrain.biz
 * Description:       AI chatbot to collect data and answer queries. No code, conversational forms, ChatGPT and multi-language for best customer support and FAQ experience.
 * Requires at least: 5.5
 * Requires PHP:      7.0
 * Author:            PBrain
 * Version:           1.0.0
 * Text Domain:       pbrain
 * Domain Path:       /languages
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

// Plugin version.
if (!defined('PBRAIN_PLUGIN_VER')) {
  define('PBRAIN_PLUGIN_VER', '1.0.0');
}

// Plugin folder path.
if (!defined('PBRAIN_PLUGIN_DIR')) {
  define('PBRAIN_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

// Plugin name.
if (!defined('PBRAIN_PLUGIN_NAME')) {
  define('PBRAIN_PLUGIN_NAME', plugin_basename(__FILE__));
}

// Don't allow multiple versions to be active.
if (function_exists('pbrain')) {
  // Do not process the plugin code further.
  return;
}

// We require PHP version 7.0+ for the whole plugin to work.
if (version_compare(phpversion(), '7.0', '<')) {

  if (!function_exists('pbrain_php52_notice')) {

    /**
     * Display the notice about incompatible PHP version after deactivation.
     *
     * @since 1.0.0
     */
    function pbrain_php52_notice()
    {
?>
      <div class="notice notice-error">
        <p>
          <?php
          printf(
            wp_kses(
              /* translators: %s - WPBeginner URL for recommended WordPress hosting. */
              __('Your site is running an <strong>insecure version</strong> of PHP that is no longer supported. Please contact your web hosting provider to update your PHP version or switch to a <a href="%s" target="_blank" rel="noopener noreferrer">recommended WordPress hosting company</a>.', 'pbrain'),
              [
                'a' => [
                  'href' => [],
                  'target' => [],
                  'rel' => [],
                ],
                'strong' => [],
              ]
            ),
            'https://www.wpbeginner.com/wordpress-hosting/'
          );
          ?>
        </p>
      </div>

<?php
      // In case this is on plugin activation.
      // phpcs:disable WordPress.Security.NonceVerification.Recommended
      if (isset($_GET['activate'])) {
        unset($_GET['activate']);
      }
      // phpcs:enable WordPress.Security.NonceVerification.Recommended
    }
  }

  add_action('admin_notices', 'pbrain_php52_notice');

  // Do not process the plugin code further.
  return;
}

// Define the class and the function.
require_once dirname(__FILE__) . '/includes/Chatbot.php';

pbrain();
