<?php 

	//기준정보 품목 수정 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//유효성 검사(품목sid ,품목명, 품목가격,품목코드)
	if(isset($_POST['product_sid']) === false){
		nowexit(false,'품목 sid의 값이 없습니다1');
	}
	if($_POST['product_sid'] === '' || $_POST['product_sid'] === null){
		nowexit(false, '품목 sid의 값이 없습니다2.');
	}

	$product_sid = $_POST['product_sid'];

	if(isset($_POST['product_name']) === false){
	nowexit(false,'품목명의 값이 없습니다');
	}
	if($_POST['product_name'] === '' || $_POST['product_name'] === null){
		nowexit(false, '품목명의 값이 없습니다.');
	}
	$product_name = $_POST['product_name'];

	if(isset($_POST['product_price']) === false){
	nowexit(false,'품목 가격의 값이 없습니다');
	}
	if($_POST['product_price'] === '' || $_POST['product_price'] === null){
		nowexit(false, '품목 가격의 값이 없습니다.');
	}

	$product_price = $_POST['product_price'];

	if(isset($_POST['product_code']) === false){
	nowexit(false,'품목코드의 값이 없습니다');
	}
	if($_POST['product_code'] === '' || $_POST['product_code'] === null){
		nowexit(false, '품목코드의 값이 없습니다.');
	}

	$product_code = $_POST['product_code'];
	
	//품목 비고란
	$product_note  = $_POST['product_note'];

	//입력받은 정보를 조건으로(품목코드와 품목명,회사코드) 품목 sid를 검색
	$select_sql = "SELECT sid FROM product_info WHERE 
		company_sid = '".__COMPANY_SID__."' AND (product_name = '$product_name' OR product_code = '$product_code');";

	$query_result = sql($select_sql);
	$query_result = select_process($query_result);

	// 조회된 품목 sid가 있을 경우
	$is_duplication = false;
	if($query_result['output_cnt'] > 0){
		//조회된 품목 sid와 현재 로그인된 회사코드의 품목sid와 불일치시
		if($query_result[0]['sid'] !== $product_sid ){
			$is_duplication = true;
		}
	}
	// (품목코드와 품목명,회사코드) 중복시 중단
	if($is_duplication === true){
		nowexit(false,'중복된 값이 있습니다');
	}

	//품목 수정
	$update_sql = "UPDATE product_info SET product_name = '$product_name' , product_code = '$product_code' , product_price ='$product_price', product_note  ='$product_note'  WHERE sid = '$product_sid';";

	$query_result = sql($update_sql);

	nowexit(true, '수정이 완료되었습니다.');


	



?>