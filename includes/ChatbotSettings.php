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
      if (is_admin()) {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
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
          <form method="post" action="options.php">
          <?php
          // This prints out all hidden setting fields
          settings_fields('pbrain_option_group');
          do_settings_sections('pbrain-options');
          submit_button();
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

      add_settings_section(
        'pbrain_section_general', // ID
        __('General', 'pbrain'), // Title
        [$this, 'print_section_info'], // Callback
        'pbrain-options' // Page
      );

      // add_settings_field(
      //   'id_number', // ID
      //   'ID Number', // Title 
      //   array($this, 'id_number_callback'), // Callback
      //   'pbrain-options', // Page
      //   'pbrain_section_general' // Section           
      // );

      add_settings_field(
        'chatbot_id',
        __('PBrain id', 'pbrain'),
        [$this, 'chatbot_id_callback'],
        'pbrain-options',
        'pbrain_section_general'
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

      if (isset($input['chatbot_id']))
        $new_input['chatbot_id'] = sanitize_text_field($input['chatbot_id']);

      return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
      printf(
        '<p id="pbrain_section_general">%s</p>',
        esc_html_e('Generate leads and better engage your customers with your custom ChatGPT', 'pbrain')
      );
    }

    // /** 
    //  * Get the settings option array and print one of its values
    //  */
    // public function id_number_callback()
    // {
    //   printf(
    //     '<input type="text" id="id_number" name="pbrain_settings[id_number]" value="%s" />',
    //     isset($this->options['id_number']) ? esc_attr($this->options['id_number']) : ''
    //   );
    // }

    /** 
     * Get the settings option array and print one of its values
     */
    public function chatbot_id_callback()
    {
      printf(
        '<input type="text" id="chatbot_id" name="pbrain_settings[chatbot_id]" value="%s" />',
        isset($this->options['chatbot_id']) ? esc_attr($this->options['chatbot_id']) : ''
      );
    }
  }
}