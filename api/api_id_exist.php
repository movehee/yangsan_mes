<?php

	//아이디 존재 여부 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//유효성 검사
	if(isset($_POST['id']) === false){
		nowexit(false, '아이디 값이 없습니다.');
	}
	if($_POST['id'] === '' || $_POST['id'] === null){
		nowexit(false, '아이디 값이 없습니다.');
	}
	$id = $_POST['id'];

	//입력받은 아이디로 유저데이터 sid 존재 조회
	$sql = "SELECT sid from user_data WHERE id='$id';";
	$query_result = sql($sql);
	$query_result = select_process($query_result);

	// 조회결과 존재하지 않을 경우
	if($query_result['output_cnt'] === 0){
		nowexit(false, '존재하지 않는 아이디입니다.');
	}

	nowexit(true, '존재하는 아이디입니다.');
?>