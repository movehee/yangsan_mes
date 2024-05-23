<?php
	//자재발주 등록 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	// 입력값 유효성 검사
	// 거래처
	if(isset($_POST['account_key']) === false){
		nowexit(false, '거래처 정보를 불러올 수 없습니다.');
	}
	if($_POST['account_key'] === '' || $_POST['account_key'] === null){
		nowexit(false, '거래처 정보가 없습니다.');
	}
	$account_sid = $_POST['account_key'];

	// 발주 정보
	if(isset($_POST['order_data']) === false){
		nowexit(false, '자재 발주 정보를 불러올 수 없습니다.');
	}
	if(is_array($_POST['order_data']) === false){
		nowexit(false, '자재 발주 정보의 유효성이 올바르지 않습니다.');
	}
	$order_data = $_POST['order_data'];
	$order_data_cnt = count($order_data);

	//자재발주 정보가 없을 시 중단 
	if($order_data_cnt === 0){
		nowexit(false, '자재 발주 정보의 유효성이 올바르지 않습니다.');
	}

	// 자재발주 번호 조회
	$material_order_number = 1;
	$sql = "SELECT material_order_number FROM material_order_info WHERE company_sid='".__COMPANY_SID__."' GROUP BY material_order_number ORDER BY material_order_number DESC LIMIT 0,1;";

	$query_result = sql($sql);
	$query_result = select_process($query_result);

	//조회 결과가 있을 시 1 더하기
	if($query_result['output_cnt'] > 0){
		$material_order_number = (int)$query_result[0]['material_order_number'] + 1;
	}


	for($i=0; $i<$order_data_cnt; $i++){

		//자재sid가 없을 시 중단
		if($order_data[$i]['material_key'] === '' || $order_data[$i]['material_key'] === null){
			nowexit(false, ($i + 1).'번째 줄의 자재선택이 되지 않았습니다.');
		}
		//자재발주 수량이 없을 시 중단
		if((int)$order_data[$i]['material_cnt'] === 0 || (int)$order_data[$i]['material_cnt'] < 0){
			nowexit(false, ($i + 1).'번째 줄의 수량의 유효성이 올바르지 않습니다.');
		}

		$temp_material_sid = $order_data[$i]['material_key'];
		$temp_material_cnt = $order_data[$i]['material_cnt'];
		$temp_note = $order_data[$i]['note'];

		//자재발주 등록 쿼리
		$sql = "INSERT INTO material_order_info(company_sid, account_sid, material_sid, material_order_cnt, material_order_note, material_order_number) VALUES('".__COMPANY_SID__."', '$account_sid', '$temp_material_sid', '$temp_material_cnt', '$temp_note', '$material_order_number');";

		$query_result = sql($sql);

		
		if(is_bool($query_result) === false){
			nowexit(false, '자재발주 정보 등록을 실패했습니다.');
		}
		if($query_result === false){
			nowexit(false, '자재발주 정보 등록을 실패했습니다.');
		}
	}

	nowexit(true, '자재 발주 정보 등록을 완료했습니다.');
?>