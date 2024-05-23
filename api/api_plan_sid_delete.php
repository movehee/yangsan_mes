<?php

	// 생산 계획 sid 삭제 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	// 단건 sid 검사
	if(isset($_POST['plan_sid']) === false){
		nowexit(false, '생산계획  정보를 불러올 수 없습니다.');
	}
	if($_POST['plan_sid'] === null || $_POST['plan_sid'] === ''){
		nowexit(false, '생산계획 정보가 없습니다.');
	}
	//생산계획 sid
	$plan_sid = $_POST['plan_sid'];

	//생산등록테이블에서 조회
	$sql = "SELECT plan_sid FROM production_info WHERE company_sid = '".__COMPANY_SID__."' AND plan_sid = '$plan_sid';";

	$query_result = sql($sql);
	$query_result = select_process($query_result);

	if($query_result['output_cnt'] > 0){

		nowexit(false,'생산등록정보에서 사용중인 정보입니다.');
	}

	//생산계획테이블에서 삭제
	$sql = "DELETE FROM plan_info WHERE sid = '$plan_sid';";

	$query_result = sql($sql);

	if(is_bool($query_result) === false){
		nowexit(false,'생산계획 정보가 삭제되지 않았습니다.');
	}
	if($query_result === false){
		nowexit(false,'생산계획 정보가 삭제되지 않았습니다.');
	}


	nowexit(true,'생산계획 정보가 삭제되었습니다.');

?>
