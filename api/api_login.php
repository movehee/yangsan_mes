<?php

	// 로그인 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';
	// 아이디 유효성 검사
	if(isset($_POST['id']) === false){
		nowexit(false, '아이디 값이 없습니다.');
	}
	if($_POST['id'] === '' || $_POST['id'] === null){
		nowexit(false, '아이디 값이 없습니다.');
	}
	if(mb_strlen($_POST['id']) > 12){
		nowexit(false, '아이디 값의 길이수 제한은 12자 입니다.');
	}
	$id = $_POST['id'];

	// 비밀번호 유효성 검사
	if(isset($_POST['pw']) === false){
		nowexit(false, '비밀번호 값이 없습니다.');
	}
	if($_POST['pw'] === '' || $_POST['pw'] === null){
		nowexit(false, '비밀번호 값이 없습니다.');
	}
	if(mb_strlen($_POST['pw']) > 12){
		nowexit(false, '비밀번호 값의 길이수 제한은 12자 입니다.');
	}
	$pw = $_POST['pw'];

	// 입력받은 아이디를 조건으로 pw를 조회
	$select_sql = "SELECT pw,company_sid FROM user_data WHERE id = '$id';";

	$result_sql =  sql($select_sql);

	$result_sql = select_process($result_sql);

	// pw가 조회되지 않았을때 예외처리
	if($result_sql['output_cnt']===0){
		nowexit(false, '조회된 아이디가 없습니다.');
	}
	

	// 입력받은 pw와 조회한 pw의 일치여부

	// pw가 일치하지 않을 때
	if($pw !== $result_sql[0]['pw'] ){
		nowexit(false, '비밀번호가 일치하지 않습니다.');
	}
	
	
	$_SESSION['id'] = $id;
	$_SESSION['company_sid'] = $result_sql[0]['company_sid'];

	nowexit(true, '로그인이 완료되었습니다.');

?>