<?php
	
	//자재발주 수정 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//유효성 검사(자재발주 넘버 , 자재발주 수정 정보)
	if(isset($_POST['update_material_number']) === false){
		nowexit(false,'등록된 정보가 없습니다.1');
	}
	if($_POST['update_material_number'] === '' || $_POST['update_material_number'] === null){
		nowexit(false,'등록된 정보가 없습니다.2');
	}
	// 자재발주 넘버
	$update_material_number = $_POST['update_material_number'];

	if(isset($_POST['order_data']) === false){
		nowexit(false,'등록된 데이터가 없습니다.1');
	}
	if(is_array($_POST['order_data']) === false){
		nowexit(false,'등록된 데이터가 없습니다.2');
	}
	if($_POST['order_data'] === '' || $_POST['order_data'] === null){
		nowexit(false,'등록된 데이터가 없습니다.3');
	}
	// 자재발주 수정 정보
	$order_data = $_POST['order_data'];

	$order_data_cnt = count($order_data);

	$account_sid = $_POST['account_sid'];

	//자재발주 sid 조회(조건 : 자재발주 넘버) 
	$select_sql = "SELECT sid FROM material_order_info WHERE company_sid = '".__COMPANY_SID__."' AND material_order_number = '$update_material_number';";

	$query_result = sql($select_sql);
	$query_result = select_process($query_result);

	for($i = 0; $i < $query_result['output_cnt']; $i++){

		//넘어온 sid가 조회한 sid와 일치할시
		if(isset($order_data[$i]) === true){

			$material_order_sid = $query_result[$i]['sid']; //자재발주sid

			//자재발주sid 조건으로 수정 
			$update_sql = "UPDATE material_order_info SET material_sid = '".$order_data[$i]['material_key']."', material_order_cnt = '".$order_data[$i]['material_cnt']."', material_order_note = '".$order_data[$i]['note']."' WHERE sid = '$material_order_sid';";
			
			$query_result_update = sql($update_sql);

			if(is_bool($query_result_update) === false){
				nowexit(false,'수정 실패.1');
			}
			if($query_result_update === false){
				nowexit(false,'수정 실패.1');
			}

		}else{
			//넘어온 sid가 조회한 sid와 불일치할 시
			
			$sid = $query_result[$i]['sid'];

			$delete_sql = "DELETE FROM material_order_info WHERE sid = $sid";
			$query_result_delete = sql($delete_sql);

			if(is_bool($query_result_delete) === false){
				nowexit(false,'삭제 실패1');
			}
			if($query_result_delete === false){
				nowexit(false, '삭제실패2');
			}

		}
	}

	// 조회결과 보다 넘어온 데이터가 많을 시
	if($query_result['output_cnt'] < $order_data_cnt){

		for($i = $query_result['output_cnt']; $i < $order_data_cnt; $i++){

			// 자재발주 등록 쿼리
			$insert_sql = "INSERT INTO material_order_info (company_sid, account_sid, material_sid, material_order_cnt, material_order_number, material_order_note) VALUES ('".__COMPANY_SID__."', '$account_sid', '".$order_data[$i]['material_key']."', '".$order_data[$i]['material_cnt']."', '".$order_data[$i]['material_order_number']."', '".$order_data[$i]['note']."')";

			$query_result_insert = sql($insert_sql);

			if(is_bool($query_result_insert) === false){
				nowexit(false,'수정 실패3');
			}
			if($query_result_insert === false){
				nowexit(false,'수정 실패4');			
			}

		}

	}



	nowexit(true,'수정되었습니다');

	//material_order_number 조건으로 sid 조회


?>