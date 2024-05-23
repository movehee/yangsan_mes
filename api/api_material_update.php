<?php 

	// 기준정보 자재 수정 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';


	//유효성 검사(자재sid , 자재명, 자재단가)
	if(isset($_POST['material_sid']) === false){
		nowexit(false,'자재 sid의 값이 없습니다1');
	}
	if($_POST['material_sid'] === '' || $_POST['material_sid'] === null){
		nowexit(false, '자재 sid의 값이 없습니다2.');
	}

	//자재sid
	$material_sid = $_POST['material_sid'];

	if(isset($_POST['material_name']) === false){
	nowexit(false,'자재명의 값이 없습니다');
	}
	if($_POST['material_name'] === '' || $_POST['material_name'] === null){
		nowexit(false, '자재명의 값이 없습니다.');
	}
	//자재명
	$material_name = $_POST['material_name'];

	if(isset($_POST['material_price']) === false){
	nowexit(false,'자재단가 값이 없습니다');
	}
	if($_POST['material_price'] === '' || $_POST['material_price'] === null){
		nowexit(false, '자재단가 값이 없습니다.');
	}

	//자재단가
	$material_price = $_POST['material_price'];

	//자재 비고란
	$material_note = $_POST['material_note'];
	
	//입력받은 정보를 조건으로(자재단가와 자재명,회사코드) 거래처 sid를 검색
	$select_sql = "SELECT sid FROM material_info WHERE 
		company_sid = '".__COMPANY_SID__."' AND material_name = '$material_name';";

	$query_result = sql($select_sql);
	$query_result = select_process($query_result);

	$is_duplication = false;
	// 조회된 자재 sid가 있을 경우
	if($query_result['output_cnt'] > 0){
		//조회된 자재 sid와 현재 로그인된 회사코드의 거래처sid와 불일치시
		if($query_result[0]['sid'] !== $material_sid ){
			$is_duplication = true;
		}
	}
	// (자재가격과 자재명,회사코드) 중복시 중단
	if($is_duplication === true){
		nowexit(false,'중복된 값이 있습니다');
	}

	//자재 기준정보 수정
	$update_sql = "UPDATE material_info SET material_name = '$material_name' , material_price = '$material_price', material_note ='$material_note' 
		 WHERE sid = '$material_sid';";

	$query_result = sql($update_sql);

	nowexit(true, '수정이 완료되었습니다.');


	



?>