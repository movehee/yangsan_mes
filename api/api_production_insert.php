<?php
	
	//생산 등록 api

	define('__CORE_TYPE__','api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//계획 번호, 생산 데이터 유효성 검사
	//계획 번호 유효성 검사
	if(isset($_POST['plan_number']) === false){
		nowexit(false, '계획번호가 없습니다.(1)');
	}
	if($_POST['plan_number'] === '' || $_POST['plan_number'] === null){
		nowexit(false, '계획번호가 없습니다.(2)');
	}
	// 계획 번호 저장
	$plan_number = $_POST['plan_number'];
	//생산 데이터 유효성 검사
	if(isset($_POST['production_data']) === false){
		nowexit(false, '생산 데이터가 없습니다.(1)');
	}
	if(is_array($_POST['production_data']) === false){
		nowexit(false, '생산 데이터가 배열이 아닙니다.');
	}
	if(empty($_POST['production_data'])){
		nowexit(false, '생산 데이터가 없습니다.(2)');
	}
	//생산 데이터 저장
	$production_data = $_POST['production_data'];

	//생산데이터 수량
	$production_data_cnt = count($production_data);

	//생산 번호 찾기
	$production_number = 1;
	//생산등록넘버 조회
	$production_num_sql = "SELECT production_number FROM production_info WHERE company_sid = '".__COMPANY_SID__."' GROUP BY production_number ORDER BY production_number DESC LIMIT 0,1 ";
	$query_result_num = sql($production_num_sql);
	$query_result_num = select_process($query_result_num);

	//조회결과가 있을 경우
	if($query_result_num['output_cnt'] > 0){
		$production_number = $query_result_num[0]['production_number']+1;
	}

	//품목sid 찾기
	//생산계획 테이블 조회
	$select_plan_sql = "SELECT sid, order_sid FROM plan_info WHERE company_sid = '".__COMPANY_SID__."' AND plan_number = '$plan_number';";

	$query_result_plan = sql($select_plan_sql);
	$query_result_plan = select_process($query_result_plan);

	$sid_product_map = array();
	$order_sid = array();

	for($i=0; $i<$query_result_plan['output_cnt']; $i++){

		//수주sid 배열에 푸쉬
		array_push($order_sid, $query_result_plan[$i]['order_sid']);

		//키 : 계획sid => 값: 수주sid
		$sid_product_map[$query_result_plan[$i]['sid']] = $query_result_plan[$i]['order_sid'];
	
	}

	//조회된 수주sid가 있을 경우
	if(count($order_sid) > 0){
		$order_sql_in = implode("','", $order_sid);
		//수주테이블 조회(조건: 계획넘버의 수주sid)
		$select_order_sql = "SELECT sid , product_sid FROM order_info WHERE company_sid = '".__COMPANY_SID__."' AND sid IN('$order_sql_in');";

		$query_result_order = sql($select_order_sql);
		$query_result_order = select_process($query_result_order);

		$order_product_map = array();

		for($i=0; $i<$query_result_order['output_cnt']; $i++){

			//키: 수주sid => 값: 품목sid
			$order_product_map[$query_result_order[$i]['sid']] = $query_result_order[$i]['product_sid'];

		}
	}

	//생산테이블에 정보 등록, 품목 재고 등록
	for($i=0; $i<$production_data_cnt; $i++){

		//품목sid
		$product_sid = $order_product_map[$sid_product_map[$production_data[$i]['plan_key']]];

		$production_insert_sql = "INSERT INTO production_info (company_sid, product_sid, plan_sid, production_number, production_cnt, production_note) VALUES ('".__COMPANY_SID__."', $product_sid, '".$production_data[$i]['plan_key']."', '$production_number', '".$production_data[$i]['production_cnt']."', '".$production_data[$i]['note']."');";

		$query_result_insert = sql($production_insert_sql);

		//등록 실패 시 중단
		if(is_bool($query_result_insert) === false){
			nowexit(false, '생산등록에 실패했습니다.(1)');
		}
		if($query_result_insert === false){
			nowexit(false, '생산등록에 실패했습니다.(2)');
		}

		//등록한 생산 등록sid 찾아오기
		//가장 최근에 등록된 생산 정보의 sid 조회
		$select_production_sql = "SELECT sid FROM production_info WHERE company_sid = '".__COMPANY_SID__."' AND plan_sid = '".$production_data[$i]['plan_key']."' ORDER BY production_date DESC LIMIT 1";

		$query_result_production = sql($select_production_sql);
		$query_result_production = select_process($query_result_production);

		//생산 sid 선언
		$production_sid = $query_result_production[0]['sid']; 

		//품목 재고 테이블에서 최신 재고 수량 가져오기
        // 해당 자재의 최신 remain_cnt 가져오기
        $select_product_remain_sql = "SELECT product_stock_cnt FROM product_stock WHERE company_sid = '".__COMPANY_SID__."' AND product_sid = '".$product_sid."' ORDER BY product_stock_date DESC LIMIT 1;";
        
        $latest_remain_result = sql($select_product_remain_sql);
        $latest_remain_result = select_process($latest_remain_result);

        // 기본값 : 0
        $latest_product_stock_cnt = 0;
        if ($latest_remain_result['output_cnt'] > 0) {

            $latest_product_stock_cnt = $latest_remain_result[0]['product_stock_cnt'];
        }

        // 생산수량만큼 최신 product_stock_cnt 추가하여 새로운 product_stock_cnt 계산
        $new_product_stock_cnt = $latest_product_stock_cnt + $production_data[$i]['production_cnt'];

        // 품목 재고 등록
        $insert_sql = "INSERT INTO product_stock (company_sid, product_sid, parent_sid, type, product_change_cnt, product_stock_cnt, stock_note)VALUES('".__COMPANY_SID__."', '".$product_sid."', '$production_sid', 'in', '".$production_data[$i]['production_cnt']."',$new_product_stock_cnt, '');";
                        
        $insert_result = sql($insert_sql);

        //품목 재고 등록 실패 시 중단
        if (is_bool($insert_result) === false) {
            nowexit(false, '품목재고 등록에 실패했습니다.');
        }
        if ($insert_result === false) {
            nowexit(false, '품목재고 등록에 실패했습니다.');
        }

        //bom테이블에 품목을 조건으로 자재sid,수량 조회
        $select_bom_sql = "SELECT material_sid, ea FROM bom_info WHERE company_sid = '".__COMPANY_SID__."' AND product_sid = '".$product_sid."';";

		$query_result_bom = sql($select_bom_sql);
		$query_result_bom = select_process($query_result_bom);

		//품목을 조건로 조회한 bom for문
		for ($j = 0; $j < $query_result_bom['output_cnt']; $j++) {

		    $material = $query_result_bom[$j]; //자재 데이터
		    $total_need_cnt = $material['ea'] * $production_data[$i]['production_cnt']; //필요 자재 소요량
		    
		    // 현재 자재 재고 조회(조건: bom 조회된 자재sid)
		    $select_material_stock_sql = "SELECT remain_cnt FROM material_stock WHERE material_sid = '".$material['material_sid']."' ORDER BY stock_date DESC LIMIT 1";

		    $query_result_material_stock = sql($select_material_stock_sql);
		    $query_result_material_stock = select_process($query_result_material_stock);

		    //기본값 = 0 설정
		    $material_stock_cnt = 0;
		    if ($query_result_material_stock['output_cnt'] > 0) {
		    	//현재 자재 재고 수량
		        $material_stock_cnt = $query_result_material_stock[0]['remain_cnt'];
		    }

		    // 새로운 자재 재고 수량 계산
		    $new_stock_cnt = $material_stock_cnt - $total_need_cnt;

		    // 자재 재고 테이블에 등록
		    $update_material_stock_sql = "INSERT INTO material_stock (company_sid, material_sid, parent_sid, type, change_cnt, stock_note, remain_cnt) VALUES ('".__COMPANY_SID__."', '".$material['material_sid']."', '$production_sid', 'out', '".$total_need_cnt."', ' ', '".$new_stock_cnt."')";

		    $query_result_stock = sql($update_material_stock_sql);

		    //자재 재고 등록 실패 시 중단
	        if (is_bool($query_result_stock) === false) {
	            nowexit(false, '자재 재고 등록에 실패했습니다.');
	        }
	        if ($query_result_stock === false) {
	            nowexit(false, '자재 재고 등록에 실패했습니다.');
	        }

		}
	}

	
	nowexit(true,'생산 등록이 완료되었습니다.');

?>




