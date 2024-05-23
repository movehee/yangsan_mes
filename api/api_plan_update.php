<?php
	
	//생산계획 수정 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//유효성 검사(plan_number,plan_data)	

	if(isset($_POST['up_plan_number']) === false ){
		nowexit(false,'계획생성번호 값이 없습니다.1');
	}

	if($_POST['up_plan_number'] === null || $_POST['up_plan_number'] === ''){
		nowexit(false,'계획생성번호 값이 없습니다.2');
	}

	if(is_int((int)$_POST['up_plan_number']) === false ){
		nowexit(false,'계획생성번호 값이 유효성이 맞지 않습니다.3');
	}

	$plan_number = (int)$_POST['up_plan_number'];


	if(isset($_POST['plan_data']) === false){
		nowexit(false, '품목정보 값이 없습니다.');
	}
	if(is_array($_POST['plan_data']) === false){
		nowexit(false,'품목정보 값이 없습니다.');
	}

	$plan_data = $_POST['plan_data'];

	//생산계획 수정할 데이터가 없으면 중단
	if(count($plan_data) === 0){
		nowexit(false,'품목정보 값이 0입니다.'); 
	}

	//plan_number 있는지 확인
	$select_plan_sql = "SELECT plan_number FROM plan_info WHERE company_sid = '".__COMPANY_SID__."' AND plan_number = '$plan_number';";
	
	$query_result = sql($select_plan_sql);
	$query_result = select_process($query_result);

	//조회결과가 없으면 예외처리
	if($query_result['output_cnt'] === 0){
		nowexit(true,'등록된 생산계획이 없습니다.');
	}

	//생산계획 정보 삭제
	$delete_plan_sql = "DELETE FROM plan_info WHERE company_sid = '".__COMPANY_SID__."' AND plan_number = '$plan_number';";
	$query_result = sql($delete_plan_sql);

	// 실패시 예외처리
	if(is_bool($query_result) === false){
		nowexit(false,'삭제를 실패했습니다.1');
	}
	if($query_result === false){
		nowexit(false,'삭제를 실패했습니다.2');
	}

	$plan_data_cnt = count($plan_data);

	//수주 재등록(수주날짜,거래처sid, 품목sid, 품목 수량, 수주비고, 수주넘버)
	for($i=0; $i<$plan_data_cnt; $i++){

		$insert_plan_sql = "INSERT INTO plan_info(company_sid, order_sid, plan_cnt, plan_note,plan_number)VALUES('".__COMPANY_SID__."' ,'".$plan_data[$i]['order_key']."','".$plan_data[$i]['plan_cnt']."','".$plan_data[$i]['plan_note']."','$plan_number');";
		
		$query_result = sql($insert_plan_sql);

		if(is_bool($query_result) === false){
			nowexit(false,'수주수정을 실패했습니다.1');
		}
		if($query_result === false){
			nowexit(false,'수주수정을 실패했습니다.2');
		}
		
	}


	 nowexit(true,'수정이 완료되었습니다.');

?>