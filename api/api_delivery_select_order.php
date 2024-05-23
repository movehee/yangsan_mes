<?php

	//납품 테이블에서 수주 선택 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	// 수주 번호가 POST로 전송되었는지 확인
	if(isset($_POST['order_number']) === false){
		nowexit(false, '수주 번호 정보를 불러올 수 없습니다.');
	}

	// 수주 번호가 비어있는지 확인
	if($_POST['order_number'] === '' || $_POST['order_number'] === null){
		nowexit(false, '수주 번호 정보가 없습니다.');
	}
	
	// 수주 번호 가져오기
	$order_number = $_POST['order_number'];

	// 품목과 수주 정보를 매핑하는 배열 선언
	$map = array();

	// 수주 조회(조건: 수주번호)
	$sql = "SELECT sid, product_sid, product_cnt FROM order_info WHERE company_sid='".__COMPANY_SID__."' AND order_number='$order_number';";
	$query_result = sql($sql);
	$order_db = select_process($query_result);

	// 조회 결과가 없으면 중단
	if($order_db['output_cnt'] === 0){
		nowexit(false, '수주 번호를 조회한 결과가 없습니다.');
	}

	$product_sid = array();
	$map['product_to_order'] = array();
	$order_cnt = array();
	$order_sid = array();
	for($i = $order_db['output_cnt'] - 1; $i >= 0; $i--){

		// 품목sid와 수주sid를 매핑
		$map['product_to_order'][$order_db[$i]['product_sid']] = $order_db[$i]['sid'];
		// 수주sid와 수주수량 매핑
		$order_cnt[$order_db[$i]['sid']] = (int)$order_db[$i]['product_cnt'];
		// 수주sid 배열에 저장
		array_push($order_sid, $order_db[$i]['sid']);
		// 품목sid 배열에 저장
		array_push($product_sid, $order_db[$i]['product_sid']);

	}

	// 품목 조회 및 가공
	$sql = "SELECT sid, product_name, product_price FROM product_info WHERE company_sid='".__COMPANY_SID__."' AND sid IN('".implode("','", $product_sid)."');";

	$query_result = sql($sql);
	$product_db = select_process($query_result);

	$product_data = array();
	$product_name = array();

	for($i = $product_db['output_cnt'] - 1; $i >= 0; $i--){

		//수주sid 뽑기
		$temp_order_sid = $map['product_to_order'][$product_db[$i]['sid']];
		//품목명 수주sid로 맵핑
		$product_name[$temp_order_sid] = $product_db[$i]['product_name'];

		$temp = array();
		$temp['product_stock'] = 0;
		$temp['product_price'] = $product_db[$i]['product_price'];
		$temp['remain_cnt'] = $order_cnt[$temp_order_sid];
		$temp['order_cnt'] = $order_cnt[$temp_order_sid];

		//품목데이터 키 : 수주sid 템프 푸쉬
		$product_data[$temp_order_sid] = $temp;
	}

	// 품목 재고 조회
	$sql = "SELECT sid, product_sid FROM product_stock WHERE company_sid='".__COMPANY_SID__."' AND product_sid IN('".implode("','", $product_sid)."') GROUP BY product_sid ORDER BY sid;";

	$query_result = sql($sql);
	$stock_db = select_process($query_result);


	$stock_sid = array();
	for($i = $stock_db['output_cnt'] - 1; $i >= 0; $i--){

		// 품목 재고 sid 확인
		if(in_array($stock_db[$i]['sid'], $stock_sid) === true){
			continue;
		}
		// 품목 재고 sid 배열에 푸쉬
		array_push($stock_sid, $stock_db[$i]['sid']);
	}

	// 재고가 있는 경우
	if(count($stock_sid) > 0){

		$sql = "SELECT sid, product_sid, product_stock_cnt FROM product_stock WHERE company_sid='".__COMPANY_SID__."' AND sid IN('".implode("','", $stock_sid)."');";

		$query_result = sql($sql);
		$stock_db = select_process($query_result);



		for($i = $stock_db['output_cnt'] - 1; $i >= 0; $i--){

			//수주sid 뽑기
			$temp_order_sid = $map['product_to_order'][$stock_db[$i]['product_sid']];

			//재고수량 맵핑
			$product_data[$temp_order_sid]['product_stock'] = $stock_db[$i]['product_stock_cnt'];

		}
	}

	// 납품 조회
	$sql = "SELECT sid, order_sid, delivery_cnt FROM delivery_info WHERE company_sid='".__COMPANY_SID__."' AND order_sid IN('".implode("','", $order_sid)."');";
	$query_result = sql($sql);
	$delivery_db = select_process($query_result);

	for($i = $delivery_db['output_cnt'] - 1; $i >= 0; $i--){

		//수주수량에서 조회된 납품 수량 차감
		$product_data[$delivery_db[$i]['order_sid']]['remain_cnt'] -= (int)$delivery_db[$i]['delivery_cnt'];
	}

	$product_list = array();
	$keys = array_keys($product_data);
	$keys_cnt = count($keys);

	for($i = 0; $i < $keys_cnt; $i++){

		$temp = array();
		$temp['product_name'] = $product_name[$keys[$i]];
		$temp['order_sid'] = $keys[$i];

		//품목 리스트 배열에 푸쉬
		array_push($product_list, $temp);
	}

	// 결과 반환
	$result['product_list'] = $product_list;
	$result['product_data'] = $product_data;

	nowexit(true);

?>
