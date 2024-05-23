<?php

	// 기준정보 자재 삭제 api

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
	$checked_sid_cnt = count($checked_sid);
	if($checked_sid_cnt === 0){
		nowexit(false,'선택된 항목이 없습니다.');
	}

	$find_sid = implode("','", $checked_sid);

	//bom 조회(조건: 자재sid)
	$select_bom_sql = "SELECT material_sid FROM bom_info WHERE company_sid ='".__COMPANY_SID__."' AND material_sid IN('$find_sid');";

	$query_result_bom = sql($select_bom_sql);
	$query_result_bom = select_process($query_result_bom);

	//만약 bom 테이블에서 사용중이라면, nowexit로 삭제 막기
	for($i=0; $i<$query_result_bom['output_cnt']; $i++){
		if($query_result_bom['output_cnt'] > 0){
			nowexit(false,'bom에서 사용중인 자재입니다.');
		}
	}

	//자재발주조회(조건: 자재sid)
	$select_order_sql = "SELECT material_sid FROM material_order_info WHERE company_sid ='".__COMPANY_SID__."' AND material_sid IN('$find_sid');";

	$query_result_order = sql($select_order_sql);
	$query_result_order = select_process($query_result_order);
	
	//만약 자재발주 테이블에서 사용중이라면, nowexit로 삭제 막기
	for($i=0; $i<$query_result_order['output_cnt']; $i++){

		if($query_result_order['output_cnt'] > 0){
			nowexit(false,'자재발주에서 사용중인 자재입니다.');
		}
	}

	//자재재고조회(조건: 자재sid)
	$select_stock_sql = "SELECT material_sid FROM material_stock WHERE company_sid ='".__COMPANY_SID__."' AND material_sid IN('$find_sid');";

	$query_result_stock = sql($select_stock_sql);
	$query_result_stock = select_process($query_result_stock);
	
	//만약 자재재고 테이블에서 사용중이라면, nowexit로 삭제 막기
	for($i=0; $i<$query_result_stock['output_cnt']; $i++){

		if($query_result_stock['output_cnt'] > 0){
			nowexit(false,'자재재고에서 사용중인 자재입니다.');
		}
	}

	//전달받은 자재sid들을 자재테이블에서 루프를 통해 삭제
	for($i=0; $i<$checked_sid_cnt; $i++){

		//선택된 자재sid을 삭제 sql작성
		$delete_sql = "DELETE FROM material_info WHERE sid = '$checked_sid[$i]';";
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