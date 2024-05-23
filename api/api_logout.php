<?php

	//로그아웃 api
	
	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	// 로그아웃시 셰션에 저장된 아이디와 회사코드 없애기

	unset($_SESSION['id']);
	unset($_SESSION['company_sid']);

	nowexit(true,'로그아웃이 되었습니다.');

?>