<?php

	//납품 등록 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//유효성(납품 정보)
	if(isset($_POST['delivery_list']) === false){
		nowexit(false, '납품 정보를 불러올 수 없습니다.');
	}
	if(is_array($_POST['delivery_list']) === false){
		nowexit(false, '납품 정보의 유효성이 올바르지 않습니다.');
	}
	$delivery_list = $_POST['delivery_list'];
	$delivery_list_cnt = count($delivery_list);

	//납품 정보가 없을시 중단
	if($delivery_list_cnt === 0){
		nowexit(false, '납품 정보가 없습니다.');
	}

	// 기본 납품 번호 설정 
	$delivery_number = 1;

	$sql = "SELECT MAX(delivery_number) as delivery_number FROM delivery_info WHERE company_sid='".__COMPANY_SID__."';";

	$query_result = sql($sql);
	$delivery_number_db = select_process($query_result);

	//조회결과 있을 경우 1 더하기
	if($delivery_number_db['output_cnt'] > 0){
		$delivery_number = (int)$delivery_number_db[0]['delivery_number'] + 1;
	}

	$order_sid = array(); //수주sid배열
	$insert_sql = array(); //등록 쿼리

	for($i=0; $i<$delivery_list_cnt; $i++){

		$row = $delivery_list[$i];
		// 수주sid가 공백일 시
		if($row['order_sid'] === ''){
			nowexit(false, ($i + 1).'번째 납품정보의 품목이 선택되지 않았습니다.');
		}
		// 수주 수량이 공백일 시
		if($row['delivery_cnt'] === 0){
			nowexit(false, ($i + 1).'번째 납품정보의 납품수량이 입력되지 않았습니다.');
		}

		// list에 있는 수주sid 배열에 담기
		array_push($order_sid, $row['order_sid']);

		$row_order_sid = $row['order_sid'];
		$row_delivery_cnt = $row['delivery_cnt'];
		$row_note = $row['note'];

		//납품 테이블에 등록 쿼리
		$sql = "INSERT INTO delivery_info(company_sid, order_sid, delivery_cnt, delivery_note, delivery_number) VALUES('".__COMPANY_SID__."', '$row_order_sid', '$row_delivery_cnt', '$row_note', '$delivery_number');";

		array_push($insert_sql, $sql);

	}

	$order_sid_cnt = count($order_sid);

	//수주sid가 있을 시 조건으로 수주테이블 조회
	if($order_sid_cnt > 0){
		$sql = "SELECT sid FROM order_info WHERE company_sid='".__COMPANY_SID__."' AND sid IN('".implode("','", $order_sid)."');";
		$query_result = sql($sql);
		$order_db = select_process($query_result);

		//수주sid와 쿼리수행 갯수가 일치하지 않을 시
		if($order_sid_cnt !== $order_db['output_cnt']){
			nowexit(false, '유효하지 않은 품목 정보가 포함되어있습니다.');
		}
	}


	$insert_sql_cnt = count($insert_sql);

	//납품정보 등록(넘겨받은 납품정보)
	for($i=0; $i<$insert_sql_cnt; $i++){

		$query_result = sql($insert_sql[$i]);

		if(is_bool($query_result) === false){
			nowexit(false, '납품정보 등록을 실패했습니다.');
		}
		if($query_result === false){
			nowexit(false, '납품정보 등록을 실패했습니다.');
		}
	}

	nowexit(true);
?>