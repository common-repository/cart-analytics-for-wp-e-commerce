<?php
session_start();
if(isset($_GET['active'])&&$_GET['active']==true){
	get_json();	
}

function get_json(){
	if(isset($_SESSION['ca_data'])){
		$records = $_SESSION['ca_data']['data']['records'];
		$products = $_SESSION['ca_data']['data']['products'];	
	
	$rows=array();
		$cols =array(
				array('label'=>$_SESSION['ca_data']['ca_date_select'],'type'=>'string'),
				array('label'=>'Added','type'=>'number'),
				array('label'=>'Purchased','type'=>'number')
			);
		foreach($records as $d){
			$p = $products[$d->product_id];
			$r['c']=array(
				array('v'=>$p->post_title,'f'=>null),
				array('v'=>(int)$d->added,'f'=>null),
				array('v'=>(int)$d->purchased,'f'=>null),
			);
			$rows[]=$r;
		}
		
		$j = array(
				'cols'=>$cols,
				'rows'=>$rows,
			);
			
			echo json_encode($j);	
		}
}
?>
