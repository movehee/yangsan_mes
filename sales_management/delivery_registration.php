<?php

	// 납품 등록 페이지

	define('__CORE_TYPE__', 'view');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//수주시작 날짜
	$startdate = date('Y-m-01 00:00:00');
	//수주 끝날짜
	$enddate = date('Y-m-d 23:59:59');

	//수주 테이블 조회 (조건 :날짜)
$sql = "SELECT order_number, MAX(order_date) AS order_date, MAX(account_sid) AS account_sid 
        FROM order_info 
        WHERE company_sid='".__COMPANY_SID__."' 
            AND order_date BETWEEN '$startdate' AND '$enddate' 
        GROUP BY order_number 
        ORDER BY order_number DESC;";

	$query_result = sql($sql);
	$order_db = select_process($query_result);

	//거래처 조회
	$sql = "SELECT sid, account_name FROM account_info WHERE company_sid='".__COMPANY_SID__."';";
	$query_result = sql($sql);
	$account_db = select_process($query_result);

	$account_name = array();
	for($i=$account_db['output_cnt']-1; $i>=0; $i--){
		//거래처 sid => 거래처명
		$account_name[$account_db[$i]['sid']] = $account_db[$i]['account_name'];
	}

	$order_list = array();

	for($i=0; $i<$order_db['output_cnt']; $i++){

		$temp = array();
		$temp['order_number'] = $order_db[$i]['order_number'];
		$temp['order_date'] = explode(' ', $order_db[$i]['order_date'])[0];
		$temp['account_name'] = $account_name[$order_db[$i]['account_sid']];

		array_push($order_list, $temp);
	}
	$order_list_cnt = count($order_list);

	$startdate = explode(' ', $startdate)[0];
	$enddate = explode(' ', $enddate)[0];
?>
<section id="pp">
<h2>납품등록</h2>

<div>
	<span>날짜 범위</span>
	<input type='date' id='startdate' value='<?=$startdate?>' />
	~
	<input type='date' id='enddate' value='<?=$enddate?>' />
	<button onclick='search_order();'>수주 검색</button>
</div>

<select id='order_list' onchange='select_order(this);'>
	<option value='' selected disabled>수주 선택</option>
	<?php
	for($i=0; $i<$order_list_cnt; $i++){
		?>
		<option value='<?=$order_list[$i]['order_number']?>'>
		수주번호 : <?=$order_list[$i]['order_number']?> / 수주날짜 : <?=$order_list[$i]['order_date']?> / 거래처 : <?=$order_list[$i]['account_name']?>
		</option>
		<?php
	}
	?>
</select>

<button onclick='registration();'>등록</button>
</section>
<hr />

<table id='grid_table'>
	<colgroup>
		<col width='200px' />
		<col width='100px' />
		<col width='200px' />
		<col />
		<col width='100px' />
	</colgroup>
	<thead>
		<tr>
			<th>품목</th>
			<th>수량</th>
			<th>소계</th>
			<th>비고</th>
			<th style='border:none;'></th>
		</tr>
	</thead>
	<tdboy>
		<tr>
			<td>
				<select key='order_sid'>
					<option value='' selected disabled>품목 선택</option>
				</select>
			</td>
			<td>
				<input type='number' key='delivery_cnt' value='0'/>
			</td>
			<td>0</td>
			<td>
				<input type='text' key='note'/>
			</td>
			<td>
				<button onclick='row_add();'>추가</button>
			</td>
		</tr>
	</tdboy>
</table>