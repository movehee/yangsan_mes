<?php

	//생산계획 등록 페이지 수주 선택 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//order_number 유효성 검사
	if(isset($_POST['order_number']) === false){
		nowexit(false, '주문번호가 없습니다.1');
	}
	if($_POST['order_number'] === '' || $_POST['order_number'] === null){
		nowexit(false, '주문번호가 없습니다.2');
	}
	if(is_int((int)$_POST['order_number']) === false){
		nowexit(false, '주문 번호 값이 정수가 아닙니다.');
	}
	//order_number 저장
	$order_number = $_POST['order_number'];

	//수주db에 있는 수주정보 가지고 오기
	$select_order_sql = "SELECT sid, product_sid, product_cnt FROM order_info WHERE order_number = '$order_number' AND company_sid = '".__COMPANY_SID__."'";
	
	$query_result_order = sql($select_order_sql);
	$query_result_order = select_process($query_result_order);

	//등록된 수주가 없으면 false
	if($query_result_order['output_cnt'] === 0){
		nowexit(false, '수주정보를 찾을 수 없습니다.');
	}

	$order_sid_arr = array();
	$product_sid_arr = array();
	$remain_cnt = array();
	$product_sid_map = array();
	$order_sid_map = array();
	
	// 수주 가공
	for($i=0; $i<$query_result_order['output_cnt']; $i++){

		// order_sid PUSH
		if(in_array($query_result_order[$i]['sid'], $order_sid_arr) === false){
			array_push($order_sid_arr, $query_result_order[$i]['sid']);
		}
		// product_sid PUSH
		if(in_array($query_result_order[$i]['product_sid'], $product_sid_arr) === false){
			array_push($product_sid_arr, $query_result_order[$i]['product_sid']);
		}

		// 계획 가능 수량 매핑 데이터 / Key : order_sid
		$remain_cnt[$query_result_order[$i]['sid']] = (int)$query_result_order[$i]['product_cnt'];

		// 품목sid 매핑 데이터 / Key : order_sid
		$product_sid_map[$query_result_order[$i]['sid']] = $query_result_order[$i]['product_sid'];

		// 오더sid 매핑 데이터 / key : product_sid
		$order_sid_map[$query_result_order[$i]['product_sid']] = $query_result_order[$i]['sid'];

	}

	// 조회된 품목 sid가 없으면 중단 
	if(count($product_sid_arr) === 0){
		nowexit(false, '조회 가능한 품목 정보가 없습니다.');
	}

	// 품목 정보 조회
	$product_sid_arr = "'".implode("','", $product_sid_arr)."'";
	$select_product_sql = "SELECT sid, product_name, product_price FROM product_info WHERE company_sid='".__COMPANY_SID__."' AND sid IN($product_sid_arr);";
	$query_result_product = sql($select_product_sql);
	$query_result_product = select_process($query_result_product);

	if($query_result_product['output_cnt'] === 0){
		nowexit(false, '조회된 품목정보가 없습니다.');
	}

	// 품목명 매핑 데이터 / Key : product_sid
	$product_name = array();
	$product_price = array();
	for($i=0; $i<$query_result_product['output_cnt']; $i++){

		$product_name[$query_result_product[$i]['sid']] = $query_result_product[$i]['product_name'];
		$product_price[$order_sid_map[$query_result_product[$i]['sid']]] = $query_result_product[$i]['product_price'];

	}

	if(count($order_sid_arr) === 0){
		nowexit(false, '조회 가능한 수주 정보가 없습니다.');
	}

	// 생산 계획 조회
	$order_sid_arr = "'".implode("','", $order_sid_arr)."'";
	$select_plan_sql = "SELECT order_sid, plan_cnt FROM plan_info WHERE company_sid='".__COMPANY_SID__."' AND order_sid IN($order_sid_arr);";
	$query_result_plan = sql($select_plan_sql);
	$query_result_plan = select_process($query_result_plan);

	// 계획 가능 수량 차감
	for($i=0; $i<$query_result_plan['output_cnt']; $i++){

		// 존재하지 않는 order_sid로 접근 시 PASS
		if(isset($remain_cnt[$query_result_plan[$i]['order_sid']]) === false){
			continue;
		}
		$remain_cnt[$query_result_plan[$i]['order_sid']] -= $query_result_plan[$i]['plan_cnt']; // 기 등록된 계획수량만큼 수주수량에서 차감

		// 계획가능한 잔여수량이 0 이하가 되면 UNSET
		if($remain_cnt[$query_result_plan[$i]['order_sid']] <= 0){
			unset($remain_cnt[$query_result_plan[$i]['order_sid']]);
		}

	}

	$order_keys = array_keys($remain_cnt);
	$order_keys_cnt = count($order_keys);

	$option_list = array();
	for($i=0; $i<$order_keys_cnt; $i++){

		$temp = array();
		$temp['product_name'] = $product_name[$product_sid_map[$order_keys[$i]]];
		$temp['order_sid'] = $order_keys[$i];
		
		array_push($option_list, $temp);

	}

	$result['remain_cnt'] = $remain_cnt;
	$result['option_list'] = $option_list;
	$result['product_price'] = $product_price;

	nowexit(true, '수주 받은 제품 정보를 가져왔습니다.' );

?>