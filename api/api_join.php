<?php

	//회원가입 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	// ***** 유효성 검사 (아이디, 비밀번호, 사업자번호, 회사명, 회사대표자, 회사연락처)
	// 아이디
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

	// 비밀번호
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

	// 사업자번호
	if(isset($_POST['company_number']) === false){
		nowexit(false, '사업자번호 값이 없습니다.');
	}
	if($_POST['company_number'] === '' || $_POST['company_number'] === null){
		nowexit(false, '사업자번호 값이 없습니다.');
	}
	if(mb_strlen($_POST['company_number']) > 100){
		nowexit(false, '사업자번호 값의 길이수 제한은 100자 입니다.');
	}
	$company_number = $_POST['company_number'];

	// 회사명
	if(isset($_POST['company_name']) === false){
		nowexit(false, '회사명 값이 없습니다.');
	}
	if($_POST['company_name'] === '' || $_POST['company_name'] === null){
		nowexit(false, '회사명 값이 없습니다.');
	}
	if(mb_strlen($_POST['company_name']) > 100){
		nowexit(false, '회사명 값의 길이수 제한은 100자 입니다.');
	}
	$company_name = $_POST['company_name'];

	// 회사대표자
	if(isset($_POST['company_ceo']) === false){
		nowexit(false, '회사대표자 값이 없습니다.');
	}
	if($_POST['company_ceo'] === '' || $_POST['company_ceo'] === null){
		nowexit(false, '회사대표자 값이 없습니다.');
	}
	if(mb_strlen($_POST['company_ceo']) > 100){
		nowexit(false, '회사대표자 값의 길이수 제한은 100자 입니다.');
	}
	$company_ceo = $_POST['company_ceo'];

	// 회사 대표연락처
	if(isset($_POST['company_tel']) === false){
		nowexit(false, '회사 대표연락처 값이 없습니다.');
	}
	if($_POST['company_tel'] === '' || $_POST['company_tel'] === null){
		nowexit(false, '회사 대표연락처 값이 없습니다.');
	}
	if(mb_strlen($_POST['company_tel']) > 100){
		nowexit(false, '회사 대표연락처 값의 길이수 제한은 100자 입니다.');
	}
	$company_tel = $_POST['company_tel'];

	// 기존에 등록된 회사 정보인지 확인
	$select_sql = "SELECT sid from company_data WHERE company_name='$company_name' AND company_ceo='$company_ceo' AND company_tel='$company_tel' AND company_number='$company_number';";

	$query_result = sql($select_sql);
	$query_result = select_process($query_result);

	// 기존 회사정보가 있는 경우
	$company_sid = null;
	if($query_result['output_cnt'] > 0){
		$company_sid = $query_result[0]['sid'];
	}

	// 기존 회사정보와 중복된 값이 없는 경우 => 신규!!!
	if($query_result['output_cnt'] === 0){

		// 신규 등록
		$insert_sql = "INSERT INTO company_data(company_name, company_ceo, company_tel, company_number) VALUES('$company_name', '$company_ceo', '$company_tel', '$company_number');";
		$query_result = sql($insert_sql);

		// 신규 등록 후 sid 재조회
		$query_result = sql($select_sql);
		$query_result = select_process($query_result);

		// 재조회 후 sid가 있을 경우 회사코드 저장
		if($query_result['output_cnt'] > 0){
			$company_sid = $query_result[0]['sid'];
		}
	}

	// 아이디 중복 검사
	$select_sql = "SELECT sid from user_data WHERE id='$id';";
	$query_result = sql($select_sql);
	$query_result = select_process($query_result);

	//입력받은 아이디로 조회 후 존재할 경우 
	if($query_result['output_cnt'] > 0){
		nowexit(false, '이미 사용중인 아이디입니다.');
	}

	// 회원 등록
	$insert_sql = "INSERT INTO user_data(id, pw, is_admin, company_sid) VALUES('$id', '$pw', '0', '$company_sid');";
	$query_result = sql($insert_sql);

	// bool타입인지 아니면
	if(is_bool($query_result) === false){
		nowexit(false, '회원가입을 실패했습니다.');
	}

	nowexit(true, '회원가입이 완료되었습니다.');
?>

