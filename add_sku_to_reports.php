<?php
/**
 * Plugin Name: add_sku_to_reports
 * Plugin URI: https://wordpress.org/plugins/add_sku_to_reports
 * Description: A WooCommerce Admin Extension to add sku(s) to the analytics table reports woocommerce (Analytics -> Orders)
 * Version: 1.0.0
 * Author: Ayoub Bousetta
 * Author URI: http://abusta.com
 * Developer: Ayoub Bousetta
 * Developer URI: https://abusta.com
 * Domain Path: /languages
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */




/**
 * Register the JS.
 */
function add_extension_register_script() {
	if ( ! class_exists( 'Automattic\WooCommerce\Admin\Loader' ) || ! \Automattic\WooCommerce\Admin\Loader::is_admin_or_embed_page() ) {
		return;
	}
	
	$script_path       = '/build/index.js';
	$script_asset_path = dirname( __FILE__ ) . '/build/index.asset.php';
	$script_asset      = file_exists( $script_asset_path )
		? require( $script_asset_path )
		: array( 'dependencies' => array(), 'version' => filemtime( $script_path ) );
	$script_url = plugins_url( $script_path, __FILE__ );

	wp_register_script(
		'add_sku_to_reports',
		$script_url,
		$script_asset['dependencies'],
		$script_asset['version'],
		true
	);

	wp_register_style(
		'add_sku_to_reports',
		plugins_url( '/build/index.css', __FILE__ ),
		// Add any dependencies styles may have, such as wp-components.
		array(),
		filemtime( dirname( __FILE__ ) . '/build/index.css' )
	);

	wp_enqueue_script( 'add_sku_to_reports' );
	wp_enqueue_style( 'add_sku_to_reports' );
}

add_action( 'admin_enqueue_scripts', 'add_extension_register_script' );




/**
 * Show the phone number of the customer in the order analytics table
 * @param $results
 * @param $args
 * @return mixed
 */
add_filter('woocommerce_analytics_orders_select_query', function ($results, $args) {

    if ($results && isset($results->data) && !empty($results->data)) {
        foreach ($results->data as $key => $result) {


           
             $item_sku = array();
			  $products =  $result['extended_info']['products']; 
			

				// Loop through ordered items
			 $order = new WC_Order($result['order_id']);
    $items = $order->get_items();

    // Loop through each item of the order
    foreach ( $items as $item ) {
        $product_variation_id = $item['variation_id'];

        if ($product_variation_id) { // IF Order Item is Product Variantion then get Variation Data instead
            $product = wc_get_product($item['variation_id']);
        } else {
            $product = wc_get_product($item['product_id']);
        }

        if ($product) { // Product might be deleted and not exist anymore    
            $item_sku[] = $product->get_sku();                    
        }  
    }   


            //get the order item data here
            // ...........................
            
            //here is how i did it for the customers SKU number
            $results->data[$key]['products_sku'] = implode(" - ", $item_sku);
        }
    }

    return $results;
}, 10, 2);


/**
 * Add the phone number column to the CSV file
 * @param $export_columns
 * @return mixed
 */
add_filter('woocommerce_report_orders_export_columns', function ($export_columns){
    $export_columns['products_sku'] = _e( 'SKU', 'woocommerce' );
    return $export_columns;
});

/**
 * Add the phone number data to the CSV file
 * @param $export_item
 * @param $item
 * @return mixed
 */
add_filter('woocommerce_report_orders_prepare_export_item', function ($export_item, $item){
    $export_item['products_sku'] = $item['products_sku'];
    return $export_item;
}, 10, 2);

