<?php
	
	//자재 입고 등록에서 자재 발주 선택 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//order_number 유효성 검사
	if(isset($_POST['order_number']) === false){
		nowexit(false, '주문 정보가 없습니다. 1');
	}
	if($_POST['order_number'] === '' || $_POST['order_number'] === null){
		nowexit(false, '주문 정보가 없습니다. 2');
	}
	if(is_int((int)$_POST['order_number']) === false){
		nowexit(false, '주문 정보 값이 정수가 아닙니다.');
	}
	$order_number = $_POST['order_number'];

	// 자재발주 db에 있는 자재발주 정보 가지고 오기
	$order_sql = "SELECT sid,  material_sid, material_order_cnt FROM material_order_info WHERE material_order_number = '$order_number' AND company_sid = '".__COMPANY_SID__."'";
	$query_result = sql($order_sql);
	$query_result = select_process($query_result);

	// 등록된 자재 발주가 없으면 false
	if($query_result['output_cnt'] === 0){
		nowexit(false, '발주정보를 찾을 수 없습니다.');
	}

	$order_sid_arr = array(); //발주SID넣기
	$material_sid_arr = array(); //자재 SID 넣기
	$remain_cnt = array(); //수량 계산
	$material_sid_map = array(); // 발주sid => 자재sid
	$material_order_map = array(); //자재sid => 발주sid

	// 자재 발주 가공
	for($i=0; $i<$query_result['output_cnt']; $i++){

		//order_sid push
		if(in_array($query_result[$i]['sid'], $order_sid_arr) === false){
			array_push($order_sid_arr, $query_result[$i]['sid']);
		}
		//material_sid push
		if(in_array($query_result[$i]['material_sid'], $material_sid_arr) === false){
			array_push($material_sid_arr, $query_result[$i]['material_sid']);
		}
		// 입고 가능 수량 매핑 데이터 / key : 발주sid
		$remain_cnt[$query_result[$i]['sid']] = (int)$query_result[$i]['material_order_cnt'];

		// 발주sid -> 자재sid
		$material_sid_map[$query_result[$i]['sid']] = $query_result[$i]['material_sid'];

		// 자재sid -> 발주sid
		$material_order_map[$query_result[$i]['material_sid']] = $query_result[$i]['sid'];

	}
	//자재 sid가 없을 시
	if(count($material_sid_arr) === 0){
		nowexit(false,'조회 가능한 자재 정보가 없습니다.');
	}

	//자재 정보 조회(발주테이블 자재sid 조건)
	$material_sid_arr = "'".implode("','",$material_sid_arr)."'";

	//자재 테이블 조회(조건 : 자재sid) 
	$select_material_sql ="SELECT sid, material_name, material_price FROM material_info WHERE company_sid='".__COMPANY_SID__."' AND sid IN($material_sid_arr);";

	$query_result = sql($select_material_sql);
	$query_result = select_process($query_result);

	//조회 결과가 없으면 중단
	if($query_result['output_cnt'] === 0){
		nowexit(false,'조회된 자재 정보가 없습니다.');
	}

	//자재명,자재가격
	$material_name = array();
	$material_price = array(); 
	
	for($i=0; $i<$query_result['output_cnt']; $i++){

		$material_name[$query_result[$i]['sid']] = $query_result[$i]['material_name'];
		$material_price[$query_result[$i]['sid']] = $query_result[$i]['material_price'];
		
	}

	//발주sid가 없을 시 중단
	if(count($order_sid_arr) === 0){
		nowexit(false,'조회 가능한 발주 정보가 없습니다.');
	}

	//입고 정보 조회(발주sid조건으로 parentsid 걸기 )
	$order_sid_arr = "'".implode("','",$order_sid_arr)."'";

	$stock_sql = "SELECT  material_sid, parent_sid, change_cnt ,remain_cnt FROM material_stock WHERE company_sid='".__COMPANY_SID__."' AND parent_sid IN($order_sid_arr);";

	$query_result = sql($stock_sql);
	$query_result = select_process($query_result);

	//입고 수량
	$input_cnt = array();

	//입고 가능 수량 차감
	for($i=0; $i<$query_result['output_cnt']; $i++){

		if(isset($input_cnt[$query_result[$i]['parent_sid']]) === false){
		 	$input_cnt[$query_result[$i]['parent_sid']] = 0;
		}

		//입고 수량 구하기
		$input_cnt[$query_result[$i]['parent_sid']] += $query_result[$i]['change_cnt'];
		
		//존재하지 않는 발주sid로 접근시 PASS
		 if(isset($remain_cnt[$query_result[$i]['parent_sid']]) === false){
		 	continue;
		 }
		 
		  //등록된 입고수량만큼 재고수량에서 차감
		 $remain_cnt[$query_result[$i]['parent_sid']] -=$query_result[$i]['change_cnt'];

		 //입고가능한 잔여수량이 0이하가 되면 unset
		 if($remain_cnt[$query_result[$i]['parent_sid']] <= 0){
		 	unset($remain_cnt[$query_result[$i]['parent_sid']]);
		 }
	}



	//키는 발주sid
	$order_keys =array_keys($remain_cnt);
	$order_keys_cnt = count($order_keys);
	$material_price_map = array(); 
	$option_list = array();
	for($i=0; $i<$order_keys_cnt; $i++){

		$material_price_map[$order_keys[$i]] = $material_price[$material_sid_map[$order_keys[$i]]];

		$temp = array();
		$temp['material_name'] = $material_name[$material_sid_map[$order_keys[$i]]];
		$temp['material_order_sid'] =$order_keys[$i];
		$temp['material_sid'] = $material_sid_map[$order_keys[$i]];
		
		array_push($option_list, $temp);
	}
	
	$result['remain_cnt'] = $remain_cnt;
	$result['option_list'] = $option_list;
	$result['material_price'] = $material_price_map;
	$result['input_cnt'] = $input_cnt;
	

nowexit(true,'자재 발주 거래처를 선택하였습니다.');


?>