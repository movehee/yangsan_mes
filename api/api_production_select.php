<?php
	
	//생산 등록에서 생산 계획 선택 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//plan_number 유효성 검사
	if(isset($_POST['plan_number']) === false){
		nowexit(false, '주문번호가 없습니다.1');
	}
	if($_POST['plan_number'] === '' || $_POST['plan_number'] === null){
		nowexit(false, '주문번호가 없습니다.2');
	}
	if(is_int((int)$_POST['plan_number']) === false){
		nowexit(false, '주문 번호 값이 정수가 아닙니다.');
	}
	//plan_number 저장
	$plan_number = $_POST['plan_number'];

	//계획 정보 가지고 오기
	$number_sql = "SELECT sid, order_sid, plan_cnt FROM plan_info WHERE plan_number = '$plan_number' AND company_sid = '".__COMPANY_SID__."';";
	$query_result = sql($number_sql);
	$query_result = select_process($query_result);

	//등록된 계획번호가 없으면 false 처리
	if($query_result['output_cnt'] === 0){
		nowexit(false, '계획번호를 찾을수 없습니다.');
	}
	//계획정보에서 가지고 올 order_sid, plan_sid 배열선언
	$order_sid_arr = array();
	$plan_sid_map = array();

	//생산 가능 수량 배열 선언
	$remain_cnt = array();
	//계획 정보 가공
	for($i=0; $i<$query_result['output_cnt']; $i++){

		//수주 sid push
		if(in_array($query_result[$i]['sid'], $order_sid_arr) === false){
			array_push($order_sid_arr, $query_result[$i]['order_sid']);
		}

		//계획 sid => 계획 수량
		$remain_cnt[$query_result[$i]['sid']] = (int)$query_result[$i]['plan_cnt'];
		//key => order_sid / 값 => plan_sid
		$plan_sid_map[$query_result[$i]['order_sid']] = $query_result[$i]['sid'];
		//key => plan_sid / 값 => order_sid
		$order_sid_in[$query_result[$i]['sid']] = $query_result[$i]['order_sid'];
	}


	//수주 데이터베이스에 있는 수주정보 가지고오기
	$order_sid = implode("','", $order_sid_arr);
	$order_sql ="SELECT sid, product_sid FROM order_info WHERE company_sid ='".__COMPANY_SID__."' AND sid IN('$order_sid');";

	$query_result = sql($order_sql);
	$query_result = select_process($query_result);

	//수주정보에서 가지고 올거 배열선언
	$product_sid = array();
	$product_sid_map = array();
	$order_sid_map = array();
	for($i=0; $i<$query_result['output_cnt']; $i++){

		//품목 sid 매핑
		array_push($product_sid, $query_result[$i]['product_sid']);
		//key => order_sid / 값 => product_sid
		$product_sid_map[$query_result[$i]['sid']] = $query_result[$i]['product_sid'];
		//key => product_sid / 값 => order_sid
		$order_sid_map[$query_result[$i]['product_sid']] = $query_result[$i]['sid'];
	}

	//품목 데이터베이스에 있는 품목정보 가지고 오기
	$product_sid_in = implode("','", $product_sid);
	$select_product_sql ="SELECT sid, product_name, product_price FROM product_info WHERE company_sid='".__COMPANY_SID__."' AND sid IN('$product_sid_in')";
	
	$query_result = sql($select_product_sql);
	$query_result = select_process($query_result);

	//등록된 품목 정보가 없을시 false
	if($query_result['output_cnt'] === 0){
		nowexit(false,'조회된 품목 정보가 없습니다.');
	}
	//품목명과 품목가격 배열선언
	$product_name = array();
	$product_price = array();
	//품목명,품목가격 저장
	for($i=0; $i<$query_result['output_cnt']; $i++){
		//product_sid를 키로 해서 product_name 매핑
		$product_name[$query_result[$i]['sid']] = $query_result[$i]['product_name'];
		//
		$product_price[$plan_sid_map[$order_sid_map[$query_result[$i]['sid']]]] = (int)$query_result[$i]['product_price'];
	}

	//bom 정보 가지고 오기
	$bom_sql ="SELECT sid, product_sid, material_sid ,ea FROM bom_info WHERE company_sid ='".__COMPANY_SID__."' AND product_sid IN('$product_sid_in')";
	$query_result = sql($bom_sql);
	$query_result = select_process($query_result);

	// 품목 sid, 자재 sid, 자재 수량 배열 초기화
	$bom_data = array();
	$material_sid = array();
	$material_cnt = array();

	// 각 행을 순회하면서 정보 저장
	for ($i = 0; $i < $query_result['output_cnt']; $i++) {

		// 현재 품목 sid
	    $product_sid = $query_result[$i]['product_sid']; 
	    // 해당 품목 sid에 대한 계획 sid
	    $plan_sid = $plan_sid_map[$order_sid_map[$product_sid]]; 

	    // bom_data 배열에 해당 계획 sid가 존재하지 않으면 배열 초기화
	    if (isset($bom_data[$plan_sid]) === false) {
	        $bom_data[$plan_sid] = array();
	    }

	    // bom_data에 넣을 자재sid와 자재 수량
	    $temp = array();
     	$temp['material_sid'] = $query_result[$i]['material_sid'];
  		$temp['material_cnt'] = $query_result[$i]['ea'];
	        
	    // bom_data의 해당 계획 sid를 키로 하여 자재 정보 push
		array_push($bom_data[$plan_sid], $temp);

	    // material_sid를 배열에 push
	    array_push($material_sid, $query_result[$i]['material_sid']);

	    // material_cnt에 자재 수량 매핑
	    $material_cnt[$query_result[$i]['material_sid']] = (int)$query_result[$i]['ea'];
	}
	

	// 자재sid별 최신 재고 sid 가져오기
	$stock_sid = "SELECT MAX(sid) as sid, material_sid FROM material_stock WHERE company_sid='".__COMPANY_SID__."' AND material_sid IN('".implode("','", $material_sid)."') GROUP BY material_sid ORDER BY sid DESC;";
	$query_result = sql($stock_sid);
	$query_result = select_process($query_result);

	$stock_sid_arr = array();
	
	for($i=$query_result['output_cnt']-1; $i>=0; $i--){

		array_push($stock_sid_arr, $query_result[$i]['sid']);
	}

	// 자재sid 배열 크기
	$material_sid_count = count($material_sid);

	// 자재sid별로 재고 수를 0으로 초기화
	for($i = 0; $i < $material_sid_count; $i++) {

	    $sid = $material_sid[$i];
	    $remain_material_cnt[$sid] = 0;
	}

	// 자재sid별 최신 재고 sid IN 조건 조회
	$select_stock_remain_sql = "SELECT sid, material_sid, remain_cnt FROM material_stock WHERE company_sid = '".__COMPANY_SID__."' AND sid IN('".implode("','", $stock_sid_arr)."');";
	$query_result = sql($select_stock_remain_sql);
	$query_result = select_process($query_result);

	// 조회 결과로 $remain_material_cnt 업데이트
	for($i = 0; $i < $query_result['output_cnt']; $i++) {

	    $material_sid = $query_result[$i]['material_sid'];

	    if(isset($remain_material_cnt[$material_sid])) {
	        $remain_material_cnt[$material_sid] = (int)$query_result[$i]['remain_cnt'];
	    }    
	}

	//생산 sql
	$production_sql = "SELECT product_sid, production_cnt FROM production_info WHERE company_sid='".__COMPANY_SID__."' AND product_sid IN('$product_sid_in');";
	$query_result = sql($production_sql);
	$query_result = select_process($query_result);

	$plan_cnt = array();

	for($i=0; $i<$query_result['output_cnt']; $i++){

        // plan_sid가 처음 등장하면 해당 키로 0을 초기화합니다.
        if(isset($plan_cnt[$plan_sid_map[$order_sid_map[$query_result[$i]['product_sid']]]]) === false){
            $plan_cnt[$plan_sid_map[$order_sid_map[$query_result[$i]['product_sid']]]] = 0;
        }

        // 각 plan_sid에 대해 production_cnt를 누적합니다.
        $plan_cnt[$plan_sid_map[$order_sid_map[$query_result[$i]['product_sid']]]]  += $query_result[$i]['production_cnt'];

		//존재하지 않는 product_sid로 접근시 pass
		if(isset($remain_cnt[$plan_sid_map[$order_sid_map[$query_result[$i]['product_sid']]]]) === false){
			continue;
		}
		//등록된 생산 수량 만큼 계획 수량에서 차감
		$remain_cnt[$plan_sid_map[$order_sid_map[$query_result[$i]['product_sid']]]] -= $query_result[$i]['production_cnt'];
		//생산 가능 잔여 수량이 0 이하가 되면 unset
		if($remain_cnt[$plan_sid_map[$order_sid_map[$query_result[$i]['product_sid']]]] <= 0){
			unset($remain_cnt[$plan_sid_map[$order_sid_map[$query_result[$i]['product_sid']]]]);
		}
	}

	//plan_sid를 키로 한 배열
	$plan_keys = array_keys($remain_cnt);
	$plan_keys_cnt = count($plan_keys);

	//option_list 생성
	$option_list = array();
	for($i=0; $i<$plan_keys_cnt; $i++){
		$temp = array();
		//제품명과 계획 sid push
		$temp['product_name'] = $product_name[$product_sid_map[$order_sid_in[$plan_keys[$i]]]];
		$temp['plan_key'] = $plan_keys[$i];

		array_push($option_list, $temp);

	}

	//js로 보내는 데이터  = 자재재고 수량, bom데이터, 옵션리스트, 제품 가격
	$result['remain_material_cnt'] = $remain_material_cnt;
	$result['bom_data'] = $bom_data;
	$result['option_list'] = $option_list;
	$result['product_price'] = $product_price;
	$result['remain_cnt'] = $remain_cnt;
	$result['plan_cnt'] = $plan_cnt;

	

	nowexit(true,'생산계획 선택이 완료되었습니다.');


?>