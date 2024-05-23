<?php
	
	//수주 번호 삭제 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	// 발주번호 검사
	if(isset($_POST['checked_number']) === false){
		nowexit(false, '발주번호 정보를 불러올 수 없습니다.');
	}
	if(is_array($_POST['checked_number']) === false){
		nowexit(false, '발주번호 정보의 유효성이 올바르지 않습니다.');
	}
	$checked_number = $_POST['checked_number'];

	$checked_number_cnt = count($checked_number);

	//수주 번호가 없을 시 중단
	if($checked_number_cnt === 0){
		nowexit(false, '발주번호 정보의 유효성이 올바르지 않습니다.');
	}

	//수주테이블에서 넘어온 수주넘버로 수주sid 가져오기	
	for($i=0; $i<$checked_number_cnt; $i++){

		$temp_number = $checked_number[$i];
		//수주테이블 수주sid 조회(조건: 수주번호 )
		$sql = "SELECT sid FROM order_info WHERE company_sid = '".__COMPANY_SID__."' AND order_number ='$temp_number';";

		$query_result = sql($sql);
		$query_result = select_process($query_result);

		//수주SID 배열
		$order_sid = array();
		for($j=0; $j<$query_result['output_cnt']; $j++){

			//수주SID 넣기
			array_push($order_sid, $query_result[$i]['sid']);
		}
	}

	$order_sid_cnt = count($order_sid);

	// 수주SID 배열이 비어 있는지 확인
	if (empty($order_sid)) {
	    nowexit(false, '해당 수주번호에 해당하는 수주sid가 없습니다.');
	}

	
	for($i=0; $i<$order_sid_cnt; $i++){

		$sql_in = implode("','", $order_sid);
		//생산계획테이블 조회(수주sid 조건)
		$sql = "SELECT order_sid FROM plan_info WHERE company_sid = '".__COMPANY_SID__."' AND order_sid IN('$sql_in');";

		$query_result = sql($sql);
		$query_result = select_process($query_result);

		//생산계획테이블에 조회결과가 있다면 삭제취소
		if($query_result['output_cnt'] > 0){

			nowexit(false,'생산계획에 수주 관련 정보가 있습니다.');
		}

		//납품테이블 조회(수주sid 조건)
		$delivery_sql = "SELECT order_sid FROM delivery_info WHERE company_sid = '".__COMPANY_SID__."' AND order_sid IN('$sql_in');";
		$query_result_delivery = sql($delivery_sql);
		$query_result_delivery = select_process($query_result_delivery);

		//납품테이블에 조회결과가 있다면 삭제취소
		if($query_result_delivery['output_cnt'] > 0){
			nowexit(false,'납품에 수주 관련 정보가 있습니다.');
		}

	}

	//조회결과가 없다면 수주테이블에서 삭제
	for($i=0; $i<$checked_number_cnt; $i++){

		$temp_number = $checked_number[$i];
		$sql = "DELETE FROM order_info WHERE order_number='$temp_number';";

		$query_result = sql($sql);
		if(is_bool($query_result) === false){
			nowexit(false, '수주번호 정보 삭제를 실패했습니다.');
		}
		if($query_result === false){
			nowexit(false, '수주번호 정보 삭제를 실패했습니다.');
		}

	}


	nowexit(true, '수주 번호 삭제를 완료했습니다.');



?>