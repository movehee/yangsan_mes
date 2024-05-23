<?php
	
	//비밀번호 변경 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//유효성검사 (id,pw)
	if(isset($_POST['id']) === false){
		nowexit(false, '아이디 값이 없습니다.');
	}
	if($_POST['id'] === '' || $_POST['id'] === null){
		nowexit(false, '아이디 값이 없습니다.');
	}
	$id = $_POST['id'];

	if(isset($_POST['pw']) === false){
		nowexit(false, '비밀번호 값이 없습니다.');
	}
	if($_POST['pw'] === '' || $_POST['pw'] === null){
		nowexit(false, '비밀번호 값이 없습니다.');
	}
	if(mb_strlen($_POST['pw']) > 12){
		nowexit(false, '비밀번호 값의 길이 제한은 12자입니다.');
	}
	$pw = $_POST['pw'];

	//유저데이터 수정
	$sql = "UPDATE user_data SET pw='$pw' WHERE id='$id';";
	$query_result = sql($sql);
	//
	if(is_bool($query_result) === false){
		nowexit(false, '비밀번호 변경을 실패했습니다.');
	}
	if($query_result === false){
		nowexit(false, '비밀번호 변경을 실패했습니다.');
	}

	nowexit(true, '비밀번호 변경이 완료되었습니다.');
?>