<?php

	//수주 수정 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//유효성 검사(order_number,order_data)	

	if(isset($_POST['order_number']) === false ){
		nowexit(false,'수주번호 값이 없습니다.1');
	}

	if($_POST['order_number'] === null || $_POST['order_number'] === ''){
		nowexit(false,'수주번호 값이 없습니다.2');
	}

	if(is_int((int)$_POST['order_number']) === false ){
		nowexit(false,'수주번호 값이 유효성이 맞지 않습니다.3');
	}

	$order_number = (int)$_POST['order_number'];

	if(isset($_POST['up_account_sid']) === false ){
		nowexit(false,'거래처 값이 없습니다.1');
	}

	if($_POST['up_account_sid'] === null || $_POST['up_account_sid'] === ''){
		nowexit(false,'거래처 값이 없습니다.2');
	}

	if(is_int((int)$_POST['up_account_sid']) === false ){
		nowexit(false,'거래처 값이 유효성이 맞지 않습니다.3');
	}

	$up_account_sid = (int)$_POST['up_account_sid'];

	if(isset($_POST['order_data']) === false){
		nowexit(false, '품목정보 값이 없습니다.');
	}
	if(is_array($_POST['order_data']) === false){
		nowexit(false,'품목정보 값이 없습니다.');
	}

	$order_data = $_POST['order_data'];

	// 수주 수정 데이터가 없으면 중단
	if(count($order_data) === 0){
		nowexit(false,'품목정보 값이 0입니다.'); 
	}
	//order_number 있는지 확인
	$select_order_sql = "SELECT order_number FROM order_info WHERE company_sid = '".__COMPANY_SID__."' AND order_number = '$order_number';";
	$query_result = sql($select_order_sql);
	$query_result = select_process($query_result);

	//조회결과가 없으면 예외처리
	if($query_result['output_cnt'] === 0){
		nowexit(true,'등록된 수주가 없습니다.');
	}

	//수주 정보 삭제
	$delete_order_sql = "DELETE FROM order_info WHERE company_sid = '".__COMPANY_SID__."' AND order_number = '$order_number';";
	$query_result = sql($delete_order_sql);

	// 실패시 예외처리
	if(is_bool($query_result) === false){
		nowexit(false,'삭제를 실패했습니다.1');
	}
	if($query_result === false){
		nowexit(false,'삭제를 실패했습니다.2');
	}

	$order_data_cnt = count($order_data);

	//수주 재등록(수주날짜,거래처sid, 품목sid, 품목 수량, 수주비고, 수주넘버)
	for($i=0; $i<$order_data_cnt; $i++){

		$insert_order_sql = "INSERT INTO order_info(company_sid, account_sid, product_sid,product_cnt, order_note ,order_number) VALUES 
		('".__COMPANY_SID__."' , '$up_account_sid','".$order_data[$i]['product_key']."','".$order_data[$i]['product_cnt']."','".$order_data[$i]['order_note']."','$order_number');";
		
		$query_result = sql($insert_order_sql);

		if(is_bool($query_result) === false){
			nowexit(false,'수주수정을 실패했습니다.1');
		}
		if($query_result === false){
			nowexit(false,'수주수정을 실패했습니다.2');
		}
		
	}


	 nowexit(true,'수정이 완료되었습니다.');

?>