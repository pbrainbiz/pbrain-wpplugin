<?php

namespace PBrain {

  require_once PBRAIN_PLUGIN_DIR . '/includes/ChatbotSettings.php';

  /**
   * Main PBrain class.
   *
   * @since 1.0.0
   */
  final class PBrain
  {
    private static $instance;
    private static $chatbotSettings;

    public static function instance()
    {
      if (
        self::$instance === null ||
        !self::$instance instanceof self
      ) {
        self::$instance = new self();
        self::$chatbotSettings = new ChatbotSettings();

        add_action('plugins_loaded', [self::$instance, 'init'], 10);
        add_action('wp_footer', [self::$instance, 'add_plugin']);
        add_filter('plugin_action_links_' . PBRAIN_PLUGIN_NAME, [self::$instance, 'add_plugin_link']);
      }

      return self::$instance;
    }

    public function init()
    {
      do_action('pbrain_loaded');
    }

    public function add_plugin()
    {
      ?>
      <script>
        window.pbrainAsyncInit = function () {
          PBrain.init({ id: <?php echo json_encode(self::$chatbotSettings->get_chatbot_id()) ?> });
        };

        (function (d, s, id) {
          var js, fjs = d.getElementsByTagName(s)[0];
          if (d.getElementById(id)) { return; }
          js = d.createElement(s);
          js.id = id;
          js.src = 'http://localhost:8082/js/sdk.js';
          fjs.parentNode.insertBefore(js, fjs);
        } (document, 'script', 'pbrain-jssdk'));
      </script>
      <?php
    }

    /**
     * Add settings link to plugin actions
     *
     * @param  array  $plugin_actions
     * @param  string $plugin_file
     * @since  1.0.0
     * @return array
     */
    public function add_plugin_link($actions)
    {
      $settings = array('settings' => '<a href="' . esc_url(self::$chatbotSettings->page_link()) . '">' . __('Settings', 'General') . '</a>');
      return array_merge($settings, $actions);
    }
  }
}

namespace {

  /**
   * The function which returns the one instance.
   *
   * @since 1.0.0
   *
   * @return PBrain\PBrain
   */
  function pbrain()
  {
    return PBrain\PBrain::instance();
  }
}
