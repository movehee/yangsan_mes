<?php
	//bom 삭제 api
	
	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//유효성 검사(bom_sid)
	if(isset($_POST['bom_sid']) === false ){
		nowexit(false,'bom 값이 없습니다.1');
	}
	if($_POST['bom_sid'] === null || $_POST['bom_sid'] === ''){
		nowexit(false,'bom 값이 없습니다.2');
	}
	if(is_int((int)$_POST['bom_sid']) === false ){
		nowexit(false,'bom 값이 유효성이 맞지 않습니다.3');
	}
	$bom_sid = (int)$_POST['bom_sid'];

	//bom 정보 삭제
	$delete_bom_sql = "DELETE FROM bom_info WHERE sid = '$bom_sid';";
	$query_result = sql($delete_bom_sql);

	//삭제 실패 시 예외처리
	if(is_bool($query_result)=== false){
		nowexit(false,'삭제를 실패했습니다.1');
	}
	if($query_result === false){
		nowexit(false,'삭제를 실패했습니다.2');
	}

	nowexit(true,'삭제가 완료되었습니다.');


?>