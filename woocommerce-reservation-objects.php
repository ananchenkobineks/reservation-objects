<?php
/**
 * Plugin Name: WooCommerce Reservation Objects
 * Description: Booking object
 * Version: 1.0
 * Author: Sleemeks
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists('WC_ReservObj') ) :

final class WC_ReservObj {

    protected static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    private function define_constants() {

        $this->define( 'RES_OBJ_PLUGIN_FILE', __FILE__ );
        $this->define( 'RES_OBJ_ABSPATH', dirname( __FILE__ ) . '/' );
        $this->define( 'RES_OBJ_TEMPLATE', RES_OBJ_ABSPATH . 'templates/' );
        $this->define( 'RES_OBJ_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) . 'assets/' );
    }

    private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin' :
                return is_admin();
            case 'ajax' :
                return defined( 'DOING_AJAX' );
            case 'cron' :
                return defined( 'DOING_CRON' );
            case 'frontend' :
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }
    }

    private function init_hooks() {

        register_activation_hook( RES_OBJ_PLUGIN_FILE, array( 'ResObj_Install', 'install' ) );

        add_action( 'wp_enqueue_scripts', array($this, 'reservobj_scripts_front') );
        add_action( 'admin_enqueue_scripts', array($this, 'reservobj_scripts_admin') );

        add_action( 'plugins_loaded', array($this, 'init_translation') );
    }

    private function includes() {

        include_once( RES_OBJ_ABSPATH . 'includes/class-resobj-install.php' );

        include_once( RES_OBJ_ABSPATH . 'includes/class-resobj-helper.php' );

        include_once( RES_OBJ_ABSPATH . 'includes/class-resobj-post-type.php' );
        include_once( RES_OBJ_ABSPATH . 'includes/class-resobj-wc-product-tab.php' );
        include_once( RES_OBJ_ABSPATH . 'includes/class-resobj-wc-product-page.php' );
        include_once( RES_OBJ_ABSPATH . 'includes/class-resobj-wc-checkout.php' );

        /**
         * Admin
         */
        include_once( RES_OBJ_ABSPATH . 'includes/admin/class-resobj-reservation-list-table.php' );
        include_once( RES_OBJ_ABSPATH . 'includes/admin/class-resobj-admin.php' );
    }

    public function reservobj_scripts_front() {

        if( is_product() && is_single() ) {
            wp_register_script( 'jquery-ui', RES_OBJ_PLUGIN_DIR_URL . 'js/jquery-ui.min.js', array( 'jquery' ), '1.12.1', true );
            wp_register_script( 'resobj-front-booking', RES_OBJ_PLUGIN_DIR_URL . 'js/front-booking.js', array( 'jquery' ), false, true );

            wp_enqueue_script( 'jquery-ui' );
            wp_enqueue_script( 'resobj-front-booking' );


            $localize_data = array(
                'url' => admin_url('admin-ajax.php'),
                'post_id' => get_the_ID()
            );

            $get_cart = WC()->cart->get_cart();
            foreach( $get_cart as $cart ) {
                if( !empty($cart['res_obj']) && get_the_ID() == $cart['product_id'] ) {
                    $localize_data['preselect'] = $cart['res_obj'];
                    break;
                }
            }

            wp_localize_script( 'resobj-front-booking', 'ajaxObj', $localize_data );
        }
    }

    public function reservobj_scripts_admin() {
        if( get_current_screen()->post_type == "res_obj" ) {

            wp_register_style( 'resobj-admin', RES_OBJ_PLUGIN_DIR_URL . 'css/admin.css' );
            wp_register_style( 'jquery-ui', RES_OBJ_PLUGIN_DIR_URL . 'css/jquery-ui.min.css', array(), '1.12.1' );
            wp_enqueue_style( 'resobj-admin' );
            wp_enqueue_style( 'jquery-ui' );

            wp_register_script( 'jquery-ui', RES_OBJ_PLUGIN_DIR_URL . 'js/jquery-ui.min.js', array( 'jquery' ), false, true );
            wp_register_script( 'resobj-admin-booking', RES_OBJ_PLUGIN_DIR_URL . 'js/admin-booking.js', array( 'jquery' ), false, true );
            wp_enqueue_script( 'jquery-ui' );
            wp_enqueue_script( 'resobj-admin-booking' );
        }
    }

    public function init_translation() {
        load_plugin_textdomain( 'woocommerce-reservation-objects', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }
}

endif;

WC_ReservObj::instance();