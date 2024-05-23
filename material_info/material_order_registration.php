<?php

    // 자재발주 등록/수정 페이지

    define('__CORE_TYPE__', 'view');
    include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

    // 업데이트 여부
    $is_update = true;

    // 자재발주넘버 유효성 검사
    if (!isset($_POST['material_order_number']) || $_POST['material_order_number'] === '' || $_POST['material_order_number'] === null) {
        $is_update = false;
    }

    // 거래처 조회
    $sql = "SELECT sid, account_name FROM account_info WHERE company_sid='" . __COMPANY_SID__ . "';";
    $query_result = sql($sql);
    $account_list = select_process($query_result);

    // 배열 선언(거래처명)
    $account_name = array();

    // for문으로 거래처sid, 거래처명 배열에 push
    for ($i = 0; $i < $account_list['output_cnt']; $i++) {
        $account_name[$account_list[$i]['sid']] = $account_list[$i]['account_name'];
    }

    // 자재 조회
    $sql = "SELECT sid, material_name, material_price FROM material_info WHERE company_sid='" . __COMPANY_SID__ . "' ORDER BY material_name ASC;";
    $query_result_material = sql($sql);
    $query_result_material = select_process($query_result_material);

    // 자재 정보 가공
    $material_list = array(); // 출력용 자재 리스트
    $material_name = array(); // 자재 이름 매핑 => key : material_sid
    $material_price = array(); // 자재 가격 매핑 => key : material_sid
    for ($i = 0; $i < $query_result_material['output_cnt']; $i++) {
        // 출력용 자재 리스트 PUSH
        $temp = array();
        $temp['material_key'] = $query_result_material[$i]['sid'];
        $temp['material_name'] = $query_result_material[$i]['material_name'];
        array_push($material_list, $temp);

        // 자재 가격 매핑
        $material_price[$query_result_material[$i]['sid']] = $query_result_material[$i]['material_price'];
        // 자재 이름 매핑
        $material_name[$query_result_material[$i]['sid']] = $query_result_material[$i]['material_name'];
    }

    $material_list_cnt = count($material_list);

    // 자재 발주 정보 기본셋
    $order_data = array();
    $default_set = array();
    $default_set['material_key'] = '';
    $default_set['material_cnt'] = 0;
    $default_set['amount'] = 0;
    $default_set['note'] = '';

    //****************************************수정****************************************
    // 수정일때의 자재 발주 정보
    if ($is_update === true) {
        $title = '자재발주 수정';
        $btn_text = '수정';
        $api_url = 'api_material_order_update';

        $update_material_number = $_POST['material_order_number'];

        // 자재발주넘버를 조건으로 조회
        $select_material_order_sql = "SELECT sid, material_order_date, account_sid, material_sid, material_order_cnt, material_order_note FROM material_order_info WHERE company_sid = '" . __COMPANY_SID__ . "' AND material_order_number = '$update_material_number'";
        $query_result_order_data = sql($select_material_order_sql);
        $query_result_plan_data = select_process($query_result_order_data);

        for ($i = 0; $i < $query_result_plan_data['output_cnt']; $i++) {
            $temp = array();
            $temp['material_order_sid'] = $query_result_plan_data[$i]['sid'];
            $temp['material_key'] = $query_result_plan_data[$i]['material_sid'];
            $temp['material_cnt'] = $query_result_plan_data[$i]['material_order_cnt'];
            $temp['note'] = $query_result_plan_data[$i]['material_order_note'];
            $temp['amount'] = $query_result_plan_data[$i]['material_order_cnt'] * $material_price[$query_result_plan_data[$i]['material_sid']];

            array_push($order_data, $temp);
        }

        $update_account = $account_name[$query_result_plan_data[0]['account_sid']]; // 거래처명
        $up_account_sid = $query_result_plan_data[0]['account_sid']; // 거래처sid
        $update_date = $query_result_plan_data[0]['material_order_date']; // 수정날짜

        $order_data_cnt = count($order_data);

    } else {
        //******************등록*******************
        // 신규등록일때의 자재 발주 정보
        $title = '자재발주 등록';
        $btn_text = '등록';
        $api_url = 'api_material_order_insert';

        array_push($order_data, $default_set);
    }

    $echo_js = '';
    $echo_js .= 'var material_price = ' . json_encode($material_price, JSON_UNESCAPED_UNICODE) . ';';
    $echo_js .= 'var material_name = ' . json_encode($material_name, JSON_UNESCAPED_UNICODE) . ';';
    $echo_js .= 'var material_list = ' . json_encode($material_list, JSON_UNESCAPED_UNICODE) . ';';
    $echo_js .= 'var order_data = ' . json_encode($order_data, JSON_UNESCAPED_UNICODE) . ';';
    $echo_js .= 'var default_set = ' . json_encode($default_set, JSON_UNESCAPED_UNICODE) . ';';

    echo '<script>' . $echo_js . '</script>'; // js로 데이터 보내기

?>
<section id='pp'>
<h2><?= $title ?></h2>
 <!-- 거래처 선택 -->

<!-- ***************등록**************** -->
<?php
    if($is_update === false){
?>
<select key ='account_key'> 
    <option value="" selected disabled >거래처 선택</option>
    <?php
    for($i=0; $i<$account_list['output_cnt']; $i++){
        ?>
        <option value='<?=$account_list[$i]['sid']?>'><?=$account_list[$i]['account_name']?></option>
        <?php
    }
    ?>
</select>
<?php
    }
?>
<!-- ***************수정**************** -->
<?php
    if($is_update === true){
?>
    <input type='hidden' id='update_material_number' name="update_material_number" value="<?= $update_material_number?>">
    <input type='hidden' id='up_account_sid' name="up_account_sid" value="<?= $up_account_sid?>">
    <h2><?= $update_account?>/<?= $update_date?></h2>
<?php
    }
?>

<button onclick="registration('<?=$api_url?>');"><?=$btn_text?></button>
</section>
<hr>


<div style='clear:both;'></div>

<!-- 목록 출력 영역(헤드) -->
<table id='grid_table' style="width: 900px;">
    <colgroup>
        <col width='200px'>
        <col width='100px'>
        <col width='200px'>
        <col width='100px'>
        <col width='100px'>
    </colgroup>
    <thead>
        <tr>
            <th>자재</th>
            <th>수량</th>
            <th>소계</th>
            <th>비고</th>
        </tr>
    </thead>

    <!-- 목록 바디 영역 -->
    <!-- 수정일 경우 -->
    <?php if($is_update === true) { 
        ?>
         <tbody >
            <?php 
                for($i=0; $i<$order_data_cnt; $i++){   
                    $material_cnt = $order_data[$i]['material_cnt'];
                    $material_price_val = $material_price[$order_data[$i]['material_key']];
                    $amount = $material_cnt*$material_price_val;
                    $material_note = $order_data[$i]['note'];
            ?>
                <tr>
                    <td>
                        <select key='material_key' onchange='is_onchange(this, <?=$i?>);'>
                            <option value='' selected disabled>자재 선택</option>
                       <?php
                        for($j = 0; $j < $material_list_cnt; $j++){
                            $selected = '';
                            if($material_list[$j]['material_key'] === $order_data[$i]['material_key']){
                                $selected = 'selected';
                        }
                        ?>
                        <option value='<?=$material_list[$j]['material_key']?>' <?=$selected?>><?=$material_list[$j]['material_name']?></option>
                    <?php } ?>
                        </select>
                    </td>
                    <td><input type='number' key='material_cnt' placeholder='자재발주수량' value='<?=$material_cnt?>' onchange='is_onchange(this, <?=$i?>);'/></td>

                    <td><div style="float:right"><?= $amount ?></div></td>

                    <td><input type='text' key='note' placeholder='비고' onchange='is_onchange(this, <?=$i?>);' value='<?=$material_note?>' /></td>

                    <td><button onclick='row_add();'>추가</button></td>
                </tr>
    
        <?php }?>
        </tbody>
    <?php }?>

    <!-- 등록일 경우 -->
    <?php if($is_update === false) { 
        ?>
    <tbody>
        <tr>
            <td>
                <select key='material_key' onchange='is_onchange(this, 0);'>
                    <option value='' selected disabled>자재 선택</option>
                    <?php
                    for($i=0; $i<$material_list_cnt; $i++){
                        ?>
                        <option value='<?=$material_list[$i]['material_key']?>'><?=$material_list[$i]['material_name']?></option>
                        <?
                    }
                    ?>
                </select>
            </td>
            <td><input type='number' key='material_cnt' placeholder='자재발주수량' value='0' onchange='is_onchange(this, 0);'/></td>
            <td>0</td>
            <td><input type='text' key='note' placeholder='비고' onchange='is_onchange(this, 0);'/></td>
            <td><button onclick='row_add();'>추가</button></td>
        </tr>
    </tbody>
    <?php }?>
    


</table>

