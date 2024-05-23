<?php

	//불량 원인 삭제 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	// 선택된 항목 유효성 검사
	if(isset($_POST['checked_sid'])=== false) {
		nowexit(false,'선택된 항목이 없습니다');
	}
	if(is_array($_POST['checked_sid']) === false){
		nowexit(false,'선택된 항목의 유효성이 올바르지 않습니다.');
	}
	$checked_sid = $_POST['checked_sid'];

	// 선택된 항목의 갯수 
	$checked_sid_cnt = count($checked_sid);

	// 선택된 항목이 0일 경우
	if($checked_sid_cnt === 0){
		nowexit(false,'선택된 항목이 없습니다.');
	}

	//전달받은 항목들을 루프를 통해 삭제
	for($i=0; $i<$checked_sid_cnt; $i++){

		//삭제 sql작성
		$delete_sql = "DELETE FROM error_info WHERE sid = '$checked_sid[$i]';";
		$query_result = sql($delete_sql);

		//bool 타입이 아닐시
		if(is_bool($query_result) === false){
			nowexit(false,'삭제가 되지 않았습니다.');
		}
		//bool이 false일 때
		if($query_result === false){
			nowexit(false,'삭제가 되지 않았습니다.');
		}
	}
	 

	nowexit(true,'삭제완료');


?>