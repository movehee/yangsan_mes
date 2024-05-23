<?php 

	//생산 계획 번호 삭제 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	// 생상계획 번호 유효성 검사
	if(isset($_POST['plan_number']) === false){
		nowexit(false, '생산계획  정보를 불러올 수 없습니다.');
	}
	if($_POST['plan_number'] === null || $_POST['plan_number'] === ''){
		nowexit(false, '생산계획 정보가 없습니다.');
	}
	//생산계획 번호
	$plan_number = $_POST['plan_number'];

	//생산sid 조회
	$sql = "SELECT sid FROM plan_info WHERE plan_number = '$plan_number';";

	$query_result = sql($sql);
	$query_result = select_process($query_result);

	$plan_sid = array();
	for($i=0; $i<$query_result['output_cnt']; $i++){

		//계획sid 배열에 푸쉬
		array_push($plan_sid, $query_result[$i]['sid']);

	}

	//생산등록 조회(조건: plan_sid)
	$sql_in = implode("','", $plan_sid);
	$sql = "SELECT plan_sid FROM production_info WHERE company_sid = '".__COMPANY_SID__."' AND plan_sid IN('$sql_in');";

	$query_result = sql($sql);
	$query_result = select_process($query_result);

	//생산등록에서 사용중이면 중단
	if($query_result['output_cnt'] > 0){

		nowexit(false, '생산등록에서 사용중인 정보 입니다.');
	}

	//생산계획 번호 삭제sql
	$sql = "DELETE FROM plan_info WHERE plan_number = '$plan_number';";

	$query_result = sql($sql);

	//삭제 실패시 중단
	if(is_bool($query_result) === false){
		nowexit(false, '수주번호 정보 삭제를 실패했습니다.');
	}
	if($query_result === false){
		nowexit(false, '수주번호 정보 삭제를 실패했습니다.');
	}
	

	nowexit(true, '생산계획 번호 삭제를 완료했습니다.');


?>