<?php
/**
 * Woo Custom Empty Price Core class
 *
 * Provides core methods for the Woo Custom Empty Price plugin.
 *
 * @package Woo_Custom_Empty_Price
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Woo_Custom_Empty_Price_Utils class
 */
class Woo_Custom_Empty_Price_Core {

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

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_filter( 'woocommerce_empty_price_html', array( $this, 'render_custom_empty_price'), 10, 2  );
		add_filter( 'woocommerce_grouped_empty_price_html', array( $this, 'render_custom_empty_price'), 10, 2  );
		add_filter( 'woocommerce_variable_empty_price_html', array( $this, 'render_custom_empty_price'), 10, 2  );
		add_filter( 'woocommerce_locate_template', array( $this, 'try_plugin_template'), 1, 3 );
    }

    /**
	 * Register the stylesheets for the front end.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( dirname( __FILE__ ) ) . 'css/woo-custom-empty-price.css', array(), $this->version, 'all' );
	}

	/**
	 * Shows the custom content in place of an empty price. The main front-end function of the plugin.
	 *
	 * @since    1.0.0
	 * @param      string $output       The html to output.
	 */
	public static function render_custom_empty_price() {

		$options = get_option( 'woo_custom_empty_price_options' );

		global $woocommerce_loop;
		if ( ! isset( $options['activate_settings'] ) || ( is_null( $woocommerce_loop ) && ! is_product() ) || ( ! is_null( $woocommerce_loop ) && strlen( $woocommerce_loop['name'] ) ) ) {
			return;
		}
		$output = '';
		if ( substr( $options['text_class'], 0, 1 ) === '.' ) {
			$options['text_class'] = ltrim( $options['text_class'], '.' );
		}
		if ( substr( $options['cta_class'], 0, 1 ) === '.' ) {
			$options['cta_class'] = ltrim( $options['cta_class'], '.' );
		}
		switch ( $options['content_type'] ) {
			case 'text':
				$output = '<p class="cep-' . $options['text_size'] . ' ' . $options['text_class'] . '" style="color:' . $options['text_colour'] . ( isset( $options['text_bold'] ) ? ';font-weight:bold' : '' ) . ( isset( $options['text_italic'] ) ? ';font-style:italic' : '' ) . '">' . $options['text_content'] . '</p>';
				break;
			case 'cta':
				if ( ! strlen( $options['cta_content'] ) ) {
					break;
				}
				$output = '<a class="wp-block-button__link wp-element-button wc-block-components-product-button__button add_to_cart_button has-text-align-center ' . $options['cta_class'] . '" style="color: ' . $options['cta_text_colour'] . ';background-color:' . $options['cta_bg_colour'] . '" href="' . $options['cta_url'] . '" ' . ( isset( $options['cta_target'] ) ? 'target="_blank"' : '' ) . '>' . $options['cta_content'] . '</a>';
				break;
			case 'html':
				$output = html_entity_decode( $options['html_content'] );
				break;

			default:
				$output = '';
		}
		// Fetch output
		return do_shortcode($output);
	}

   /**
	* Tries to use a plugin-specific template for the product price display.
	*
	* This function is used to override the default WooCommerce product price template
	* with a custom template provided by the plugin. It checks if the current product
	* has an empty price, and if so, it attempts to load the plugin's custom template
	* instead of the default one.
	*
	* @param string $template The default template path.
	* @param string $template_name The name of the template.
	* @param string $template_path The path to the template.
	* @return string The modified template path, or the original template path if the plugin's template is not used.
	*/
 	public function try_plugin_template( $template, $template_name, $template_path ) {
    	global $woocommerce;
		
		$product = wc_get_product();

		if(!$product) return $template;
		
		if ( '' !== $product->get_price()) return $template;
    	$_template = $template;
    	
		if ( ! $template_path ) 
        	$template_path = $woocommerce->template_url;
 		
		$plugin_path  = untrailingslashit( plugin_dir_path( dirname( __FILE__ ) ) )  . '/templates/';

		if( $this->template_path === $template_name )
			$template = $plugin_path . $template_name;
 
		if ( ! $template )
			$template = $_template;
		
		return $template;
	}
}
