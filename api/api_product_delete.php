<?php

	//기준정보 품목 삭제 api

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
	
	$find_sid = implode("','", $checked_sid);

	//bom 조회(조건: 품목 sid)
	$select_bom_sql = "SELECT product_sid FROM bom_info WHERE company_sid ='".__COMPANY_SID__."' AND product_sid IN('$find_sid');";
	
	$query_result_bom = sql($select_bom_sql);
	$query_result_bom = select_process($query_result_bom);

	//만약 bom 테이블에서 사용중이라면, nowexit로 삭제 막기
	for($i=0; $i<$query_result_bom['output_cnt']; $i++){

		if($query_result_bom['output_cnt'] > 0){
			nowexit(false,'bom에서 사용중인 품목입니다.');
		}
	}

	//수주 조회(조건: 품목 sid)
	$select_order_sql = "SELECT product_sid FROM order_info WHERE company_sid ='".__COMPANY_SID__."' AND product_sid IN('$find_sid');";
	$query_result_order = sql($select_order_sql);
	$query_result_order = select_process($query_result_order);

	//만약 수주 테이블에서 사용중이라면, nowexit로 삭제 막기
	for($i=0; $i<$query_result_order['output_cnt']; $i++){

		if($query_result_order['output_cnt'] > 0){
			nowexit(false,'수주에서 사용중인 품목입니다.');
		}
	}

	//품목재고 조회(조건: 품목 sid)
	$select_stock_sql = "SELECT product_sid FROM product_stock WHERE company_sid ='".__COMPANY_SID__."' AND product_sid IN('$find_sid');";
	$query_result_stock = sql($select_stock_sql);
	$query_result_stock = select_process($query_result_stock);

	//만약 품목재고 테이블에서 사용중이라면, nowexit로 삭제 막기
	for($i=0; $i<$query_result_stock['output_cnt']; $i++){
		if($query_result_stock['output_cnt'] > 0){
			nowexit(false,'품목에서 사용중인 품목입니다.');
		}
	}

	//생산등록 조회(조건: 품목 sid)
	$select_production = "SELECT product_sid FROM production_info WHERE company_sid = '".__COMPANY_SID__."' AND product_sid IN('$find_sid');";
	$query_result_production = sql($select_production);
	$query_result_production = select_process($query_result_production);
	
	//만약 생산등록 테이블에서 사용중이라면, nowexit로 삭제 막기
	for($i=0; $i<$query_result_production['output_cnt']; $i++){

		if($query_result_production['output_cnt'] > 0){
			nowexit(false,'생산등록에서 사용중인 품목입니다.');
		}
	}

	//전달받은 품목sid들을 품목테이블에서 루프를 통해 삭제
	for($i=0; $i<$checked_sid_cnt; $i++){

		//선택된 항목을 삭제 sql작성
		$delete_sql = "DELETE FROM product_info WHERE sid = '$checked_sid[$i]';";
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