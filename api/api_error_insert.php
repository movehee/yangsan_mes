<?php

	//불량 원인 등록

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//전달받은 입력값 유효성 검사(불럄명, 불량코드)
	if(isset($_POST['error_name']) === false){
		nowexit(false, '불량명의 값이 없습니다.');
	}
	if(($_POST['error_name']) === '' || $_POST['error_name'] === null){
		nowexit(false, '불량명의 값이 없습니다.');
	}
	if(mb_strlen($_POST['error_name']) > 100){
		nowexit(false, ' 불량명의 값이 100자가 넘습니다.');
	}
	$error_name = $_POST['error_name'];

	if(isset($_POST['error_number']) === false){
		nowexit(false, '불량코드 값이 없습니다.');
	}
	if(($_POST['error_number']) === '' || $_POST['error_number'] === null){
		nowexit(false, '불량코드 값이 없습니다.');
	}
	if(mb_strlen($_POST['error_number']) > 100){
		nowexit(false, '불량코드 값이 100자가 넘습니다.');
	}
	$error_number = $_POST['error_number'];

	// 불량 비고란
	$error_note = $_POST['error_note'];

	//기존 등록된 불량 정보인지 확인
	$select_sql = "SELECT sid FROM error_info WHERE company_sid = '".__COMPANY_SID__."' AND (error_name='$error_name'OR error_number ='$error_number'); ";

	$result_sql = sql($select_sql);
	$result_sql = select_process($result_sql);
	//기존에 불량명과 불량코드가 있는경우 중복되었습니다 라는 콘솔로 내보내기
	$sid = null;
	if($result_sql['output_cnt'] > 0){
		nowexit(false, '중복된 불량정보 입니다.');
	}

	//기존에 불량정보가 없는경우 신규 등록
	
		//신규 등록
		$insert_sql = "INSERT INTO error_info(error_name,  error_number,error_note ,company_sid) VALUES ('$error_name', '$error_number','$error_note' ,'".__COMPANY_SID__."');";

		$result_sql = sql($insert_sql);

		//bool 타입이 아닐시
		if (is_bool($result_sql) === false) {
			nowexit(false, '불량 등록에 실패했습니다.');
		}
		//bool이 false일 때
		if ($result_sql === false) {
			nowexit(false, '불량 등록에 실패했습니다.');
		}
	

	nowexit(true, '불량 등록을 완료했습니다.');
?>