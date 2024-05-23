<?php

// 생산등록 등록 페이지 

define('__CORE_TYPE__', 'view');
include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

// 생산계획시작범위
$startdate = date('Y-m-d', strtotime('-1 months'));
if (isset($_POST['startdate']) && $_POST['startdate'] !== '') {
    $startdate = $_POST['startdate'];
}

// 생산계획종료범위
$enddate = date('Y-m-d');
if (isset($_POST['enddate']) && $_POST['enddate'] !== '') {
    $enddate = $_POST['enddate'];
}

// 거래처 조회 sql
$account_sql = "SELECT sid, account_name FROM account_info WHERE company_sid = '".__COMPANY_SID__."';";
$query_result = sql($account_sql);
$query_result = select_process($query_result);

// 거래처명 매핑
$account_name = array();
for ($i = 0; $i < $query_result['output_cnt']; $i++) {
    $account_name[$query_result[$i]['sid']] = $query_result[$i]['account_name'];
}

// 수주테이블에서 거래처 들고오기
$select_order_sql = "SELECT sid, account_sid FROM order_info WHERE company_sid = '".__COMPANY_SID__."';";

$query_result = sql($select_order_sql);
$query_result = select_process($query_result);

// order_sid -> 거래처명
$sid_account_map = array();

for ($i = 0; $i < $query_result['output_cnt']; $i++) {
    // 존재하는 거래처sid일 시
    if (isset($account_name[$query_result[$i]['account_sid']])) {
        // 수주sid => 거래처 명
        $sid_account_map[$query_result[$i]['sid']] = $account_name[$query_result[$i]['account_sid']];
    }
}

echo '<script> var sid_account_map = ' . json_encode($sid_account_map) . ';</script>';

// 계획테이블에서 거래처 들고오기
$select_plan_sql = "
    SELECT plan_info.sid, plan_info.plan_number, plan_info.order_sid, plan_info.plan_date
    FROM plan_info
    INNER JOIN order_info ON plan_info.order_sid = order_info.sid
    WHERE plan_info.company_sid = '".__COMPANY_SID__."'
    AND plan_info.plan_date BETWEEN '$startdate 00:00:01' AND '$enddate 23:59:59'
    GROUP BY plan_info.plan_number, plan_info.sid, plan_info.order_sid, plan_info.plan_date
    ORDER BY plan_info.sid DESC;
";

$query_result = sql($select_plan_sql);
$query_result = select_process($query_result);

$plan_data = array();
$order_sid = array();

// 생산계획 선택 정보 가공
for ($i = 0; $i < $query_result['output_cnt']; $i++) {
    array_push($order_sid, $query_result[$i]['order_sid']);

    // 정보 가공
    $temp = array();
    $temp['order_sid'] = $query_result[$i]['order_sid'];
    $temp['account_name'] = '삭제된 거래처';
    if (isset($sid_account_map[$query_result[$i]['order_sid']])) {
        $temp['account_name'] = $sid_account_map[$query_result[$i]['order_sid']];
    }
    $temp['plan_number'] = $query_result[$i]['plan_number'];
    $temp['plan_date'] = $query_result[$i]['plan_date'];

    array_push($plan_data, $temp);
}

$plan_data_cnt = count($plan_data);
echo '<script> var plan_data = ' . json_encode($plan_data) . ';</script>';

?>

<h2>생산등록</h2>

<!-- 검색어 조회 -->
<article id='search_area'>

   <section id='searchoption_left'>
       <div class='group'>
         <span class='search_field'> 날짜조회 </span>
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

<!-- *********수주선택******* -->
<select key="plan_number" id="plan_number" name="plan_number" onchange="select_order(this)">
    <option value="" selected disabled>생산계획 선택</option>
    <?php for ($i = 0; $i < $plan_data_cnt; $i++) { ?>
        <option value="<?=$plan_data[$i]['plan_number']?>">
            계획번호 : <?=$plan_data[$i]['plan_number']?>
            거래처명 : <?=$plan_data[$i]['account_name']?>
            계획일자 : <?=$plan_data[$i]['plan_date']?>
        </option>
    <?php } ?>
</select>

<button onclick="registration('api_production_registration');">생산등록</button>
<hr>

<table id="grid_table">
    <colgroup>
        <col width='200px' />
        <col width='100px' />
        <col width="100px" />
        <col width="100px" />
        <col width="100px" />
        <col width="100px" />
    </colgroup>
    <!-- 목록 출력 영역(헤드) -->
    <thead>
        <tr>
            <th>품목명</th>
            <th>품목가격</th>
            <th>생산계획수량</th>
            <th>소계금액</th>
            <th>비고</th>
            <th style='display:none;'></th>
        </tr>
    </thead>

    <!-- 목록 바디 영역 -->
    <tbody>
        <tr>
            <td>
                <select id="plan_key" key="plan_key" onchange="is_onchange(this,0);">
                    <option value="" selected disabled>품목선택</option>
                </select>
            </td>
            <td>
                <input key='product_price' type='number' onchange="is_onchange(this,0);" value=''/>
            </td>
            <td>
                <input key='production_cnt' type='number' onclick="select_order()" onchange="is_onchange(this,0);" value=''/>
            </td>
            <td>
                <div style="float:right">0</div>
            </td>
            <td>
                <input key='note' name="note" type='text' onchange="is_onchange(this,0);" value=''/>
            </td>
            <td>
                <button onclick='add_row();'>추가</button>
            </td>
        </tr>
    </tbody>
</table>
