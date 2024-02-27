<?php

namespace PBrain {
  final class ChatbotSettings
  {
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
      // Set class property
      $this->options = get_option('pbrain_settings');
      add_action('wp_ajax_pbrain_settings_save', [$this, 'all_settings_save']);
      if (is_admin()) {
        add_action('admin_menu', [$this, 'add_plugin_page']);
        add_action('admin_init', [$this, 'page_init']);
        add_action('admin_enqueue_scripts', [$this, 'svelte_scripts']);
      }
    }

    public function page_link()
    {
      return get_admin_url(null, 'options-general.php?page=pbrain-options');
    }

    public function get_chatbot_id()
    {
      return isset($this->options['chatbot_id']) ? $this->options['chatbot_id'] : '';
    }

    public function svelte_scripts($hook)
    {
      if ('settings_page_pbrain-options' != $hook) {
        return;
      }

      $js_paths = glob(PBRAIN_PLUGIN_DIR . '/dist/*.js');
      if (count($js_paths) > 0) {
        wp_enqueue_script('pbrain-svelte-js', $js_paths[0], [], $hook, true);
      }

      $css_paths = glob(PBRAIN_PLUGIN_DIR . '/dist/*.css');
      if (count($css_paths) > 0) {
        wp_enqueue_style('pbrain-svelte-css', $css_paths[0]);
      }

      wp_localize_script('pbrain-svelte-js', 'pbrain_wpplugin_global', [
        'nonce' => wp_create_nonce('pbrain-ajax-nonce')
      ]);
    }

    public function all_settings_save()
    {
      if (!is_admin()) {
        return wp_send_json([
          'status' => 'fail'
        ], 400);
      }

      if (
        isset($_POST['data'], $_POST['_ajax_nonce'])
        && wp_verify_nonce($_POST['_ajax_nonce'], 'pbrain-ajax-nonce')
      ) {
        // Input var okay.
        $user_settings = json_decode(wp_unslash($_POST['data']));
        $new_settings = [
          'chatbot_id' => !empty($user_settings->chatbotId)
            ? $user_settings->chatbotId
            : $this->options['chatbot_id']
        ];

        $updated = update_option('pbrain_settings', $new_settings);
        $this->options = $new_settings;
        return wp_send_json([
          'status' => 'success',
          'updated' => $updated,
        ]);
      }

      return wp_send_json([
        "status" => "fail"
      ], 400);
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
      // This page will be under "Settings"
      add_options_page(
        __('PBrain lead generation ChatGPT', 'pbrain'),
        'PBrain',
        'manage_options',
        'pbrain-options',
        [$this, 'create_admin_page']
      );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
?>
      <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <div id="pbrain-options-fields" data-chatbot-id="<?php echo isset($this->options['chatbot_id']) ? esc_attr($this->options['chatbot_id']) : '' ?>">
        </div>
      </div>
<?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
      register_setting(
        'pbrain_option_group', // Option group
        'pbrain_settings', // Option name
        [$this, 'sanitize'] // Sanitize
      );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input)
    {
      $new_input = array();
      // if (isset($input['id_number']))
      //   $new_input['id_number'] = absint($input['id_number']);

      if (isset($input['chatbot_id'])) {
        $new_input['chatbot_id'] = sanitize_text_field($input['chatbot_id']);
      }

      return $new_input;
    }
  }
}
