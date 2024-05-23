<?php

	//생산계획 등록 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//유효성 검사(plan_data)
	if(isset($_POST['plan_data']) === false){
		nowexit(false, '생산계획 값이 없습니다.1');
	}
	if(is_array($_POST['plan_data']) === false){
		nowexit(false,'생산계획 값이 없습니다.2');
	}

	$plan_data = $_POST['plan_data'];

	if(count($plan_data) === 0){
		nowexit(false,'수주정보값이 0입니다.'); 
	}

	//생산계획 넘버찾기
	$plan_number = '1';
	$select_plan_sql = "SELECT plan_number FROM plan_info WHERE company_sid= '".__COMPANY_SID__."' GROUP BY plan_number ORDER BY plan_number DESC LIMIT 0,1;";

	$query_result = sql($select_plan_sql);
	$query_result = select_process($query_result);
	
	// 결과가 있을 경우 +1로 저장
	if($query_result['output_cnt'] > 0){
		$plan_number = $query_result[0]['plan_number'] + 1;
	}

	//생산계획 등록
	for($i=0; $i<count($plan_data); $i++){

		$insert_plan_sql = "INSERT INTO plan_info(company_sid, order_sid, plan_cnt, plan_note,plan_number) VALUES ('".__COMPANY_SID__."' ,'".$plan_data[$i]['order_key']."','".$plan_data[$i]['plan_cnt']."','".$plan_data[$i]['plan_note']."','$plan_number');";
		
		$query_result = sql($insert_plan_sql);

		if(is_bool($query_result) === false){
			nowexit(false,'생산계획등록을 실패했습니다.');
		}
		if($query_result === false){
			nowexit(false,'생산계획등록을 실패했습니다.');
		}		
		
	}


	nowexit(true, '생산계획 등록이 완료되었습니다.' );

?>