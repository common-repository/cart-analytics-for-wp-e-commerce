<?php 
/*
Plugin Name: Cart Analytics for WP e-Commerce
Description: Stores how many products added to cart are actually purchased. WP e-Commerce needed.
Author: Francesco Lentini
Version: 1.0
Plugin URI: https://github.com/flentini/cart-analytics
Author URI: http://spugna.org/tpk
*/
session_start();
require_once('ca_cart.php');

global $cart_analytics;
$cart_analytics = new CartAnalytics();

require_once('json_data.php');
define('CA_URLPATH', WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)));

if (isset($_POST['date_select'])) {
	$cart_analytics->set_selected($_POST['date_select']);
}
$_SESSION['ca_data']['ca_status'] = false;
$_SESSION['ca_data']['data'] = $cart_analytics->get_records($cart_analytics->get_selected());
 	
add_action('admin_menu', 'init_menu');
add_action('wpsc_set_cart_item', array(&$cart_analytics, 'add_to_cart'),10,3);
add_action('wpsc_save_cart_item', array(&$cart_analytics, 'success_checkout'),10,3);
add_action('wpsc_bottom_of_shopping_cart', array(&$cart_analytics, 'update_before_checkout'),10,3);
add_action('wp_logout', 'ca_session_destroy');  

function init_menu() {
    wp_enqueue_script('jsapi', 'https://www.google.com/jsapi');
	$ad_opt_page = add_menu_page('Cart Analytics', 'Cart Analytics', 'manage_options', 'cart-analytics-plugin', 'show_table',CA_URLPATH .'/css/cart_go.png');
	add_action('admin_print_styles-'.$ad_opt_page, wp_enqueue_style('ca-css', CA_URLPATH .'/css/styles.css', false, false, 'all'));
	if(isset($_SESSION['ca_data']['ca_status'])){
		add_action('admin_print_scripts-'.$ad_opt_page, wp_enqueue_script('ca_charts', CA_URLPATH . '/js/gchart.php'));	
	}
}

function ca_session_destroy(){
	if(isset($_SESSION['ca_data'])){
		unset($_SESSION['ca_data']);
	}
}

function show_table() {
global $cart_analytics;
$_SESSION['ca_data']['ca_path'] = CA_URLPATH;
$_SESSION['ca_data']['ca_date_select'] = $cart_analytics->get_selected();

$records = $_SESSION['ca_data']['data']['records'];
$products = $_SESSION['ca_data']['data']['products'];

if (!current_user_can('manage_options'))  {
				wp_die( __('You do not have sufficient permissions to access this page.') );
	} ?>
	<div class="wrap">
		<p><h3>Cart Analytics</h3></p>
		<form name="ca_form" method="post" action="<?php str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
			<select name="date_select" id="date_select">
				<option value="">select...</option>
				<?php
				foreach($cart_analytics->get_dates() as $date){
					echo '<option value="'.$date->date_cart.'">'.$date->date_cart.'</option>';
				}
				?>
			</select>
			<input type="submit" class='button-secondary' name="submit" id="submit" value="go" />
		</form>
		<p>	
		<?php if($records!=false) { ?>
		<table class="widefat">
		<thead>
	    	<tr>
		        <th>Product</th>
		        <th>Added</th>
		        <th>Purchased</th>
	    	</tr>
		</thead>
		<tbody>
			<?php
			foreach($records as $r){
					$p = $products[$r->product_id];
					echo '<tr>';
					echo '<td><a href="'.get_permalink($p->ID).'">'.$p->post_title.'</a></td>';
					echo '<td>'.$r->added.'</td>';	
					echo '<td>'.$r->purchased.'</td>';
					echo '</tr>';
				}
			?>
		</tbody>
		</table>
		</p>
		<p>
			<div class="chart_date"><?php echo $cart_analytics->get_selected(); ?></div>
			<div class="chart" id="chart_div"></div>
		</p>
	</div>
	<?php } else {
				echo __('There are no stats at the moment.');
		}
}
