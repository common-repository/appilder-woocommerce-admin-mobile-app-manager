<?php
/**
 * Plugin Name: Appilder WooCommerce Admin Mobile App Manager
 * Plugin URI: https://appilder.com/
 * Description:  Manage mobile app created for woocommerce store admins to manage orders, view orders and get push notification
 * Version: 0.1
 * Author: Appilder
 * Author URI: https://appilder.com
 * Requires at least: 3.8
 * Tested up to: 4.2.2
 */

class Appilder_woocommerce_admin_app
{
	static $_pusher = false;
    public static function add_api($classes)
    {
        if(!class_exists('Appilder_woocommerce_admin_api')){
            require_once(plugin_dir_path(__FILE__).'appilder-woocommerce-admin-api.php');
        }
        $classes[] = 'Appilder_woocommerce_admin_api';
        return $classes;
    }

    public static function init()
    {
         add_action('admin_init',array(self::name(), 'appilder_woo_admin_register_settings'));
		 add_action('admin_menu', array(self::name(),'appilder_woo_admin_register_options_page'),99);
		 add_action('woocommerce_checkout_order_processed', array(self::name(),'order_processed_notification'));
         add_filter('woocommerce_api_classes',array(self::name(),'add_api'),1);
		 add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array(self::name(),'appilder_woo_admin_add_plugin_action_links'));

	}
	public static function send_notification($message,$title,$action,$param){
		if(self::$_pusher == false){
			if(!class_exists("Appilder_woocommerce_admin_push")){
				require_once(plugin_dir_path(__FILE__).'appilder-woocommerce-admin-push.php');
			}
			self::$_pusher = new Appilder_woocommerce_admin_push();
		}
		self::$_pusher->sendPush($message,$title,$action,$param);
	}

	public static function order_processed_notification($order_id){
		$order = new WC_Order( $order_id );
		self::send_notification(__("Order ID : #").$order_id.__(" Total : ").$order->get_total()." ".$order->get_order_currency()."",__("New Order from ").$order->get_user()->nickname,"open_order",$order_id);
	}

    public static function name()
    {
        return 'appilder_woocommerce_admin_app';
    }

    public static function  appilder_woo_admin_register_settings()
    {
        add_option('appilder_woo_admin_gcm_key', '');
        register_setting('default', 'appilder_woo_admin_gcm_key');
    }

    public static function appilder_woo_admin_register_options_page()
    {
        add_submenu_page('woocommerce', 'WooCommerce Admin App Settings', 'Admin Mobile App', 'manage_options', 'appilder-woocomerce-admin-app', array(self::name(), 'appilder_woo_admin_options_page'));
    }

	public static function appilder_woo_admin_options_page() {
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2>Appilder WooCommerce Admin App</h2>
			<form method="post" action="options.php">
				<?php settings_fields( 'default' ); ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="appilder_woo_admin_gcm_key">GCM KEY</label></th>
						<td><input type="text" id="appilder_woo_admin_gcm_key" name="appilder_woo_admin_gcm_key" value="<?php echo get_option('appilder_woo_admin_gcm_key'); ?>" /></td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
	public static function appilder_woo_admin_add_plugin_action_links( $links ) {
		return array_merge(
			array(
				'settings' => '<a href="' . get_bloginfo( 'wpurl' ) . '/wp-admin/admin.php?page=appilder-woocomerce-admin-app">Settings</a>',
				'create_app' => '<a target="_blank" href="https://appilder.com/">Create App</a>' ),
			$links
		);
	}
}
add_action('woocommerce_loaded',array(appilder_woocommerce_admin_app::name(),'init'));
