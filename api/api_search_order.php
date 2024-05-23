<?php

	//납품 등록 페이지 수주 날짜 선택 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//유효성 검사(수주 시작 날짜, 수주 종료 날짜)
	if(isset($_POST['startdate']) === false){
		nowexit(false, '날짜 시작범위 정보를 불러올 수 없습니다.');
	}
	if($_POST['startdate'] === '' || $_POST['startdate'] === null){
		nowexit(false, '날짜 시작범위 정보가 없습니다.');
	}
	$temp_date = explode('-', $_POST['startdate']);

	if(count($temp_date) !== 3){
		nowexit(false, '날짜 시작범위 정보의 유효성이 올바르지 않습니다.');
	}
	if(checkdate($temp_date[1], $temp_date[2], $temp_date[0]) === false){
		nowexit(false, '날짜 시작범위 정보의 유효성이 올바르지 않습니다.');
	}
	$startdate = $_POST['startdate'];

	if(isset($_POST['enddate']) === false){
		nowexit(false, '날짜 종료범위 정보를 불러올 수 없습니다.');
	}
	if($_POST['enddate'] === '' || $_POST['enddate'] === null){
		nowexit(false, '날짜 종료범위 정보가 없습니다.');
	}
	$temp_date = explode('-', $_POST['enddate']);
	if(count($temp_date) !== 3){
		nowexit(false, '날짜 종료범위 정보의 유효성이 올바르지 않습니다.');
	}
	if(checkdate($temp_date[1], $temp_date[2], $temp_date[0]) === false){
		nowexit(false, '날짜 종료범위 정보의 유효성이 올바르지 않습니다.');
	}
	$enddate = $_POST['enddate'];

	// 수주테이블에 수주 날짜 조건으로 조회
	$sql = "SELECT order_number, order_date, account_sid FROM order_info WHERE company_sid='".__COMPANY_SID__."' AND order_date BETWEEN '$startdate 00:00:00' AND '$enddate 23:59:59' GROUP BY order_number ORDER BY order_number DESC;";

	$query_result = sql($sql);
	$order_db = select_process($query_result);

	// 조회 결과가 없을 시 중단
	if($order_db['output_cnt'] === 0){
		nowexit(false, '날짜 범위 내에 등록된 수주 정보가 없습니다.');
	}

	// 거래처 테이블에 sid, 거래처명 조회
	$sql = "SELECT sid, account_name FROM account_info WHERE company_sid='".__COMPANY_SID__."';";

	$query_result = sql($sql);
	$account_db = select_process($query_result);

	$account_name = array();

	for($i=$account_db['output_cnt']-1; $i>=0; $i--){

		//거래처sid => 거래처명 맵핑
		$account_name[$account_db[$i]['sid']] = $account_db[$i]['account_name'];
	}

	$order_list = array();

	//select bar 가공
	for($i=0; $i<$order_db['output_cnt']; $i++){
		$temp = array();
		$temp['order_number'] = $order_db[$i]['order_number'];
		$temp['order_date'] = explode(' ', $order_db[$i]['order_date'])[0];
		$temp['account_name'] = $account_name[$order_db[$i]['account_sid']];

		array_push($order_list, $temp);
	}

	$result['order_list'] = $order_list;

	nowexit(true);
?>