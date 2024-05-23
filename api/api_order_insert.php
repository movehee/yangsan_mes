<?php
	
	//수주 등록 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//유효성 검사(order_data,account_sid)
	if(isset($_POST['order_data']) === false){
		nowexit(false, '수주정보 값이 없습니다.');
	}
	if(is_array($_POST['order_data']) === false){
		nowexit(false,'수주정보 값이 없습니다.');
	}

	// 수주정보
	$order_data = $_POST['order_data'];

	if(count($order_data) === 0){
		nowexit(false,'수주정보값이 0입니다.'); 
	}

	if(isset($_POST['account_sid'])===false){
		nowexit(false,'거래처정보가 없습니다.');
	}
	if($_POST['account_sid'] === ''|| $_POST['account_sid'] === null){
		nowexit(false,'거래처정보가 없습니다');
	}
	//거래처sid
	$account_sid = $_POST['account_sid'];

	//수주넘버찾기
	$order_number = '1';
	$select_order_sql = "SELECT order_number FROM order_info WHERE company_sid= '".__COMPANY_SID__."' GROUP BY order_number ORDER BY order_number DESC LIMIT 0,1;";

	$query_result = sql($select_order_sql);
	$query_result = select_process($query_result);

	//조회결과가 있을 경우 1 더하기
	if($query_result['output_cnt'] > 0){
		$order_number = $query_result[0]['order_number'] + 1;

	}

	//수주등록 
	for($i=0; $i<count($order_data); $i++){

		//수주 등록
		$insert_order_sql = "INSERT INTO order_info(company_sid, account_sid, product_sid,product_cnt, order_note ,order_number) VALUES ('".__COMPANY_SID__."' , '$account_sid','".$order_data[$i]['product_key']."','".$order_data[$i]['product_cnt']."','".$order_data[$i]['order_note']."','$order_number');";
		
		$query_result = sql($insert_order_sql);

		if(is_bool($query_result) === false){
			nowexit(false,'수주등록을 실패했습니다.');
		}
		if($query_result === false){
			nowexit(false,'수주등록을 실패했습니다.');
		}
		
	}
	

	 nowexit(true,'수주등록이 완료되었습니다.');	

?>