<?php
	// 기준정보 자재 등록 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//전달받은 입력값 유효성 검사(자재명,자재 단가)
	if(isset($_POST['material_name']) === false){
		nowexit(false, '자재 명의 값이 없습니다.');
	}
	if(($_POST['material_name']) === '' || $_POST['material_name'] === null){
		nowexit(false, '자재명의 값이 없습니다.');
	}
	if(mb_strlen($_POST['material_name']) > 100){
		nowexit(false, '자재명의 값이 100자가 넘습니다.');
	}
	$material_name = $_POST['material_name'];
	//
	if(isset($_POST['material_price']) === false){
		nowexit(false, '자재단가의 값이 없습니다.');
	}
	if(($_POST['material_price']) === '' || $_POST['material_price'] === null){
		nowexit(false, '자재단가의  값이 없습니다.');
	}
	if(mb_strlen($_POST['material_price']) > 100){
		nowexit(false, '자재단가의 값이 100자가 넘습니다.');
	}
	$material_price = $_POST['material_price'];

	//자재 비고란
	$material_note = $_POST['material_note'];

	//입력받은 자재 정보를 조건으로 기존 등록된 거래처 정보인지 확인
	$select_sql = "SELECT sid FROM material_info WHERE material_name='$material_name'AND material_price ='$material_price'; ";

	$result_sql = sql($select_sql);
	$result_sql = select_process($result_sql);
	//기존에 거래처가 있는경우 중복되었습니다 라는 콘솔로 내보내기
	$sid = null;
	if($result_sql['output_cnt'] > 0){
		nowexit(false, '중복된 자재 입니다.');
	}

	//기존에 자재가 없는경우 신규 등록
	//신규 등록
	$insert_sql = "INSERT INTO material_info(material_name, material_price,material_note ,company_sid) VALUES ('$material_name', '$material_price','$material_note' ,'".__COMPANY_SID__."');";

	$result_sql = sql($insert_sql);
	//bool타입이 아닐경우
	if (is_bool($result_sql) === false) {
		nowexit(false, '자재 등록에 실패했습니다.');
	}
	//bool이 false일 경우
	if ($result_sql === false) {
		nowexit(false, '자재 등록에 실패했습니다.');
	}


	nowexit(true, '자재 등록을 완료했습니다.');
?>