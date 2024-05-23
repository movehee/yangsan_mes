<?php
	
	//납품 조회 화면 페이지

	define('__CORE_TYPE__', 'view');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	$page = 1;
	if(isset($_POST['page']) === true){
		if($_POST['page'] !== '' && $_POST['page'] !== null){
			$page = $_POST['page'];
		}
	}

	// 입력값 검사
	//거래처
	$is_account_name = '';
	if(isset($_POST['account_name']) === true){
		if($_POST['account_name'] !== ''&& $_POST['account_name'] !== null){
			$is_account_name = $_POST['account_name'];
		}
	}
	//품목명
	$is_product_name = '';
	if(isset($_POST['product_name']) === true){
		if($_POST['product_name'] !== '' && $_POST['product_name'] !== null){
			$is_product_name = $_POST['product_name'];
		}
	}
	//납품수량
	$is_delivery_cnt = '';
	if(isset( $_POST['delivery_cnt']) === true){
		if($_POST['delivery_cnt'] !== '' && $_POST['delivery_cnt'] !== null){
			$is_delivery_cnt = $_POST['delivery_cnt'];
		}
	}
	//납품비고
	$is_delivery_note = '';
	if(isset($_POST['delivery_note']) === true){
		if($_POST['delivery_note'] !== '' && $_POST['delivery_note'] !== null){
			$is_delivery_note = $_POST['delivery_note'];
		}
	}
	//수주시작날짜
	$is_order_start = date('Y-m-d', strtotime('-1 months'));
	if(isset($_POST['order_date_start']) === true){
		if($_POST['order_date_start'] !== '' && $_POST['order_date_start'] !== null){
			$is_order_start = $_POST['order_date_start'];
		}
	}
	//수주끝날짜
	$is_order_end = date('Y-m-d');
	if(isset($_POST['order_date_end']) === true){
		if($_POST['order_date_end'] !== '' && $_POST['order_date_end'] !== null){
			$is_order_end = $_POST['order_date_end'];
		}
	}
	//납품시작날짜
	$is_delivery_date_start = date('Y-m-d', strtotime('-1 months'));
	if(isset($_POST['delivery_date_start']) === true){
		if($_POST['delivery_date_start'] !== '' && $_POST['delivery_date_start'] !== null){
			$is_production_date_start = $_POST['delivery_date_start'];
		}
	}
	//납품끝날짜
	$is_delivery_date_end = date('Y-m-d');
	if(isset($_POST['delivery_date_end']) === true){
		if($_POST['delivery_date_end'] !== '' && $_POST['delivery_date_end'] !== null){
			$is_delivery_date_end = $_POST['delivery_date_end'];
		}
	}

	//************************************************************************************************************
 	//1.기본정보조회(거래처,품목)
 	//1-1.거래처 조회 SQL문 작성
 	$select_account_sql = "SELECT account_name, sid FROM account_info WHERE company_sid ='".__COMPANY_SID__."'";

 	// 거래처 검색어가 있으면 쿼리문에 추가
 	if($is_account_name !== ''){
 		$select_account_sql .= " AND account_name LIKE '%$is_account_name%'";
 	}
 	$select_account_sql .= ';';
 	//데이터베이스에전송
 	$query_result_account = sql($select_account_sql);
 	//조회된 정보들 배열로 저장
 	$query_result_account = select_process($query_result_account);

 	//거래처sid 저장할 배열
 	$account_sid = array();
 	//거래처이름 저장할 배열
 	$account_name = array();
 	for($i=0;$i<$query_result_account['output_cnt'];$i++){
 		//거래처 sid만 저장
 		array_push($account_sid, $query_result_account[$i]['sid']);
 		//거래처sid => 거래처명
 		$account_name[$query_result_account[$i]['sid']] = $query_result_account[$i]['account_name'];
 	}

 	$account_sid_cnt = count($account_sid);
 	//************************************************************************************************************
 	//1-2.품목 조회 SQL문 작성
	$select_product_sql = "SELECT sid, product_name, product_price FROM product_info WHERE company_sid ='".__COMPANY_SID__."'";
	//품목검색어가 있으면 쿼리문에 추가
	if($is_product_name !== ''){
		$select_product_sql .= " AND product_name like '%$is_product_name%'";
	}
	$select_product_sql .= ';';

	$query_result_product = sql($select_product_sql);
	$query_result_product = select_process($query_result_product);

	//품목sid와 name,price 매핑
	$product_sid = array();
	$product_name = array();
	$product_price = array();
	$delivery_note = '';

	for($i=0;$i<$query_result_product['output_cnt'];$i++){
		//품목sid 배열에 푸쉬
		array_push($product_sid,$query_result_product[$i]['sid']);
		//품목sid => 품목명
		$product_name[$query_result_product[$i]['sid']] = $query_result_product[$i]['product_name'];
		//품목sid => 품목가격
		$product_price[$query_result_product[$i]['sid']] = $query_result_product[$i]['product_price'];
	}
 	$product_sid_cnt = count($product_sid);
	//************************************************************************************************************
	//1.수주정보조회(거래처sid in + 품목sid in)
	$select_order_num_sql = "SELECT order_number,sid,account_sid,product_sid,order_date,product_cnt,order_note FROM order_info WHERE company_sid= '".__COMPANY_SID__."'";
	
	//거래처 겸색어가 있다면 account_sid를 in으로 검색
	if($is_account_name !== '' && $account_sid_cnt !== 0 ){           
        $account_in_sql = implode("','", $account_sid);
        $select_order_num_sql .= "AND account_sid IN ('$account_in_sql')";
 	}
 	//품목 겸색어가 있다면 product_sid를 in으로 검색
	if($is_product_name !== '' && $product_sid_cnt !== 0){
        $product_in_sql = implode("','", $product_sid);
        $select_order_num_sql .= "AND product_sid IN ('$product_in_sql')";           
 	}
 	//수주 날짜 선택시 
 	if($is_order_start !== '' && $is_order_end !== ''){
		$select_order_num_sql .= "AND `order_date` BETWEEN '$is_order_start 00:00:01' and '$is_order_end 23:59:59'";
	}
 	$select_order_num_sql .= ';';

 	$query_result_order = sql($select_order_num_sql);
 	$query_result_order = select_process($query_result_order);

	//수주정보 가공(관계형 배열 미리 만들기 -> $total_data[수주넘버][list][수주번호][list])
	$total_data = array();
	$order_sid_num_map = array();
	$pro_sid_map = array();
	

	//수주sid 배열
	$order_sid = array();
	for($i = 0; $i < $query_result_order['output_cnt']; $i++){

		// data[order_number] 없으면 선언
		if(isset($total_data[$query_result_order[$i]['order_number']]) === false){
			$temp = array();
			$temp['order_number'] = $query_result_order[$i]['order_number'];
			$temp['account_name'] = $account_name[$query_result_order[$i]['account_sid']];
			$temp['order_date'] = $query_result_order[$i]['order_date'];
			$temp['total_cnt'] = 0;
			$temp['rowspan'] = 0;
			$temp['list'] = array();

			$total_data[$query_result_order[$i]['order_number']] = $temp;
		}
		
		// data[order_number][list][order_sid] 없으면 선언
		if(isset($total_data[$query_result_order[$i]['order_number']]['list'][$query_result_order[$i]['sid']]) === false){

		$temp = array();
		$temp['product_name'] = $product_name[$query_result_order[$i]['product_sid']];
		$temp['product_price'] = $product_price[$query_result_order[$i]['product_sid']];
		$temp['product_cnt'] = $query_result_order[$i]['product_cnt'];
		$temp['amount'] = (int)$product_price[$query_result_order[$i]['product_sid']]*$query_result_order[$i]['product_cnt']; 
		$temp['order_note'] = $query_result_order[$i]['order_note'];
		$temp['rowspan'] = 0;
		$temp['list'] = array();
		

		$total_data[$query_result_order[$i]['order_number']]['list'][$query_result_order[$i]['sid']] = $temp;
		}
		// order_sid 배열 푸시
		array_push($order_sid, $query_result_order[$i]['sid']);

		// 수주sid => 수주번호
		$order_sid_num_map[$query_result_order[$i]['sid']] = $query_result_order[$i]['order_number'];
		// 수주sid => 품목sid
		$pro_sid_map[$query_result_order[$i]['sid']] = $query_result_order[$i]['product_sid'];
		
	}


//*******************************************************************
// 전체 납품 넘버 조회
	$select_delivery_num_sql = "SELECT delivery_number FROM delivery_info WHERE company_sid= '".__COMPANY_SID__."'";

	//수량이 있을시 검색필터 적용
	if($is_delivery_cnt !== ''){
		$select_delivery_num_sql .= "AND delivery_cnt like '%$is_delivery_cnt%'";
	}
	//비고가 있을시 검색필터 적용
	if($is_delivery_note !== ''){
		$select_delivery_num_sql .= "AND delivery_note like '%$is_delivery_note%'";
	}
	//납품날짜 선택 시
	if($is_delivery_date_start !== '' && $is_delivery_date_end !== ''){
		$select_delivery_num_sql .= "AND `delivery_date` BETWEEN '$is_delivery_date_start 00:00:01' and '$is_delivery_date_end 23:59:59'";
	}
	$select_delivery_num_sql .= " GROUP BY delivery_number ORDER BY delivery_number";

	$total_sql = $select_delivery_num_sql.';';

 	$query_result_delivery_num = sql($total_sql);

 	//납품넘버만 가져온 값
	$delivery_num_arr = select_process($query_result_delivery_num);

	//전체 납품넘버
	$total_delivery_num = array();
	for($i=0; $i<$delivery_num_arr['output_cnt']; $i++){

		array_push($total_delivery_num , $delivery_num_arr[$i]['delivery_number']);
	}

	//내가 선택한 페이지
	$search_start = ($page - 1) * 10; 

	//limit 걸어주기
	$select_delivery_num_sql .= " LIMIT $search_start,10";
	$select_delivery_num_sql .= ";";

	$select_limit_sql = sql($select_delivery_num_sql);

	//페이지 범위설정
	$select_limit_sql = select_process($select_limit_sql);

	$limit_number = array();

	for($i=0; $i<$select_limit_sql['output_cnt']; $i++){

		array_push($limit_number,$select_limit_sql[$i]['delivery_number']);
	}

//************************************************************************************************************	

//limit 조회결과가 있으면
if($select_limit_sql['output_cnt'] > 0){

	//1.납품조회(수주sid in)
	$order_in_sql = implode("','", $order_sid);

	$selct_delivery_sql = "SELECT sid, delivery_cnt, order_sid, delivery_number, delivery_date, delivery_note FROM delivery_info WHERE company_sid = '".__COMPANY_SID__."' AND order_sid IN('$order_in_sql') ";

	$delivery_num_in = implode("','" , $limit_number);

	$selct_delivery_sql .= "AND delivery_number IN ('$delivery_num_in')";

	$selct_delivery_sql .= ';';

	$query_result_delivery = sql($selct_delivery_sql);
	$query_result_delivery = select_process($query_result_delivery);

	$delivery_number = array();
	$delivery_sid = array();

	for($i=0; $i<$query_result_delivery['output_cnt']; $i++){

		//조회 수주sid로 수주 넘버 찾기
		$temp_order_number = $order_sid_num_map[$query_result_delivery[$i]['order_sid']];
		//납품넘버
		$delivery_number = $query_result_delivery[$i]['delivery_number'];

		// data[order_number][list][order_sid][list][delivery_number] 없으면 선언

		if(isset($total_data[$temp_order_number]['list'][$query_result_delivery[$i]['order_sid']]['list'][$query_result_delivery[$i]['delivery_number']]) === false){
			
			$temp = array();
			$temp['delivery_number'] = $query_result_delivery[$i]['delivery_number'];
			$temp['delivery_date'] = $query_result_delivery[$i]['delivery_date'];
			$temp['total_cnt'] = 0;
			$temp['rowspan'] = 0;
			$temp['list'] = array();

			$total_data[$temp_order_number]['list'][$query_result_delivery[$i]['order_sid']]['list'][$query_result_delivery[$i]['delivery_number']] = $temp;
		}
		//data[order_number][list][order_sid][list][delivery_number][list][delivery_sid] 선언

		$temp =array();
		$temp['delivery_sid'] = $query_result_delivery[$i]['sid'];
		$temp['product_name'] = $product_name[$pro_sid_map[$query_result_delivery[$i]['order_sid']]];
		$temp['delivery_cnt'] = $query_result_delivery[$i]['delivery_cnt'];
		$temp['amount'] = (int)$product_price[$pro_sid_map[$query_result_delivery[$i]['order_sid']]]*(int)$query_result_delivery[$i]['delivery_cnt'];
		$temp['delivery_note'] = $query_result_delivery[$i]['delivery_note'];
		$temp['list'] = array();

		$total_data[$temp_order_number]['list'][$query_result_delivery[$i]['order_sid']]['list'][$query_result_delivery[$i]['delivery_number']]['list'][$query_result_delivery[$i]['sid']] = $temp;

		//납품sid 배열에 푸쉬
		array_push($delivery_sid,$query_result_delivery[$i]['sid']);		

		// rowspan 증가
		$total_data[$temp_order_number]['rowspan']++;
     	$total_data[$temp_order_number]['list'][$query_result_delivery[$i]['order_sid']]['rowspan']++;
   	$total_data[$temp_order_number]['list'][$query_result_delivery[$i]['order_sid']]['list'][$query_result_delivery[$i]['delivery_number']]['rowspan']++;
	}

}	


//************************************************************************************************************
//2차 가공(4번의 for문) = 수주, 납품 색인배열 만들기
$total_list = array();
//2차 가공
$total_data_keys = array_keys($total_data);
$total_data_keys_cnt = count($total_data_keys);

if($total_data_keys_cnt > 0){
// 수주번호 루프 부분
for($i=0; $i<$total_data_keys_cnt; $i++){

	// 수주sid 정보가 없는 수주번호 정보 UNSET
	if($total_data[$total_data_keys[$i]]['rowspan'] === 0){
		unset($total_data[$total_data_keys[$i]]);
		continue;
	}
	// order_number 데이터 생성 여부
	$order_number_push = false;

	$order_sid_list = $total_data[$total_data_keys[$i]]['list'];
	$order_sid_keys = array_keys($order_sid_list);
	$order_sid_keys_cnt = count($order_sid_keys);

	// 수주sid 루프 부분
	for($j=0; $j<$order_sid_keys_cnt; $j++){

		// 생산번호 정보가 없는 수주sid 정보 UNSET
		if($order_sid_list[$order_sid_keys[$j]]['rowspan']===0){
			unset($total_data[$total_data_keys[$i]]['list'][$order_sid_keys[$j]]);
			continue;
		}
		// order_sid 데이터 생성 여부
		$order_sid_push = false;

		$delivery_number_list = $order_sid_list[$order_sid_keys[$j]]['list'];
		$delivery_number_keys = array_keys($delivery_number_list);
		$delivery_number_keys_cnt = count($delivery_number_keys);
		// 납품번호 루프 부분
		for($k=0; $k<$delivery_number_keys_cnt; $k++){

			// 납품sid 정보가 없는 납품번호 정보 UNSET
			if($delivery_number_list[$delivery_number_keys[$k]]['rowspan'] === 0){
				unset($total_data[$total_data_keys[$i]]['list'][$order_sid_keys[$j]]['list'][$delivery_number_keys[$k]]);
				continue;
			}
			// 납품번호 데이터 생성 여부
			$delivery_number_push = false;	

			$delivery_sid_list = $delivery_number_list[$delivery_number_keys[$k]]['list'];
			$delivery_sid_keys = array_keys($delivery_sid_list);
			$delivery_sid_keys_cnt = count($delivery_sid_keys);
			// 납품 sid 루프 부분
			for($l=0; $l<$delivery_sid_keys_cnt; $l++){
				// list에 PUSH할 임시 배열
				$temp = array();

				// 수주번호 정보 생성 안되었다면 생성
				if($order_number_push === false){

					$order_number_data = $total_data[$total_data_keys[$i]];
					$temp['order_number'] = $order_number_data;

					// 수주 번호 생성되었음으로 변경
					$order_number_push = true;
				}

				// 수주sid 정보 생성 안되었다면 생성
				if ($order_sid_push === false) {
					
					$order_sid_data = $order_sid_list[$order_sid_keys[$j]];
					$temp['order_sid'] = $order_sid_data;

					// 수주 sid 생성되었음으로 변경
					$order_sid_push = true;
				}

				// 납품번호 정보 생성 안되었다면 생성
				if($delivery_number_push === false){

					$delivery_number_data = $delivery_number_list[$delivery_number_keys[$k]];
					$temp['delivery_number'] = $delivery_number_data;

					// 계획 번호 생성되었음으로 변경
					$delivery_number_push = true;

				}
				
				// 납품sid 정보 생성
				$delivery_sid_data = $delivery_sid_list[$delivery_sid_keys[$l]];
				$temp['delivery_sid'] = $delivery_sid_data;
				// 출력할 색인배열 list에 temp 배열 PUSH
				array_push($total_list, $temp);


				}
			}
		}
	}
} 
$total_list_cnt = count($total_list);

//**********페이지처리**********

	//전체 계획번호 개수
	$plan_delivery_cnt = count($total_delivery_num);

	//페이지 바 개수
	$pagging_cnt = ceil($plan_delivery_cnt / 10);

	//시작 페이지
	$start_page = (floor($page/10)*10)+1;

	//마지막 페이지(시작페이지 + 9)
	$end_page = $start_page + 9;

	// 만약 마지막페이지가 페이지갯수보다 클 경우 마지막페이지 = 페이지 갯수
	if($end_page > $pagging_cnt){
		$end_page = $pagging_cnt;
	}
	

	//**********페이징 화살표**********

	//이전
	$prev = 1;
	// 현재페이지 -1 1보다 작을 때 이전페이지는 1
	if($page - 1 > 0){
		$prev = $page - 1;
	}
	//다음 
	$next = $page + 1;
	// 다음페이지가 총페이지보다 클 경우 다음페이지는 총페이지
	if($next > $pagging_cnt){
		$next = $pagging_cnt;
	}

?>

<h2>납품관리조회</h2>
<head>
   <style>
.search_section {
    display: inline-block;
    vertical-align: top; /* 섹션들을 위쪽으로 정렬 */
}

#searchoption_one,
#searchoption_two,
#searchoption_three,
#searchoption_four {
    width: 20%; /* 각 섹션 너비 조정 */
    margin-right: 2%; /* 오른쪽 여백 조정 */
}

#account_name,
#delivery_cnt,
#product_name,
#delivery_note,
#order_date,
#delivery_date,
#order_date_end,
#delivery_date_end
 {
    width: 140px;
}

.search_field{
   width: 70px;
}

   </style>
</head>
<!--검색어조회-->
<article id='search_area'>
	<section id='searchoption_one' class="search_section">
		<div class='group'>
			<span class='search_field'>거래처</span>
			<input type='text' id='account_name' placeholder="거래처명" autocomplete="off" value='<?= $is_account_name?>' />
		</div>
		<div class='group'>
         <span class='search_field'>납품수량</span>
         <input type='number' id='delivery_cnt' placeholder="납품수량" autocomplete="off" value='<?=$is_delivery_cnt?>' />
      </div>
   </section>
   <section id='searchoption_two' class="search_section">
      <div class='group'>
         <span class='search_field'>품목</span>
         <input type='text' id='product_name' placeholder='품목' autocomplete="off" value='<?=$is_product_name?>' />
      </div>
      <div class='group'>
         <span class='search_field'>비고</span>
         <input type='text' id='delivery_note' placeholder='비고' autocomplete="off" value='<?=$delivery_note?>' />
      </div>
	</section>
	<section id='searchoption_three' class="search_section">
		<div class='group'>
			<span class='search_field'>수주날짜</span>
			<input type='date' id='order_date_start' placeholder="품목명" autocomplete="off" value='<?= $is_order_start?>' />
		</div>
		<div class='group'>
         <span class='search_field'>납품날짜</span>
         <input type='date' id='delivery_date_start' placeholder="비고" autocomplete="off" value='<?=$is_delivery_date_start?>' />
      </div>
   </section>
   <section id="searchoption_four" class="search_section">
      <div class='group'>
         <span class='search_field'>~~</span>
         <input type='date' id='order_date_end'  value='<?=$is_order_end?>' />
      </div>
      <div class='group'>
         <span class='search_field'>~~</span>
         <input type='date' id='delivery_date_end'  value='<?=$is_delivery_date_end?>' />
      </div>
	</section>	
</article>

<div style='clear:both;'></div>
<hr>

<!-- 버튼 모음 -->
<div class='btn_area'>
	<button onclick='search();'>조회</button>
	<button onclick='render("sales_management/delivery_registration");'>등록</button>
	<button onclick="resetSearchFields()">검색 조건 초기화</button>
</div>

<div style='clear:both;'></div>
<section id='table_area'>
    <table id='grid_table' border="1">
        <!-- <colgroup>
            <col width='50px' />
            <col width='80px' />
            <col width='100px' />
            <col width='100px' />
            <col width='100px' />
            <col width='120px' />
            <col width='150px' />
            <col width='200px' />
            <col width='55px' />
            <col width='55px' />
            <col />
        </colgroup> -->
      
<!-- 목록 출력 영역(헤드) -->
        <thead>
            <tr>
				<th>수주번호</th>
				<th>거래처명</th>
				<th>수주날짜</th>
				<th>상품명</th>
				<th>상품가격</th>
				<th>수주수량</th>
				<th>수주소계</th>
				<th>수주비고</th>
				<th>납품번호</th>
				<th>납품날짜</th>
				<th>납품품목</th>
				<th>납품수량</th>
				<th>납품소계</th>
				<th>납품비고</th>
            </tr>
        </thead>
        <!-- 목록 바디 영역 -->
           <tbody>
            <?php
            for($i=0; $i<$total_list_cnt; $i++){
                ?>
                <tr>
                    <?php
                    $row = $total_list[$i];

                    // 수주번호 정보 있다면 출력
                    if(isset($row['order_number']) === true){
                        ?>
                        <td rowspan='<?=$row['order_number']['rowspan']?>'><?=$row['order_number']['order_number']?></td>
                        <td rowspan='<?=$row['order_number']['rowspan']?>'><?=$row['order_number']['account_name']?></td>
                        <td rowspan='<?=$row['order_number']['rowspan']?>'><?=$row['order_number']['order_date']?></td>
                        <?php
                    }

                    // 수주sid 정보 있다면 출력
                    if(isset($row['order_sid']) === true){
                        ?>
                        <td rowspan='<?=$row['order_sid']['rowspan']?>'><?=$row['order_sid']['product_name']?></td>
                        <td rowspan='<?=$row['order_sid']['rowspan']?>'><?=number_format($row['order_sid']['product_price'])?></td>
                        <td rowspan='<?=$row['order_sid']['rowspan']?>'><?=number_format($row['order_sid']['product_cnt'])?></td>
                        <td rowspan='<?=$row['order_sid']['rowspan']?>'><?=number_format($row['order_sid']['amount'])?></td>
                        <td rowspan='<?=$row['order_sid']['rowspan']?>'><?=$row['order_sid']['order_note']?></td>
                        <?php
                    }

                    // 납품번호 정보 있다면 출력
                    if(isset($row['delivery_number']) === true){
                        ?>
                        <td rowspan='<?=$row['delivery_number']['rowspan']?>'><?=$row['delivery_number']['delivery_number']?></td>
                        <td rowspan='<?=$row['delivery_number']['rowspan']?>'><?=$row['delivery_number']['delivery_date']?></td>
                        <?php
                    }

                    // 납품sid 정보 출력
                    ?>
                    <td><?=$row['delivery_sid']['product_name']?></td>
                    <td><?=number_format($row['delivery_sid']['delivery_cnt'])?></td>
                    <td><?=number_format($row['delivery_sid']['amount'])?></td>
                    <td><?=$row['delivery_sid']['delivery_note']?></td>     			
                </tr>
                <?php } ?>
        </tbody>
    </table>

    <ul id='pagging'>
		<li onclick='search(<?=$prev?>);'>이전</li>
		<?php

		for($i=$start_page; $i<=$end_page; $i++){
			?>
			<li <?php if((int)$i === (int)$page){ echo 'id="this_page"'; } ?> onclick='search(<?=$i?>);'><?=$i?></li>
			<?php
		}
		?>
		<li onclick='search(<?=$next?>);'>다음</li>
	</ul>


</section>