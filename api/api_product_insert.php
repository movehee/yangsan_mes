<?php 
	
	//기준정보 품목 등록 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//전달받은 입력값 유효성 검사(품목명, 품목 가격,품목 코드)
	if(isset($_POST['product_name']) === false){
		nowexit(false,'품목명의 값이 없습니다.');
	}
	if($_POST['product_name'] === '' || $_POST['product_name'] === null){
		nowexit(false, '품목명의 값이 없습니다.');
	}
	if(mb_strlen($_POST['product_name']) > 100){
		nowexit(false, '품목명의 값이 100자가 넘습니다.');
	}
	$product_name = $_POST['product_name'];

	if(isset($_POST['product_price']) === false){
		nowexit(false,'품목 가격의 값이 없습니다.');
	}
	if($_POST['product_price'] === '' || $_POST['product_price'] === null){
		nowexit(false, '품목 가격의 값이 없습니다.');
	}
	if(mb_strlen($_POST['product_price']) > 100){
		nowexit(false, '품목 가격의 값이 100자가 넘습니다.');
	}
	$product_price = $_POST['product_price'];

	if(isset($_POST['product_code']) === false){
		nowexit(false,'품목 코드의 값이 없습니다.');
	}
	if($_POST['product_code'] === '' || $_POST['product_code'] === null){
		nowexit(false, '품목 코드의 값이 없습니다.');
	}
	if(mb_strlen($_POST['product_code']) > 100){
		nowexit(false, '품목 코드의 값이 100자가 넘습니다.');
	}
	$product_code = $_POST['product_code'];
	//품목 비고란
	$product_note = $_POST['product_note'];

	//입력받은 품목 정보를 조건으로 기존 등록된 품목 정보인지 확인
	$select_sql = "SELECT sid FROM product_info WHERE company_sid = '".__COMPANY_SID__."' AND (product_name='$product_name'OR product_code='$product_code'); ";

	$query_result = sql($select_sql);
	$query_result = select_process($query_result);
	//기존에 품목이 있는경우 중복되었습니다 라는 콘솔로 내보내기
	$sid = null;
	if($query_result['output_cnt'] > 0){
		nowexit(false, '중복된 품목 입니다.');
	}
	//기존에 품목이 없는경우 신규 등록
	$insert_sql = "INSERT INTO product_info(product_name, product_price, product_code,product_note ,company_sid) VALUES ('$product_name', '$product_price', '$product_code','$product_note' ,'".__COMPANY_SID__."');";

		$query_result = sql($insert_sql);
		//bool타입이 아닐경우
		if (is_bool($query_result) === false) {
			nowexit(false, '품목 등록에 실패했습니다.');
		}
		//bool이 false일 경우
		if ($query_result === false) {
			nowexit(false, '품목 등록에 실패했습니다.');
		}


	nowexit(true,'품목 등록에 성공했습니다');
?>