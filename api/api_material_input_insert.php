<?php

    //자재 입고 등록 api

	define('__CORE_TYPE__', 'api');
    include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

    // 주문 데이터 유효성 검사
    if (!isset($_POST['order_data']) || !is_array($_POST['order_data']) || empty($_POST['order_data'])) {
        nowexit(false, '주문 데이터가 없습니다.');
    }
    $order_data = $_POST['order_data'];

    // 주문 번호 유효성 검사
    if (!isset($_POST['order_number']) || empty($_POST['order_number'])) {
        nowexit(false, '주문 번호가 없습니다.');
    }
    $order_number = $_POST['order_number'];

    // 주문번호를 조건으로 발주 sid, 자재 sid 맵핑
    $select_order_sql = "SELECT sid, material_sid FROM material_order_info WHERE company_sid = '".__COMPANY_SID__."' AND material_order_number = '".$order_number."';";

    $order_result = sql($select_order_sql);
    $order_result = select_process($order_result);

    $material_sid_map = array();

    for ($i = 0; $i < $order_result['output_cnt']; $i++) {

        //키 : 발주sid => 값 : 자재sid
        $material_sid_map[$order_result[$i]['sid']] = $order_result[$i]['material_sid'];
    }

    // 주문 데이터를 반복 처리
    for ($i = 0; $i < count($order_data); $i++) {

        $material_order_sid = $order_data[$i]['material_order_sid']; //자재발주sid
        $material_sid = $material_sid_map[$material_order_sid]; //자재sid 뽑기

        // 해당 자재의 최신 remain_cnt 가져오기
        $select_latest_remain_sql = "SELECT remain_cnt FROM material_stock WHERE company_sid = '".__COMPANY_SID__."' AND material_sid = '".$material_sid."' ORDER BY stock_date DESC LIMIT 1;";
        
        $latest_remain_result = sql($select_latest_remain_sql);
        $latest_remain_result = select_process($latest_remain_result);

        // 기본값 : 0
        $latest_remain_cnt = 0;
        if ($latest_remain_result['output_cnt'] > 0) {

            $latest_remain_cnt = $latest_remain_result[0]['remain_cnt'];
        }

        // 주문량만큼 최신 remain_cnt에 추가하여 새로운 remain_cnt 계산
        $new_remain_cnt = $latest_remain_cnt + $order_data[$i]['change_cnt'];

        // 자재입고 등록
        $insert_sql = "INSERT INTO material_stock (company_sid, material_sid, parent_sid, type, change_cnt, stock_note, remain_cnt)
                       VALUES ('".__COMPANY_SID__."', '".$material_sid."', '".$material_order_sid."', 'in', '".$order_data[$i]['change_cnt']."', '".$order_data[$i]['note']."', '".$new_remain_cnt."')";
                        
        $insert_result = sql($insert_sql);

        if ($insert_result === false) {
            nowexit(false, '입고 등록에 실패했습니다.');
        }
    }

nowexit(true, '등록에 성공했습니다.');

?>