<?php
/**
 * Woo Custom Empty Price Settings class
 *
 * Handles the registration and rendering of the plugin settings page.
 *
 * @package Woo_Custom_Empty_Price
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Woo_Custom_Empty_Price_Settings class
 */
class Woo_Custom_Empty_Price_Settings {
	
	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The current content type.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string    $content_type    The current stored content type.
	 */
	private $content_type;

	/**
	 * The template path.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string    $template_path    The template path.
	 */
	private $template_path;


    /**
     * Constructor
     *
     * Sets up the necessary actions for registering the plugin settings
     * and adding the settings page to the WordPress admin menu.
     */
    public function __construct() {
        $this->version = '2.0.0';
        $this->plugin_name = 'woo-custom-empty-price';
		$this->template_path = 'single-product/price.php';

		$options            = get_option( 'woo_custom_empty_price_options' );
		$this->content_type = isset( $options['content_type'] ) ? $options['content_type'] : null;

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'admin_menu', array( $this, 'setup_plugin_settings_menu' ) );
		add_action( 'admin_menu', array( $this, 'initialize_options' ) );	
    }
	
	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_admin_styles() {
		
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( dirname( __FILE__ ) ) . 'css/woo-custom-empty-price-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( dirname( __FILE__ ) ) . 'js/woo-custom-empty-price-admin.js', array( 'wp-color-picker', 'jquery' ), $this->version, false );
	}

	/**
	 * This function adds a plugin options page under the 'Settings' menu.
	 */
	public function setup_plugin_settings_menu() {

        add_options_page(
            'Settings Admin',
            'Custom Empty Price',
            'manage_options',
            'woo-custom-empty-price-settings',
            array( $this, 'render_settings_page_content' )
        );
    }

	/**
	 * Initializes the plugin's options page by registering the Sections,
	 * Fields, and Settings.
	 */
	public function initialize_options() {

		// Set the defaults for the display settings, if none exist.
		if ( false === get_option( 'woo_custom_empty_price_options' ) ) {
			$default_array = $this->default_options();
			update_option( 'woo_custom_empty_price_options', $default_array );
		}

		$this->add_general_settings();
		$this->add_text_settings();
		$this->add_cta_settings();
		$this->add_html_settings();
	}

	/**
	 * Adds the fields & registers the 'general' settings section.
	 */
	public function add_general_settings() {

		add_settings_section(
			'general_settings_section',                     // ID used to identify this section and with which to register options
			'', 											// Title to be displayed on the administration page
			array( $this, 'general_options_callback' ),     // Callback used to render the description of the section
			'woo_custom_empty_price_options'          		// Page on which to add this section of options
		);

		// The checkbox to determine if you want to activate the plugins functionality.
		add_settings_field(
		'activate_settings',                                   				 // ID used to identify the field throughout the theme
			__( 'Activate the settings?', 'woo-custom-empty-price-plugin' ), // The label to the left of the option interface element
			array( $this, 'activate_settings_callback' ),          			 // The name of the function responsible for rendering the option interface
			'woo_custom_empty_price_options',                          		 // The page on which this option will be displayed
			'general_settings_section',                             		 // The name of the section to which this field belongs
			array(                                                  		 // The array of arguments to pass to the callback. In this case, just a description.
				__( 'This toggles the plugins active state.', 'woo-custom-empty-price-plugin' ),
			)
		);

		// The dropdown to select which of the content types you want to display.
		add_settings_field(
			'content_type',                           				// ID used to identify the field throughout the theme
			__( 'Content type', 'woo-custom-empty-price-plugin' ), 	// The label to the left of the option interface element
			array( $this, 'content_type_callback' ),  				// The name of the function responsible for rendering the option interface
			'woo_custom_empty_price_options',               		// The page on which this option will be displayed
			'general_settings_section',                  			// The name of the section to which this field belongs
			array(                                                  // The array of arguments to pass to the callback. In this case, just a description.
				__( 'The content type you would like to use. ( Plain text, a call to action buttton, or custom HTML. )', 'woo-custom-empty-price-plugin' ),
			)
		);

		// Finally, we register the fields with WordPress
		register_setting(
			'general_settings_section',
			'woo_custom_empty_price_options',
			array( $this, 'sanitize_options' )
		);
	}

	/**
	 * Adds the fields & registers the 'text' settings section
	 */
	public function add_text_settings() {

		$row_class = $this->get_row_class( 'text' );

		add_settings_section(
			'text_settings_section',			// ID used to identify this section and with which to register options
			'', 								// Title to be displayed on the administration page
			null,     							// Callback used to render the description of the section
			'woo_custom_empty_price_options'	// Page on which to add this section of options
		);

		// The text that you would like to show.
		add_settings_field(
			'text_content',                           				// ID used to identify the field throughout the theme
			__( 'Text content', 'woo-custom-empty-price-plugin' ), 	// The label to the left of the option interface element
			array( $this, 'text_content_callback' ),  				// The name of the function responsible for rendering the option interface
			'woo_custom_empty_price_options',               		// The page on which this option will be displayed
			'text_settings_section',                  				// The name of the section to which this field belongs
			array(                                                  // The array of arguments to pass to the callback. In this case, just a description.
				__( 'The actual text you would like displayed whenever a price is empty.', 'woo-custom-empty-price-plugin' ),
				'class' => $row_class,
			)
		);

		// The size of the text: small, medium or large.
		add_settings_field(
			'text_size',                           				// ID used to identify the field throughout the theme
			__( 'Text size', 'woo-custom-empty-price-plugin' ), // The label to the left of the option interface element
			array( $this, 'text_size_callback' ),  				// The name of the function responsible for rendering the option interface
			'woo_custom_empty_price_options',               	// The page on which this option will be displayed
			'text_settings_section',                  			// The name of the section to which this field belongs
			array(                                              // The array of arguments to pass to the callback. In this case, just a description.
				__( 'The size of the text ( S/M/L )', 'woo-custom-empty-price-plugin' ),
				'class' => $row_class,
			)
		);

		// The text colour.
		add_settings_field(
			'text_colour',
			'Text colour',
			array( $this, 'text_colour_callback' ),
			'woo_custom_empty_price_options',
			'text_settings_section',
			array(
				'class' => $row_class,
			)
		);

		// The checkbox to determine if you want to activate the plugins functionality.
		add_settings_field(
			'text_bold',                                    // ID used to identify the field throughout the theme
			__( 'Bold?', 'woo-custom-empty-price-plugin' ), // The label to the left of the option interface element
			array( $this, 'text_bold_callback' ),           // The name of the function responsible for rendering the option interface
			'woo_custom_empty_price_options',               // The page on which this option will be displayed
			'text_settings_section',                        // The name of the section to which this field belongs
			array(                                          // The array of arguments to pass to the callback. In this case, just a description.
				__( 'Check for <strong>bold</strong> text.', 'woo-custom-empty-price-plugin' ),
				'class' => $row_class,
			)
		);

		// The checkbox to determine if you want to activate the plugins functionality.
		add_settings_field(
			'text_italic',                                    // ID used to identify the field throughout the theme
			__( 'Italic?', 'woo-custom-empty-price-plugin' ), // The label to the left of the option interface element
			array( $this, 'text_italic_callback' ),           // The name of the function responsible for rendering the option interface
			'woo_custom_empty_price_options',                 // The page on which this option will be displayed
			'text_settings_section',                          // The name of the section to which this field belongs
			array(                                            // The array of arguments to pass to the callback. In this case, just a description.
				__( 'Check for <em>italic</em> text.', 'woo-custom-empty-price-plugin' ),
				'class' => $row_class,
			)
		);

		// Custom CSS class for additional styling of text.
		add_settings_field(
			'text_class',
			'Custom CSS class',
			array( $this, 'text_class_callback' ),
			'woo_custom_empty_price_options',
			'text_settings_section',
			array(
				__( 'Custom CSS class for additional styling of text (with or without a preceding dot, it doesn\'t matter)', 'woo-custom-empty-price-plugin' ),
				'class' => $row_class,
			)
		);

		// Finally, we register the fields with WordPress
		register_setting(
			'text_settings_section',
			'woo_custom_empty_price_options',
			array( $this, 'sanitize_options' )
		);
	}

	/**
	 * Adds the fields & registers the 'cta' settings section
	 */
	public function add_cta_settings() {

		$row_class = $this->get_row_class( 'cta' );
		add_settings_section(
			'cta_settings_section', 		 // ID used to identify this section and with which to register options
			'', 							 // Title to be displayed on the administration page
			null,     						 // Callback used to render the description of the section
			'woo_custom_empty_price_options' // Page on which to add this section of options
		);

		// Button text.
		add_settings_field(
			'cta_content',                           			  // ID used to identify the field throughout the theme
			__( 'Button text', 'woo-custom-empty-price-plugin' ), // The label to the left of the option interface element
			array( $this, 'cta_content_callback' ),  			  // The name of the function responsible for rendering the option interface
			'woo_custom_empty_price_options',               	  // The page on which this option will be displayed
			'cta_settings_section',                  			  // The name of the section to which this field belongs
			array(                                                // The array of arguments to pass to the callback. In this case, just a description.
				__( 'The text for the displayed button whenever a price is empty.', 'woo-custom-empty-price-plugin' ),
				'class' => $row_class,
			)
		);

		// Button URL.
		add_settings_field(
			'cta_url',                           				 // ID used to identify the field throughout the theme
			__( 'Button URL', 'woo-custom-empty-price-plugin' ), // The label to the left of the option interface element
			array( $this, 'cta_url_callback' ),  				 // The name of the function responsible for rendering the option interface
			'woo_custom_empty_price_options',               	 // The page on which this option will be displayed
			'cta_settings_section',                  			 // The name of the section to which this field belongs
			array(                                               // The array of arguments to pass to the callback. In this case, just a description.
				__( 'The button URL (with or without a preceding https://, it doesn\'t matter)', 'woo-custom-empty-price-plugin' ),
				'class' => $row_class,
			)
		);

		// The checkbox to determine if you want to activate the plugins functionality.
		add_settings_field(
			'cta_target',                                   			 // ID used to identify the field throughout the theme
			__( 'Open in a new tab?', 'woo-custom-empty-price-plugin' ), // The label to the left of the option interface element
			array( $this, 'cta_target_callback' ),          			 // The name of the function responsible for rendering the option interface
			'woo_custom_empty_price_options',                          	 // The page on which this option will be displayed
			'cta_settings_section',                             		 // The name of the section to which this field belongs
			array(                                                  	 // The array of arguments to pass to the callback. In this case, just a description.
				__( 'Check this to open the link in a new tab|window.', 'woo-custom-empty-price-plugin' ),
				'class' => $row_class,
			)
		);

		// Button colour.
		add_settings_field(
			'cta_bg_colour',
			'Button colour',
			array( $this, 'cta_bg_colour_callback' ),
			'woo_custom_empty_price_options',
			'cta_settings_section',
			array(
				'class' => $row_class,
			)
		);

		// Button text colour.
		add_settings_field(
			'cta_text_colour',
			'Text colour',
			array( $this, 'cta_text_colour_callback' ),
			'woo_custom_empty_price_options',
			'cta_settings_section',
			array(
				'class' => $row_class,
			)
		);

		// Custom button class.
		add_settings_field(
			'cta_class',
			'Custom CSS class',
			array( $this, 'cta_class_callback' ),
			'woo_custom_empty_price_options',
			'cta_settings_section',
			array(
				__( 'Custom CSS class for additional styling of the button (with or without a preceding dot, it doesn\'t matter)', 'woo-custom-empty-price-plugin' ),
				'class' => $row_class,
			)
		);

		// Finally, we register the fields with WordPress
		register_setting(
			'cta_settings_section',
			'woo_custom_empty_price_options',
			array( $this, 'sanitize_options' )
		);
	}

	/**
	 * Adds the fields & registers the 'html' settings section
	 */
	public function add_html_settings() {

		$row_class = $this->get_row_class( 'html' );
		add_settings_section(
			'html_settings_section',         // ID used to identify this section and with which to register options
			'',                              // Title to be displayed on the administration page
			null,                            // Callback used to render the description of the section
			'woo_custom_empty_price_options' // Page on which to add this section of options
		);

		// The custom HTML content. This uses a stripped down WYSIWYG editor using wp_editor().
		add_settings_field(
			'html_content',                                        // ID used to identify the field throughout the theme
			__( 'Custom HTML', 'woo-custom-empty-price-plugin' ), // The label to the left of the option interface element
			array( $this, 'html_content_callback' ),               // The name of the function responsible for rendering the option interface
			'woo_custom_empty_price_options',                      // The page on which this option will be displayed
			'html_settings_section',                               // The name of the section to which this field belongs
			array(                                                 // The array of arguments to pass to the callback. In this case, just a description.
				__( 'Enter your custom HTML here.', 'woo-custom-empty-price-plugin' ),
				'class' => $row_class,
			)
		);

		// Finally, we register the fields with WordPress
		register_setting(
			'html_settings_section',
			'woo_custom_empty_price_options',
			array( $this, 'sanitize_options' )
		);
	}

	/**
	 * Renders the page to display the plugin settings.
	 */
	public function render_settings_page_content() {
		?>
		<!-- Create a header in the default WordPress 'wrap' container. -->
		<div class="wrap">

			<h2><?php _e( 'Woo Custom Empty Price Options', $this->plugin_name ); ?></h2>

			<form method="post" action="options.php">
			<?php

			settings_fields( 'general_settings_section' );
			settings_fields( 'text_settings_section' );
			settings_fields( 'cta_settings_section' );
			settings_fields( 'html_settings_section' );
			do_settings_sections( 'woo_custom_empty_price_options' );
			submit_button( 'Update Settings' );

			?>
			</form>

		</div><!-- /.wrap -->
		<?php
	}

	/**
	 * This function provides a simple description for the Options page.
	 *
	 * It's called from the 'initialize_options' function by being passed as a parameter
	 * in the add_settings_section function.
	 */
	public function general_options_callback() {

		$markup = '<p>These settings determine what you would like to show on a single product if no price is set, instead of the default <em>empty price html</em>.</p>';
		echo $markup;
	}

	/**
	 * This function renders the checkbox field to indicate whether you want to activate the plugin or not.
	 */
	public function activate_settings_callback( $args ) {

		// First, we read the options collection
		$options = get_option( 'woo_custom_empty_price_options' );
		// Generate a checkbox and set its default checked/unchecked state.
		$html = '<input type="checkbox" id="activate_settings" name="woo_custom_empty_price_options[activate_settings]" value="1" ' . ( ( isset( $options['activate_settings'] ) ) ? 'checked="checked"' : '' ) . ' />';
		// Here, we'll take the first argument of the array and add it to a label next to the checkbox
		$html .= '<label for="activate_settings">&nbsp;' . $args[0] . '</label>';

		echo $html;
	}

	/**
	 * This function renders the <select> tag for the content types.
	 */
	public function content_type_callback( $args ) {

		$options       = get_option( 'woo_custom_empty_price_options' );
		$content_types = [
			'text' => 'Plain text',
			'cta'  => 'Call to action button',
			'html' => 'Custom HTML',
		];
		$html          = '<select id="content_type" name="woo_custom_empty_price_options[content_type]">';
		$html         .= '<option value="">Choose a content type</option>';

		foreach ( $content_types as $key => $value ) {
			$html .= '<option value="' . $key . '" ' . ( ( $key === $this->content_type ) ? 'selected="selected"' : '' ) . '>' . $value . '</option>';
		}
		$html .= '</select>';
		// Here, we'll take the first argument of the array and add it to a label next to the checkbox
		$html .= '<label for="content_type">&nbsp;' . $args[0] . '</label>';

		echo $html;
	}

	/**
	 * This function renders the text field for storing the text content.
	 */
	public function text_content_callback( $args ) {

		$options = get_option( 'woo_custom_empty_price_options' );
		$html = '<input type="text" id="text_content" name="woo_custom_empty_price_options[text_content]" value="' . ( isset( $options['text_content'] ) ? $options['text_content'] : '' ) . '"/>';
		// Here, we'll take the first argument of the array and add it to a label next to the checkbox
		$html .= '<label for="text_content">&nbsp;' . $args[0] . '</label>';

		echo $html;
	}


	/**
	 * This function renders the <select> tag for the text sizes.
	 */
	public function text_size_callback( $args ) {
		$options    = get_option( 'woo_custom_empty_price_options' );
		$text_sizes = [
			'small'  => 'Small',
			'medium' => 'Medium',
			'large'  => 'Large',
		];
		$html       = '<select id="text_size" name="woo_custom_empty_price_options[text_size]">';
		$html      .= '<option value="">Choose a text size</option>';

		foreach ( $text_sizes as $key => $value ) {
			$html .= '<option value="' . $key . '" ' . ( ( isset( $options['text_size'] ) && $key === $options['text_size'] ) ? 'selected="selected"' : '' ) . '>' . $value . '</option>';
		}
		$html .= '</select>';
		// Here, we'll take the first argument of the array and add it to a label next to the checkbox
		$html .= '<label for="text_size">&nbsp;' . $args[0] . '</label>';

		echo $html;

		// Display the informational box
		$info_box = '<p class="description">';
		$info_box .= __( 'Please note, this will add a CSS class ( either <code>.cep-small</code>, <code>.cep-medium</code>, or <code>.cep-large</code> ) onto the element to adjust the size. However, your theme CSS may also affect this sizing.', 'woo-custom-empty-price-plugin' );
		$info_box .= '</p>';
		echo $info_box;
	}


	/**
	 * This function renders the colour picker for the text colour setting.
	 */
	public function text_colour_callback() {

		$options = get_option( 'woo_custom_empty_price_options' );
		echo '<input name="woo_custom_empty_price_options[text_colour]" type="text" value="' . ( isset( $options['text_colour'] ) ? $options['text_colour'] : '' ) . '" class="colour_picker" data-default-color="#000000" />';
	}

	/**
	 * This function renders the checkbox field to indicate whether you want bold text or not.
	 */
	public function text_bold_callback( $args ) {

		// First, we read the options collection
		$options = get_option( 'woo_custom_empty_price_options' );
		// Generate a checkbox and set its default checked/unchecked state.
		$html = '<input type="checkbox" id="text_bold" name="woo_custom_empty_price_options[text_bold]" value="1" ' . ( ( isset( $options['text_bold'] ) ) ? 'checked="checked"' : '' ) . ' />';
		// Here, we'll take the first argument of the array and add it to a label next to the checkbox
		$html .= '<label for="text_bold">&nbsp;' . $args[0] . '</label>';
		echo $html;

	}

	/**
	 * This function renders the checkbox field to indicate whether you want italic text or not.
	 */
	public function text_italic_callback( $args ) {

		// First, we read the options collection
		$options = get_option( 'woo_custom_empty_price_options' );
		// Generate a checkbox and set its default checked/unchecked state.
		$html = '<input type="checkbox" id="text_italic" name="woo_custom_empty_price_options[text_italic]" value="1" ' . ( ( isset( $options['text_italic'] ) ) ? 'checked="checked"' : '' ) . ' />';
		// Here, we'll take the first argument of the array and add it to a label next to the checkbox
		$html .= '<label for="text_italic">&nbsp;' . $args[0] . '</label>';
		echo $html;

	}

	/**
	 * This function renders the text field for storing the text custom class.
	 */
	public function text_class_callback( $args ) {

		$options = get_option( 'woo_custom_empty_price_options' );
		$html = '<input type="text" id="text_class" name="woo_custom_empty_price_options[text_class]" value="' . ( isset( $options['text_class'] ) ? $options['text_class'] : '' ) . '"/>';
		// Here, we'll take the first argument of the array and add it to a label next to the checkbox
		$html .= '<label for="text_class">&nbsp;' . $args[0] . '</label>';
		echo $html;
	}

	/**
	 * This function renders the text field for storing the button text.
	 */
	public function cta_content_callback( $args ) {

		$options = get_option( 'woo_custom_empty_price_options' );
		$html = '<input type="text" id="cta_content" name="woo_custom_empty_price_options[cta_content]" value="' . ( isset( $options['cta_content'] ) ? $options['cta_content'] : '' ) . '"/>';
		// Here, we'll take the first argument of the array and add it to a label next to the checkbox
		$html .= '<label for="cta_content">&nbsp;' . $args[0] . '</label>';
		echo $html;
	}

	/**
	 * This function renders the text field for storing the button URL.
	 */
	public function cta_url_callback( $args ) {

		$options = get_option( 'woo_custom_empty_price_options' );
		$html = '<input type="text" id="text_content" name="woo_custom_empty_price_options[cta_url]" value="' . ( isset( $options['cta_url'] ) ? $options['cta_url'] : '' ) . '"/>';
		// Here, we'll take the first argument of the array and add it to a label next to the checkbox
		$html .= '<label for="cta_url">&nbsp;' . $args[0] . '</label>';
		echo $html;
	}

	/**
	 * This function renders the checkbox field to indicate whether you want to open the link in a new tab or not.
	 */
	public function cta_target_callback( $args ) {

		// First, we read the options collection
		$options = get_option( 'woo_custom_empty_price_options' );
		// Generate a checkbox and set its default checked/unchecked state.
		$html = '<input type="checkbox" id="cta_target" name="woo_custom_empty_price_options[cta_target]" value="1" ' . ( ( isset( $options['cta_target'] ) ) ? 'checked="checked"' : '' ) . ' />';
		// Here, we'll take the first argument of the array and add it to a label next to the checkbox
		$html .= '<label for="cta_target">&nbsp;' . $args[0] . '</label>';
		echo $html;
	}

	/**
	 * This function renders the colour picker for the button background colour setting.
	 */
	public function cta_bg_colour_callback() {

		$options = get_option( 'woo_custom_empty_price_options' );
		echo '<input name="woo_custom_empty_price_options[cta_bg_colour]" type="text" value="' . ( isset( $options['cta_bg_colour'] ) ? $options['cta_bg_colour'] : '' ) . '" class="colour_picker" data-default-color="#dd3333" />';
	}

	/**
	 * This function renders the colour picker for the button text colour setting.
	 */
	public function cta_text_colour_callback() {

		$options = get_option( 'woo_custom_empty_price_options' );
		echo '<input name="woo_custom_empty_price_options[cta_text_colour]" type="text" value="' . ( isset( $options['cta_text_colour'] ) ? $options['cta_text_colour'] : '' ) . '" class="colour_picker" data-default-color="#ffffff" />';
	}

	/**
	 * This function renders the text field for storing the custom button class.
	 */
	public function cta_class_callback( $args ) {

		$options = get_option( 'woo_custom_empty_price_options' );
		$html = '<input type="text" id="cta_class" name="woo_custom_empty_price_options[cta_class]" value="' . ( isset( $options['cta_class'] ) ? $options['cta_class'] : '' ) . '"/>';
		// Here, we'll take the first argument of the array and add it to a label next to the checkbox
		$html .= '<label for="cta_url">&nbsp;' . $args[0] . '</label>';
		echo $html;
	}

	/**
	 * Callback function for rendering the HTML content field
	 *
	 * @param array $args The arguments passed to the callback function.
	 */
	public function html_content_callback( $args ) {
		$options = get_option( 'woo_custom_empty_price_options' );
		$html_content = isset( $options['html_content'] ) ? $options['html_content'] : '';

		$editor_settings = array(
			'textarea_name' => 'woo_custom_empty_price_options[html_content]',
			'textarea_rows' => 10,
			'media_buttons' => true,
			'tinymce'       => array(
				'toolbar1' => 'bold italic bullist numlist alignleft aligncenter alignright link',
				'toolbar2' => '',
				'toolbar3' => '',
			),
		);

		wp_editor( $html_content, 'html_content', $editor_settings );

		$description = $args[0];
		$class = $args['class'];

		printf(
			'<p class="%s"><span class="description">%s</span></p>',
			esc_attr( $class ),
			esc_html( $description )
		);
	}

	/**
	 * Generates a class along with its visibility state, based on the section name. For example, if 'text' is passed
	 * it will generate the class 'text_settings_section hide' or 'text_settings_section show'
	 *
	 * @params $section  The name of the section ( text, cta or html ).
	 *
	 * @returns The classes as a string.
	 */
	public function get_row_class( $section ) {

		return $section . '_settings_section ' . ( ( $section === $this->content_type ) ? 'show' : 'hide' );
	}

	/**
	 * Sanitization callback for the display options. Since some of the display options are text inputs,
	 * this function loops through the incoming option and strips all tags and slashes from the values
	 * before serializing it.
	 *
	 * @params $input  The unsanitized collection of options.
	 *
	 * @returns The collection of sanitized values.
	 */
	public function sanitize_options( $input ) {

		// Define the array for the updated options
		$output = array();

		// Loop through each of the options sanitizing the data
		foreach ( $input as $key => $val ) {
			if ( isset( $input[ $key ] ) ) {
				if ( $key === 'html_content' ) {
					// Decode the HTML content
					$decoded_html = html_entity_decode( $input[ $key ], ENT_QUOTES, 'UTF-8' );
					
					// Check if the decoded HTML contains script tags
					if ( preg_match( '/<script\b[^>]*>(.*?)<\/script>/is', $decoded_html ) ) {
						// Remove any script tags and their contents from the decoded HTML
						$sanitized_html = preg_replace( '/<script\b[^>]*>(.*?)<\/script>/is', '', $decoded_html );
						
						// Remove any leftover closing script tags
						$sanitized_html = preg_replace( '/<\/script>/i', '', $sanitized_html );
						
						// Encode the sanitized HTML
						$output[ $key ] = htmlentities( $sanitized_html, ENT_QUOTES, 'UTF-8' );
						
						// Add a notification message
						add_settings_error(
							'woo_custom_empty_price_options',
							esc_attr( 'settings_updated' ),
							__( 'Script tags have been removed from the custom HTML for security reasons.', 'woo-custom-empty-price-plugin' ),
							'updated'
						);
					} else {
						// No script tags found, store the original encoded HTML
						$output[ $key ] = $input[ $key ];
					}
				} else {
					// Sanitize other input fields
					$output[ $key ] = wp_kses_post( $input[ $key ] );
				}
			}
		}

		// Return the new collection
		return apply_filters( 'sanitize_options', $output, $input );
	}


	/**
	 * Provides default values for the Display Options.
	 *
	 * @return array
	 */
	public function default_options() {

		$defaults = array(
			'text_size'       => 'medium',
			'text_colour'     => '#111111',
			'text_bold'       => 1,
			'text_class'      => '.cep-text',
			'cta_bg_colour'   => '#0073aa',
			'cta_text_colour' => '#ffffff',
			'cta_class'       => '.cep-button',
		);

		return $defaults;
	}
}
