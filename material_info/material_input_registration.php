<?php

// 자재 입고 등록/수정 페이지

define('__CORE_TYPE__', 'view');
include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

// 발주시작범위
$startdate = date('Y-m-d', strtotime('-1 months'));
if (isset($_POST['startdate']) === true) {
    $startdate = $_POST['startdate'];
    if ($startdate === '') {
        $startdate = false;
    }
}

// 발주종료범위
$enddate = date('Y-m-d');
if (isset($_POST['enddate']) === true) {
    $enddate = $_POST['enddate'];
    if ($enddate === '') {
        $enddate = false;
    }
}

// 자재 입고 기본값
$material_order_number = '';
$parent_sid = '';
$material_cnt = 0;
$note = '';

// 거래처 조회 sql
$account_sql = "SELECT sid, account_name FROM account_info WHERE company_sid = '".__COMPANY_SID__."';";
$query_result = sql($account_sql);
$query_result = select_process($query_result);

// 거래처명 매핑
$account_name = array();
for ($i = 0; $i < $query_result['output_cnt']; $i++) {
    $account_name[$query_result[$i]['sid']] = $query_result[$i]['account_name'];
}

// 자재 조회 sql
$material_sql = "SELECT sid, material_name, material_price FROM material_info WHERE company_sid = '".__COMPANY_SID__."' ORDER BY material_name ASC;";
$query_result = sql($material_sql);
$query_result = select_process($query_result);

// 자재 자료 매핑
$material_name = array();
$material_price = array();
for ($i = 0; $i < $query_result['output_cnt']; $i++) {
    // 자재명 매핑
    $material_name[$query_result[$i]['sid']] = $query_result[$i]['material_name'];
    // 자재 가격 매핑
    $material_price[$query_result[$i]['sid']] = $query_result[$i]['material_price'];
}

// 자재 발주 sql
$material_order_sql = "SELECT sid, account_sid, material_sid, material_order_date, material_order_cnt, material_order_number, material_order_note FROM material_order_info WHERE company_sid = '".__COMPANY_SID__."' AND `material_order_date` BETWEEN '$startdate 00:00:01' AND '$enddate 23:59:59';";

$query_result = sql($material_order_sql);
$query_result = select_process($query_result);

$order_data = array();
for ($i = 0; $i < $query_result['output_cnt']; $i++) {
    $temp = array();
    $temp['order_number'] = $query_result[$i]['material_order_number'];
    $temp['account_name'] = $account_name[$query_result[$i]['account_sid']];
    $temp['material_order_sid'] = $query_result[$i]['sid'];
    $temp['material_price'] = $material_price[$query_result[$i]['material_sid']];
    $temp['change_cnt'] = $query_result[$i]['material_order_cnt'];
    $temp['amount'] = $material_price[$query_result[$i]['material_sid']] * $query_result[$i]['material_order_cnt'];
    $temp['note'] = $query_result[$i]['material_order_note'];
    $temp['material_key'] = $query_result[$i]['material_sid'];
    $temp['material_name'] = $material_name[$query_result[$i]['material_sid']];
    array_push($order_data, $temp);
}

// js로 자재 발주 정보 넘기기
echo '<script> var order_data = '.json_encode($order_data).';</script>';

?>
<section id='pp'>
<h2>자재 입고 등록</h2>

<!-- 검색어 조회 -->
<article id='search_area'>

   <section id='searchoption_left'>

       <div class='group'>
         <span class='search_field'>발주일 </span>
         <input type='date' id='startdate' autocomplete="off" value='<?=$startdate?>' />
      </div>
      
   </section>       

   <section id='searchoption_right'>
      
      <div class='group'>
         <span class='search_field'> ~~ </span>
         <input type='date' id='enddate' autocomplete="off" value='<?=$enddate?>'>
      </div>

   </section>
</article>

<!-- 버튼 모음 -->
<div style="margin: 20px;">
<button style="margin-top: 10px;" onclick='search();'>조회</button>
</div>

<div style='clear:both;'></div>

<select key='order_number' id='order_number' name='order_number' onchange="select_order(this)">
    <option value='' selected disabled>발주 선택</option>
</section>
    <?php
    $unique_order = array();
    foreach ($order_data as $order) {
        
        $order_number = $order['order_number'];
        $account_name = $order['account_name'];
        $unique_order[$order_number] = $account_name; // 발주번호를 키로, 거래처명을 값으로 하는 배열 생성
    }
    foreach ($unique_order as $order_number => $account_name){
    ?>
    <option value="<?= $order_number?>">
        발주번호: <?=$order_number?> 거래처명: <?=$account_name?>
    </option>
    <?php
    }
    ?>
</select>


<button onclick="registration();">등록</button>


<hr />

<table id='grid_table' style="width: 1200px;" >
    <colgroup>
        <col width='200px'>
        <col width='100px'>
        <col width='300px'>
        <col width=''>
        <col width='100px'>
    </colgroup>
    <thead>
        <tr>
            <th>자재</th>
            <th>가격</th>
            <th>수량</th>
            <th>소계</th>
            <th>비고</th>
            <th style='display:none;'></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <select id='material_order_sid' key='material_order_sid' onchange="is_onchange(this,0);">
                    <option value='' selected disabled>자재 선택</option>
                </select>
            </td>
            <td>
                <input key="material_price" id="material_price" type="number" onchange="is_onchange(this,0);" disabled value='0'>
            </td>
            <td>
                <input type='number' key='change_cnt' onchange="is_onchange(this,0);" placeholder='자재입고수량' value=''/>
            </td>
            <td>
                <div style="float:right">0</div>
            </td>
            <td>
                <input type='text' key='note' placeholder='비고' onchange="is_onchange(this,0);" value=''/>
            </td>
            <td>
                <button onclick='add_row();'>추가</button>
            </td>
        </tr>
    </tbody>
</table>