<?php 
	
	date_default_timezone_set('Asia/Kuala_Lumpur');

	define('VERSION', '1.0m');
	define('MERCHANT_ID', 'MBISMY@DEV');
	define('API_KEY','*nz!+x4fk(7i*TLe@ReY');
	define('API_URL','http://api.gtp.development/mbbgtp.php');
	define('CURRENT_DATE', date('Y-m-d H:i:s'));


	$api_method['getgoldbar'] = [
		'name' => 'Gold Bar Request', //A Name display in select box
		'version' => VERSION, //Must
		'merchant_id' => MERCHANT_ID, //Must 
		'action' => 'goldbar_allocation', //Must
		'ref_id' => 'MBB2U1tx34id', //Must
		'quantity' => '2', //Must
		'reference' => '', //Optional
		'timestamp' => CURRENT_DATE, //Must, will auto generate when posting to API
	];

	$api_method['getprice'] = [
		'name' => 'Query Price', //A Name display in select box
		'version' => VERSION, //Must
		'merchant_id' => MERCHANT_ID, //Must 
		'action' => ['price_acebuy','price_acesell'], //Must
		'product' => 'DG-999-9', //Must
		'currency' => 'MYR', //Must
		'reference' => '', //Optional
		'timestamp' => CURRENT_DATE, //Must, will auto generate when posting to API
	];


	$api_method['getspotorder'] = [
		'name' => 'Get Spot Order', //A Name display in select box
		'version' => VERSION, //Must
		'merchant_id' => MERCHANT_ID, //Must 
		'action' => ['spot_acebuy','spot_acesell'], //Must
		'ref_id' => 'MBB2U1tx34id', //Must
		'price_request_id' => '', //optional
		'future_ref_id' => '', //optional\
		'total_price' => '220.50', //Must
		'product' => 'DG-999-9', //Must
		'order_type' => ['weight','amount'], //Must
		'weight' => '0.500', //Must
		'amount' => '230.30', //Must'
		'reference' => '', //Optional
		'timestamp' => CURRENT_DATE, //Must, will auto generate when posting to API
	];

	$api_method['getreverseorder'] = [
		'name' => 'Get Reverse Order', //A Name display in select box
		'version' => VERSION, //Must
		'merchant_id' => MERCHANT_ID, //Must 
		'action' => ['reverse_order'], //Must
		'ref_id' => 'MBB2U1tx34id', //Must
		'reference' => '', //Optional
		'timestamp' => CURRENT_DATE, //Must, will auto generate when posting to API
	];

	$api_method['getfutureorder'] = [
		'name' => 'Get Future Order', //A Name display in select box
		'version' => VERSION, //Must
		'merchant_id' => MERCHANT_ID, //Must 
		'action' => ['future_acebuy','future_acesell'], //Must
		'future_ref_id' => 'MBB2U1tx34id', //Must
		'expected_matching_price' => '177.08', //Must
		'product' => 'DG-999-9', //Must
		'order_type' => ['weight','amount'], //Must
		'weight' => '1.500', //Must
		'amount' => '320.07', //Must'
		'future_order_expiry' => date('Y-m-d H:i:s'), //Must
		'success_notify_url' => 'http://example.com/notify', //Must'
		'reference' => '', //Optional
		'timestamp' => CURRENT_DATE, //Must, will auto generate when posting to API
	];

	$api_method['getcancelfutureorder'] = [
		'name' => 'Get Cancel Future Order', //A Name display in select box
		'version' => VERSION, //Must
		'merchant_id' => MERCHANT_ID, //Must 
		'action' => ['cancel_future_placement'], //Must
		'ref_id' => 'MBB2U1tx34id', //Must
		'reference' => '', //Optional
		'timestamp' => CURRENT_DATE, //Must, will auto generate when posting to API
	];

;?>
