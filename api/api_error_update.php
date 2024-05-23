<?php 

	//불량 원인 수정 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//유효성 검사(불량sid, 불량명, 불량코드)
	if(isset($_POST['error_sid']) === false){
		nowexit(false,'불량 sid의 값이 없습니다1');
	}
	if($_POST['error_sid'] === '' || $_POST['error_sid'] === null){
		nowexit(false, '불량 sid의 값이 없습니다2.');
	}

	$error_sid = $_POST['error_sid'];

	if(isset($_POST['error_name']) === false){
	nowexit(false,'불량명 값이 없습니다');
	}
	if($_POST['error_name'] === '' || $_POST['error_name'] === null){
		nowexit(false, '불량명 값이 없습니다.');
	}
	$error_name = $_POST['error_name'];

	if(isset($_POST['error_number']) === false){
	nowexit(false,'불량코드 값이 없습니다');
	}
	if($_POST['error_number'] === '' || $_POST['error_number'] === null){
		nowexit(false, '불량코드 값이 없습니다.');
	}	
	$error_number = $_POST['error_number'];

	//불량 비고란
	$error_note = $_POST['error_note'];

	//조건으로(불량명와 불량코드,회사코드) 불량 sid를 검색
	$select_sql = "SELECT sid FROM error_info WHERE 
		company_sid = '".__COMPANY_SID__."' AND (error_name = '$error_name' OR error_number = '$error_number');";

	$query_result = sql($select_sql);
	$query_result = select_process($query_result);

	// 조회된 불량sid가 있을 경우
	$is_duplication = false;
	if($query_result['output_cnt'] > 0){
		//조회된 불량 sid와 현재 로그인된 회사코드의 불량 sid와 불일치시
		if($query_result[0]['sid'] !== $error_sid ){
			$is_duplication = true;
		}
	}

	// (불량명과 불량코드,회사코드) 중복시 중단
	if($is_duplication === true){
		nowexit(false,'중복된 값이 있습니다');
	}

	//불량 수정 쿼리
	$update_sql = "UPDATE error_info SET error_name = '$error_name' ,
		error_number = '$error_number', error_note ='$error_note' 
		 WHERE sid = '$error_sid';";

	$query_result = sql($update_sql);

	nowexit(true, '수정이 완료되었습니다.');


	



?>