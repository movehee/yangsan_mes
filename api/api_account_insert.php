<?php
	// 거래처 등록 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//전달받은 입력값 유효성 검사(거래처명 )
	if(isset($_POST['account_name']) === false){
		nowexit(false, '거래처 명의 값이 없습니다.');
	}
	if(($_POST['account_name']) === '' || $_POST['account_name'] === null){
		nowexit(false, '거래처 명의 값이 없습니다.');
	}
	if(mb_strlen($_POST['account_name']) > 100){
		nowexit(false, '거래처 명의 값이 100자가 넘습니다.');
	}
	$account_name = $_POST['account_name'];
	
	//전달받은 입력값 유효성 검사(거래처대표 )
	if(isset($_POST['account_ceo']) === false){
		nowexit(false, '거래처대표의 값이 없습니다.');
	}
	if(($_POST['account_ceo']) === '' || $_POST['account_ceo'] === null){
		nowexit(false, '거래처대표의  값이 없습니다.');
	}
	if(mb_strlen($_POST['account_ceo']) > 100){
		nowexit(false, '거래처대표의 값이 100자가 넘습니다.');
	}
	$account_ceo = $_POST['account_ceo'];

	//전달받은 입력값 유효성 검사( 거래처연락처 사업자번호)
	if(isset($_POST['account_tel']) === false){
		nowexit(false, '거래처 연락처의 값이 없습니다.');
	}
	if(($_POST['account_tel']) === '' || $_POST['account_tel'] === null){
		nowexit(false, '거래처 연락처의 값이 없습니다.');
	}
	if(mb_strlen($_POST['account_tel']) > 100){
		nowexit(false, '거래처 연락처의 값이 100자가 넘습니다.');
	}
	$account_tel = $_POST['account_tel'];

	//전달받은 입력값 유효성 검사( 사업자번호)
	if(isset($_POST['account_number']) === false){
		nowexit(false, '사업자번호 값이 없습니다.');
	}
	if(($_POST['account_number']) === '' || $_POST['account_number'] === null){
		nowexit(false, '사업자번호 값이 없습니다.');
	}
	if(mb_strlen($_POST['account_number']) > 100){
		nowexit(false, '사업자번호 값이 100자가 넘습니다.');
	}

	$account_number = $_POST['account_number'];

	//거래처 비고란
	$account_note = $_POST['account_note'];
	
	//입력받은 거래처 정보를 조건으로 기존 등록된 거래처 정보인지 확인
	$select_sql = "SELECT sid  FROM account_info WHERE company_sid = '".__COMPANY_SID__."' AND (account_name = '$account_name' OR account_tel= '$account_tel' OR account_number = '$account_number');";

	$result_sql = sql($select_sql);
	$result_sql = select_process($result_sql);

	//기존에 거래처가 있는경우 중단
	if($result_sql['output_cnt'] > 0){
		nowexit(false,'거래처가 중복된 값입니다.');
	}


	//기존에 거래처가 없는경우 신규 등록
	//입력받은 정보를 신규 등록
	$insert_sql = "INSERT INTO account_info(account_name, account_tel, account_ceo, account_number,account_note ,company_sid) VALUES ('$account_name', '$account_tel', '$account_ceo', '$account_number','$account_note' ,'".__COMPANY_SID__."');";

	$result_sql = sql($insert_sql);

	//bool타입이 아닐경우
	if (is_bool($result_sql) === false) {
		nowexit(false, '거래처 등록에 실패했습니다.');
	}
	//bool이 false일 경우
	if ($result_sql === false) {
		nowexit(false, '거래처 등록에 실패했습니다.');
	}
	

	nowexit(true, '거래처 등록을 완료했습니다.');
?>