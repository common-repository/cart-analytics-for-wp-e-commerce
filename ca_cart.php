<?php
if (!class_exists('CartAnalytics')) :
	class CartAnalytics {
		private $table_name;
		private $today;
		private $selected;
		
		function __construct(){
			global $wpdb;
			$this->table_name = $wpdb->prefix . "cart_analytics";
			$this->today = date("Y-m-d");
			$this->selected = $this->today;
			$this->init();
		}
		
		protected function init(){
			global $wpdb;
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			
			$sql = "CREATE TABLE " . $this->table_name . " (
			  ID mediumint(9) NOT NULL AUTO_INCREMENT,
			  product_id int NOT NULL,
			  added int DEFAULT 0 NOT NULL,
			  purchased int DEFAULT 0 NOT NULL,
			  date_cart date DEFAULT '0000-00-00' NOT NULL,
			  UNIQUE KEY id (id)
			);";

			dbDelta($sql);	
		}
		
		function add_to_cart($product_id, $parameters, $wpsc_cart){
			global $wpsc_cart, $wpdb;
			$data['product_id'] = $product_id;
			$data['date_cart'] = $this->today;
			$record = $this->check_record($product_id, $this->today); // check for quantity, maybe it should be refactored.
			if($record!=false){
				$data['qty'] = $record->added+1;
   				$this->update_record($data,'added');
			}else{
				$data['added'] = 1;
   				$wpdb->insert($this->table_name, $data);
			}
		}
		
		function success_checkout($cart_id, $product_id){
			global $wpsc_cart;
			$data['product_id'] = $product_id;
			$data['date_cart'] = $this->today;
			$record = $this->check_record($product_id, $this->today);
			$cart = $this->get_checkout_cart($cart_id);
			if($record!=false&&$cart!=false){
				$data['qty'] = $record->purchased+$cart->quantity;
   				$this->update_record($data,'purchased');
			}
		}
		
		function check_record($pid, $date){
			global $wpdb;
			$record = $wpdb->get_row("SELECT * FROM ".$this->table_name." WHERE date_cart = '".$date."' and product_id = '".$pid."'");
			if($record == null)
   				return false;
			else
   				return $record;
   		}
		
		public function get_today(){
			return $this->today;
		}
		
		public function get_dates(){
			global $wpdb;
			$record = $wpdb->get_results("SELECT DISTINCT(date_cart) FROM ".$this->table_name." order by date_cart DESC");
			if($record == null)
   				return false;
			else
   				return $record;
		}
		
		function get_records($date='', $number_of_rows=50){
			global $wpdb;
			if($date=='')
				$date = $this->today;
			$records = $wpdb->get_results("SELECT * FROM ".$this->table_name." WHERE date_cart = '".$date."' LIMIT ".$number_of_rows);
			if($records == null) {
				return false;
			}
			else {
				$prods = array();
				foreach($records as $r){
					$prods[$r->product_id] = get_post($r->product_id);
				}		
				$data = array(
					'records'=>$records,
					'products'=>$prods
				); 
			}
			
			return $data;
		}
		
		function get_checkout_cart($cart_id){
			global $wpdb;
			$cart = $wpdb->get_row("SELECT * FROM ".WPSC_TABLE_CART_CONTENTS." where id='".$cart_id."'");
			if($cart==null) {
				return false;
			}
			else {
				return $cart;
			}
		}
		
		public function get_selected(){
			return $this->selected;
		}
		
		public function set_selected($date){
			$this->selected = $date;
		}
		
		function update_record($data,$column){
			global $wpdb;
			$wpdb->update( $this->table_name, array($column=>$data['qty']), 
   						array('date_cart'=>$data['date_cart'],'product_id'=>$data['product_id']));
		}
		
		function update_before_checkout(){
			global $wpsc_cart;			
			foreach($wpsc_cart->cart_items as $item){
				$record = $this->check_record($item->product_id, $this->today);
				if($record!=false&&$record->added!=$item->quantity){
					$this->update_record(array('qty'=>$item->quantity,'date_cart'=>$this->today,
						'product_id'=>$item->product_id), 'added');
				}
			}
		}
	}
endif;