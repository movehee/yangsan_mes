<?php 

	//bom 수정 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	// 유효성 검사(품목 SID, material_data)
	if(isset($_POST['product_sid']) === false ){
		nowexit(false,'품목 값이 없습니다.1');
	}

	if($_POST['product_sid'] === null || $_POST['product_sid'] === ''){
		nowexit(false,'품목 값이 없습니다.2');
	}

	if(is_int((int)$_POST['product_sid']) === false ){
		nowexit(false,'품목 값이 유효성이 맞지 않습니다.3');
	}

	$product_sid = (int)$_POST['product_sid'];

	if(isset($_POST['material_data']) === false){
		nowexit(false, '자재 값이 없습니다.');
	}
	if(is_array($_POST['material_data']) === false){
		nowexit(false,'자재 값이 없습니다.');
	}

	$material_data = $_POST['material_data'];

	if(count($material_data) === 0){
		nowexit(false,'값이 0입니다.'); 
	}

	//bom에 등록된 품목이 있는지 확인
	$select_bom_sql = "SELECT sid FROM bom_info WHERE product_sid = '$product_sid' AND company_sid ='".__COMPANY_SID__."';";	
	$query_result = sql($select_bom_sql);
	$query_result = select_process($query_result);

	//조회결과가 없으면 예외처리
	if($query_result['output_cnt'] === 0){
		nowexit(false,'등록된 품목이 없습니다.');
	}

	//bom 정보 삭제
	$delete_bom_sql = "DELETE FROM bom_info WHERE product_sid = '$product_sid' AND company_sid ='".__COMPANY_SID__."';";	
	$query_result = sql($delete_bom_sql);

	//삭제 실패 시 예외처리
	if(is_bool($query_result)=== false){
		nowexit(false,'삭제를 실패했습니다.1');
	}
	if($query_result === false){
		nowexit(false,'삭제를 실패했습니다.2');
	}

	$material_data_cnt = count($material_data);
	
	//for문으로 bom 다시 재등록
	for($i=0; $i<$material_data_cnt; $i++){

	$insert_bom_sql = "INSERT INTO bom_info(product_sid, material_sid,
			ea,company_sid,bom_note) VALUES ('$product_sid','".$material_data[$i]['material_key']."', '".$material_data[$i]['material_cnt']."', '".__COMPANY_SID__."' , '".$material_data[$i]['note']."' );";
		
	$query_result = sql($insert_bom_sql);

		if(is_bool($query_result)=== false){
			nowexit(false,'bom 등록이 되지 않았습니다.1');
		}
		if($query_result === false){
			nowexit(false,'bom 등록이 되지 않았습니다.2');
		}

	}

	nowexit(true,'bom등록이 완료되었습니다.');






?>