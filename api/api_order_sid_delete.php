<?php

	//수주 sid 삭제 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	// 단건 sid 검사
	if(isset($_POST['order_sid']) === false){
		nowexit(false, '입고sid 정보를 불러올 수 없습니다.');
	}
	if($_POST['order_sid'] === null || $_POST['order_sid'] === ''){
		nowexit(false, '입고sid 정보가 없습니다.');
	}
	$order_sid = $_POST['order_sid'];

	//생산계획테이블( 조건 :선택한 수주sid와 회사코드) 조회
	$select_plan_sql = "SELECT order_sid FROM plan_info WHERE company_sid ='".__COMPANY_SID__."' AND order_sid IN('$order_sid');";
	$query_result_plan = sql($select_plan_sql);
	$query_result_plan = select_process($query_result_plan);

	//만약 생산계획테이블에 조회결과가 있다면, nowexit로 삭제 막기
	for($i=0; $i<$query_result_plan['output_cnt']; $i++){
		if($query_result_plan[$i] > 0){
			nowexit(false,'생산계획에서 사용중인 수주입니다.');
		}
	}

	//납품테이블(조건 :선택한 수주sid와 회사코드) 조회
	$select_delivery_sql = "SELECT order_sid FROM delivery_info WHERE company_sid ='".__COMPANY_SID__."' AND order_sid IN('$order_sid');";
	$query_result_delivery = sql($select_delivery_sql);
	$query_result_delivery = select_process($query_result_delivery);
	
	//만약 납품테이블에 조회결과가 있다면, nowexit로 삭제 막기
	for($i=0; $i<$query_result_delivery['output_cnt']; $i++){
		if($query_result_delivery[$i] > 0){
			nowexit(false,'납품에서 사용중인 수주입니다.');
		}
	}

	//조회결과가 없다면 수주테이블에서 삭제
	$sql = "DELETE FROM order_info WHERE sid = '$order_sid';";

	$query_result = sql($sql);

	if(is_bool($query_result) === false){
		nowexit(false,'수주 정보가 삭제되지 않았습니다.');
	}
	if($query_result === false){
		nowexit(false,'수주 정보가 삭제되지 않았습니다.');
	}


	nowexit(true,'수주 정보가 삭제되었습니다.');

?>
