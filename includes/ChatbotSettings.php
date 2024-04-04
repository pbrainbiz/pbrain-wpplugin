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

      $js_paths = glob(PBRAIN_PLUGIN_DIR . 'dist/*.js');
      if (count($js_paths) > 0) {
        $relative_path = substr($js_paths[0], strlen(PBRAIN_PLUGIN_DIR));
        $url_path = plugins_url($relative_path, PBRAIN_PLUGIN_DIR . basename(PBRAIN_PLUGIN_NAME));
        wp_enqueue_script('pbrain-svelte-js', $url_path, [], PBRAIN_PLUGIN_VER, true);
      }

      $css_paths = glob(PBRAIN_PLUGIN_DIR . 'dist/*.css');
      if (count($css_paths) > 0) {
        $relative_path = substr($css_paths[0], strlen(PBRAIN_PLUGIN_DIR));
        $url_path = plugins_url($relative_path, PBRAIN_PLUGIN_DIR . basename(PBRAIN_PLUGIN_NAME));
        wp_enqueue_style('pbrain-svelte-css', $url_path, [], PBRAIN_PLUGIN_VER);
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
        && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_ajax_nonce'])), 'pbrain-ajax-nonce')
      ) {
        // Input var okay.
        $user_settings = json_decode(sanitize_text_field(wp_unslash($_POST['data'])));
        $new_settings = [
          'chatbot_id' => !empty($user_settings->chatbotId)
            ? $user_settings->chatbotId
            : $this->options['chatbot_id'],
          'onboard_id' => !empty($user_settings->onboardId)
            ? $user_settings->onboardId
            : $this->options['onboard_id'],
          'signing_key' => !empty($user_settings->signingKey)
            ? $user_settings->signingKey
            : $this->options['signing_key']
        ];

        $updated = update_option('pbrain_settings', $new_settings);
        $this->options = $new_settings;
        return wp_send_json([
          'status' => 'success',
          'chatbotId' => $new_settings['chatbot_id'],
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
      $url = get_site_url();
      $site_name = get_bloginfo();
      $current_user = wp_get_current_user();
      $email = $current_user->user_email;
      $admin_name = !empty($current_user->display_name)
        ? $current_user->display_name
        : (!empty($current_user->user_firstname)
          ? $current_user->user_firstname
          : (!empty($current_user->user_lastname)
            ? $current_user->user_lastname
            : $current_user->user_login));
?>
      <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <div id="pbrain-options" data-chatbot-id="<?php echo isset($this->options['chatbot_id']) ? esc_attr($this->options['chatbot_id']) : '' ?>" data-onboard-id="<?php echo isset($this->options['onboard_id']) ? esc_attr($this->options['onboard_id']) : '' ?>" data-signing-key="<?php echo isset($this->options['signing_key']) ? esc_attr($this->options['signing_key']) : '' ?>" data-url="<?php echo esc_attr($url) ?>" data-email="<?php echo esc_attr($email) ?>" data-admin-name="<?php echo esc_attr($admin_name) ?>" data-site-name="<?php echo esc_attr($site_name) ?>">
        </div>
        <form method="post" action="options.php" style="display: none">
          <input type="hidden" name="pbrain_settings" value="" />
          <?php
          // This prints out all hidden setting fields
          settings_fields('pbrain_option_group');
          submit_button("Reset", 'secondary');
          ?>
        </form>
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

      if (isset($input['onboard_id'])) {
        $new_input['onboard_id'] = sanitize_text_field($input['onboard_id']);
      }

      if (isset($input['signing_key'])) {
        $new_input['signing_key'] = sanitize_text_field($input['signing_key']);
      }

      return $new_input;
    }
  }
}
