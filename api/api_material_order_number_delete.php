<?php

	//자재발주 번호 삭제 api

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

	//넘어온 발주 번호가 없을 시 중단
	if($checked_number_cnt === 0){
		nowexit(false, '발주번호 정보의 유효성이 올바르지 않습니다.');
	}

	for($i=0; $i<$checked_number_cnt; $i++){

		$temp_number = $checked_number[$i];
		
		//자재발주 테이블에 자재발주sid 조회
		$sql = "SELECT sid FROM material_order_info WHERE material_order_number='$temp_number' AND company_sid= '".__COMPANY_SID__."';";

		$query_result = sql($sql);
		$query_result = select_process($query_result);

		$parent_sid = array();

		for($j=0; $j<$query_result['output_cnt']; $j++){

			//발주sid 배열에 푸쉬
			array_push($parent_sid, $query_result[$j]['sid']);
		}

		$parent_sid_sql = implode("','", $parent_sid);

		//자재발주 sid를 조건으로 자재 입고 데이터 삭제
		$delete_sql = "DELETE FROM material_stock WHERE parent_sid IN('$parent_sid_sql') AND type = 'in';";
		$query_result_delete = sql($delete_sql);

		if(is_bool($query_result_delete) === false){
			nowexit(false, '자재 입고 sid 정보 삭제를 실패했습니다.');
		}
		if($query_result_delete === false){
			nowexit(false, '자재 입고 sid 정보 삭제를 실패했습니다.');
		}

		// 자재발주테이블에서 삭제
		$sql = "DELETE FROM material_order_info WHERE material_order_number='$temp_number';";

		$query_result = sql($sql);
		
		if(is_bool($query_result) === false){
			nowexit(false, '발주번호 정보 삭제를 실패했습니다.');
		}
		if($query_result === false){
			nowexit(false, '발주번호 정보 삭제를 실패했습니다.');
		}
	}

		
	nowexit(true , '발주번호 삭제를 완료했습니다.');
?>