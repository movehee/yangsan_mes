<?php

	//생산등록 페이지

	define('__CORE_TYPE__', 'view');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//유효성 검사(페이지,거래처명,품목명,생산수량,생산비고,시작날짜,마지막날짜)
	//페이지
	$page = 1;
	if(isset($_POST['page']) === true){
		if($_POST['page'] !== '' && $_POST['page'] !== null){
			$page = $_POST['page'];
		}
	}

	//거래처
	$is_account_name = '';
	if(isset($_POST['account_name']) === true){
		if($_POST['account_name'] !== ''&& $_POST['account_name'] !== null){
			$is_account_name = $_POST['account_name'];
		}
	}
	//생산수량
	$is_production_cnt = '';
	if(isset( $_POST['production_cnt']) === true){
		if($_POST['production_cnt'] !== '' && $_POST['production_cnt'] !== null){
			$is_production_cnt = $_POST['production_cnt'];
		}
	}
	//품목명
	$is_product_name = '';
	if(isset($_POST['product_name']) === true){
		if($_POST['product_name'] !== '' && $_POST['product_name'] !== null){
			$is_product_name = $_POST['product_name'];
		}
	}
	// 생산등록비고
	$is_production_note = '';
	if (isset($_POST['production_note']) && $_POST['production_note'] !== '' && $_POST['production_note'] !== null) {
		$is_production_note = $_POST['production_note'];
	}
	//생산시작날짜
	$is_plan_date_start = date('Y-m-d', strtotime('-1 months'));
	if(isset($_POST['plan_date_start']) === true){
		if($_POST['plan_date_start'] !== '' && $_POST['plan_date_start'] !== null){
			$is_plan_date_start = $_POST['plan_date_start'];
		}
	}
	//생산끝날짜
	$is_plan_date_end = date('Y-m-d');
	if(isset($_POST['plan_date_end']) === true){
		if($_POST['plan_date_end'] !== '' && $_POST['plan_date_end'] !== null){
			$is_plan_date_end = $_POST['plan_date_end'];
		}
	}
	//생산등록시작날짜
	$is_production_date_start = date('Y-m-d', strtotime('-1 months'));
	if(isset($_POST['production_date_start']) === true){
		if($_POST['production_date_start'] !== '' && $_POST['production_date_start'] !== null){
			$is_production_date_start = $_POST['production_date_start'];
		}
	}
	//생산등록끝날짜
	$is_production_date_end = date('Y-m-d');
	if(isset($_POST['production_date_end']) === true){
		if($_POST['production_date_end'] !== '' && $_POST['production_date_end'] !== null){
			$is_production_date_end = $_POST['production_date_end'];
		}
	}

	//************************************************************************************************************
 	//1.기본정보조회(거래처,품목)
 	//1-1.거래처 조회 SQL문 작성
 	$select_account_sql = "SELECT account_name, sid FROM account_info WHERE company_sid ='".__COMPANY_SID__."'";

 	//거래처 검색어가 있으면 쿼리문에 추가
 	if($is_account_name !== ''){
 		$select_account_sql .= " AND account_name LIKE '%$is_account_name%'";
 	}
 	$select_account_sql .= ';';
 	$query_result_account = sql($select_account_sql);
 	$query_result_account = select_process($query_result_account);

 	$account_sid = array();
 	$account_name = array();

 	for($i=0;$i<$query_result_account['output_cnt'];$i++){
 		//거래처sid 배열 push
 		array_push($account_sid, $query_result_account[$i]['sid']);
 		//거래처sid => 거래처명 mapping
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
	$production_note = '';

	for($i=0;$i<$query_result_product['output_cnt'];$i++){

		//품목sid 배열에 푸쉬
		array_push($product_sid,$query_result_product[$i]['sid']);
		//품목sid => 품목명 맵핑
		$product_name[$query_result_product[$i]['sid']] = $query_result_product[$i]['product_name'];
		//품목sid => 품목가격 맵핑
		$product_price[$query_result_product[$i]['sid']] = $query_result_product[$i]['product_price'];
	}
 	$product_sid_cnt = count($product_sid);
	//************************************************************************************************************
	//1-3.수주테이블조회
	$select_order_sql = "SELECT sid, product_sid, account_sid FROM order_info  WHERE company_sid ='".__COMPANY_SID__."';";
	
	$query_result_order = sql($select_order_sql);
	$query_result_order = select_process($query_result_order);

	$order_sid = array();
	$order_sid_product_sid_map = array();
	$order_sid_account_sid_map = array();
	$account_sid_map = array();

	for($i=0;$i<$query_result_order['output_cnt'];$i++){
		array_push($order_sid,$query_result_order[$i]['sid']);

		//키 : 거래처sid => 수주sid 맵핑
		$account_sid_map[$query_result_order[$i]['account_sid']] = $query_result_order[$i]['sid'];

		//키 : 수주sid => 품목sid 맵핑
		$order_sid_product_sid_map[$query_result_order[$i]['sid']] = $query_result_order[$i]['product_sid'];

		//키 : 수주sid => 거래처sid 맵핑
		$order_sid_account_sid_map[$query_result_order[$i]['sid']] = $query_result_order[$i]['account_sid'];
	}   


	//===========================================================================
	//2.plan조회
	$select_plan_sql = "SELECT sid, plan_number, plan_date, plan_cnt, order_sid, plan_note FROM plan_info WHERE company_sid ='".__COMPANY_SID__."'";

	//생산계획날짜 선택시 쿼리 추가
	if($is_plan_date_start !== '' && $is_plan_date_end !== ''){
		$select_plan_sql .= "AND `plan_date` BETWEEN '$is_plan_date_start 00:00:01' and '$is_plan_date_end 23:59:59'";
	}

	$select_plan_sql .= ';';

	$query_result_plan = sql($select_plan_sql);
	$query_result_plan = select_process($query_result_plan); 

	$plan_num_to_account_name = array();
	$plan_sid_to_product_name = array();
	$plan_sid_to_product_price = array();

	for($i=0;$i<$query_result_plan['output_cnt'];$i++){

		//키 : plan_number => 값 : 거래처명 맵핑
		$plan_num_to_account_name[$query_result_plan[$i]['plan_number']] = $account_name[$order_sid_account_sid_map[$query_result_plan[$i]['order_sid']]];

		//키 : plan_sid => 값 : 품목명 맵핑
		$plan_sid_to_product_name[$query_result_plan[$i]['sid']] = $product_name[$order_sid_product_sid_map[$query_result_plan[$i]['order_sid']]];

		//키 : plan_sid => 값 : 품목가격 맵핑
		$plan_sid_to_product_price[$query_result_plan[$i]['sid']] = $product_price[$order_sid_product_sid_map[$query_result_plan[$i]['order_sid']]];	
	}

	$total_data = array();
	$plan_number = array();
	$plan_sid = array();
	$plan_sid_to_plan_num = array();

	for($i=0;$i<$query_result_plan['output_cnt'];$i++){
		//data[plan_number]없을시 선언	
		if(isset($total_data[$query_result_plan[$i]['plan_number']]) === false){

			//생산계획 번호 배열에 푸쉬
			array_push($plan_number,$query_result_plan[$i]['plan_number']);
			//생산계획 sid 배열에 푸쉬
			array_push($plan_sid,$query_result_plan[$i]['sid']);

			$temp = array();
			$temp['plan_number'] = $query_result_plan[$i]['plan_number'];
			$temp['account_name'] = $plan_num_to_account_name[$query_result_plan[$i]['plan_number']];
			$temp['plan_date'] = $query_result_plan[$i]['plan_date'];
			$temp['rowspan'] = 0;
			$temp['list'] = array();

			$total_data[$query_result_plan[$i]['plan_number']] = $temp; 
		}

		//total_data[plan_num][list][plan_sid]없을시 선언
		if(isset($total_data[$query_result_plan[$i]['plan_number']]['list'][$query_result_plan[$i]['sid']]) === false){
		
			$temp = array();
			$temp['sid'] = $query_result_plan[$i]['sid'];
			$temp['product_name'] = $plan_sid_to_product_name[$query_result_plan[$i]['sid']];
			$temp['product_price'] = $plan_sid_to_product_price[$query_result_plan[$i]['sid']];
			$temp['plan_cnt'] = $query_result_plan[$i]['plan_cnt'];
			$temp['plan_note'] = $query_result_plan[$i]['plan_note'];
			$temp['rowspan'] = 0;
			$temp['list'] = array();

			$total_data[$query_result_plan[$i]['plan_number']]['list'][$query_result_plan[$i]['sid']] = $temp;
		}

		array_push($plan_sid,$query_result_plan[$i]['sid']);

		//plan_number 매핑 => key : plan_sid
		$plan_sid_to_plan_num[$query_result_plan[$i]['sid']] = $query_result_plan[$i]['plan_number'];
	}

	//======================================================================
	//생산등록번호조회(페이징처리를위해서)
	$select_production_num_sql = "SELECT production_number FROM production_info WHERE company_sid= '".__COMPANY_SID__."'";

	//수량이 있을시 검색필터 적용
	if($is_production_cnt !== ''){
		$select_production_num_sql .= "AND production_cnt like '%$is_production_cnt%'";
	}
	//비고가 있을 경우
	if($is_production_note !== ''){
		$select_production_num_sql .= "AND production_note like '%$is_production_note%'";
	}
	//생산등록 날짜가 있을시 검색 필터 적용
   if($is_production_date_start !== '' && $is_production_date_end !== ''){
		$select_production_num_sql .= "AND `production_date` BETWEEN '$is_production_date_start 00:00:01' and '$is_production_date_end 23:59:59'";
	}

   $select_production_num_sql .= " GROUP BY production_number ORDER BY production_number";

   $total_sql = $select_production_num_sql.';';

   $query_result_pro = sql($total_sql);

   $pro_num_arr = select_process($query_result_pro);

   //전체 생산등록 번호 
   $total_pro_num = array();
   for($i=0;$i<$pro_num_arr['output_cnt'];$i++){

   	array_push($total_pro_num,$pro_num_arr[$i]['production_number']);
   }

   //내가선택한페이지
   //한 페이지에 10개씩 나오도록 정렬하기위해
   //리밋을 10까지 걸어서 select문에 추가
   $search_start = ($page - 1)*10;

   //limit 추가
   $select_production_num_sql .= " LIMIT $search_start,10";
   $select_production_num_sql .= ';';

   $limit_sql = sql($select_production_num_sql);
   $limit_sql = select_process($limit_sql);

   $limit_number = array();
   for($i=0;$i<$limit_sql['output_cnt'];$i++){
   	array_push($limit_number,$limit_sql[$i]['production_number']);
   }


   if($limit_sql['output_cnt'] > 0){

   	//생산계획sid
   	$plan_in_sql = implode("','", $plan_sid);

   	$select_production_sql = "SELECT sid, plan_sid, product_sid, production_number, production_cnt, production_note, production_date FROM production_info WHERE company_sid = '".__COMPANY_SID__."' AND plan_sid IN ('$plan_in_sql')";

   	$pro_num_in = implode("','",$limit_number);

   	$select_production_sql .= "AND production_number IN ('$pro_num_in')";
   	$select_production_sql .= ';';

   	$query_result_pro = sql($select_production_sql);
   	$query_result_pro = select_process($query_result_pro);

   	$pro_num = array();
   	$pro_sid = array();

   	for($i=0;$i<$query_result_pro['output_cnt'];$i++){

   		$temp_plan_number = $plan_sid_to_plan_num[$query_result_pro[$i]['plan_sid']];
   		$pro_num = $query_result_pro[$i]['production_number'];

   		//data[plan_number][list][plan_sid][list][production_number]없을시
   		if(isset($total_data[$temp_plan_number]['list'][$query_result_pro[$i]['plan_sid']]['list'][$query_result_pro[$i]['production_number']]) === false){

   			$temp = array();
   			$temp['production_number'] = $query_result_pro[$i]['production_number'];
   			$temp['production_date'] = $query_result_pro[$i]['production_date'];
   			$temp['rowspan'] = 0;
   			$temp['list'] = array();

   			$total_data[$temp_plan_number]['list'][$query_result_pro[$i]['plan_sid']]['list'][$query_result_pro[$i]['production_number']] = $temp;
   		}

   		// data[plan_number][list][plan_sid][list][production_number][list][production_sid]없을시 선언
			$temp = array();
			$temp['production_sid'] = $query_result_pro[$i]['sid'];
			$temp['product_name'] = $product_name[$query_result_pro[$i]['product_sid']]; 
			$temp['production_note'] = $query_result_pro[$i]['production_note'];
			$temp['production_cnt'] = $query_result_pro[$i]['production_cnt'];
			$temp['production_price'] = $product_price[$query_result_pro[$i]['product_sid']]; 
			$temp['amount'] = (int)$product_price[$query_result_pro[$i]['product_sid']]*(int)$query_result_pro[$i]['production_cnt'];
			

			$total_data[$temp_plan_number]['list'][$query_result_pro[$i]['plan_sid']]['list'][$query_result_pro[$i]['production_number']]['list'][$query_result_pro[$i]['sid']] = $temp;


			// rowspan 증가
			$total_data[$temp_plan_number]['rowspan']++;
	     	$total_data[$temp_plan_number]['list'][$query_result_pro[$i]['plan_sid']]['rowspan']++;
      	$total_data[$temp_plan_number]['list'][$query_result_pro[$i]['plan_sid']]['list'][$query_result_pro[$i]['production_number']]['rowspan']++;

		}

   }


  	//2차 가공(4번의 for문) = 생산계획, 생산등록 색인배열 만들기
	$total_list = array();
	//2차 가공
	$total_data_keys = array_keys($total_data);
	$total_data_keys_cnt = count($total_data_keys);

	if($total_data_keys_cnt > 0){
	// 생산계획번호 루프 부분
	for($i=0; $i<$total_data_keys_cnt; $i++){

		// 생산계획sid 정보가 없는 생산계획번호 정보 UNSET
		if($total_data[$total_data_keys[$i]]['rowspan'] === 0){
			unset($total_data[$total_data_keys[$i]]);
			continue;
		}
		//  생산계획번호 데이터 생성 여부
		$plan_number_push = false;

		$plan_sid_list = $total_data[$total_data_keys[$i]]['list'];
		$plan_sid_keys = array_keys($plan_sid_list);
		$plan_sid_keys_cnt = count($plan_sid_keys);

		// 생산계획sid 루프 부분
		for($j=0; $j<$plan_sid_keys_cnt; $j++){

			// 생산등록번호 정보가 없는 생산계획sid 정보 UNSET
			if($plan_sid_list[$plan_sid_keys[$j]]['rowspan']===0){
				unset($total_data[$total_data_keys[$i]]['list'][$plan_sid_keys[$j]]);
				continue;
			}
			// 생산계획sid 데이터 생성 여부
			$plan_sid_push = false;

			$production_number_list = $plan_sid_list[$plan_sid_keys[$j]]['list'];
			$production_number_keys = array_keys($production_number_list);
			$production_number_keys_cnt = count($production_number_keys);

			// 생산등록번호 루프 부분
			for($k=0; $k<$production_number_keys_cnt; $k++){

				// 생산등록sid 정보가 없는 생산등록번호 정보 UNSET
				if($production_number_list[$production_number_keys[$k]]['rowspan'] === 0){
					unset($total_data[$total_data_keys[$i]]['list'][$plan_sid_list[$j]]['list'][$production_number_list[$k]]);
					continue;
				}
				// 생산등록번호 데이터 생성 여부
				$production_number_push = false;	

				$production_sid_list = $production_number_list[$production_number_keys[$k]]['list'];
				$production_sid_keys = array_keys($production_sid_list);
				$production_sid_keys_cnt = count($production_sid_keys);

				// 생산등록 sid 루프 부분
				for($l=0; $l<$production_sid_keys_cnt; $l++){

					// list에 PUSH할 임시 배열
					$temp = array();

					// 생산계획번호 정보 생성 안되었다면 생성
					if($plan_number_push === false){

						$plan_number_data = $total_data[$total_data_keys[$i]];
						$temp['plan_number'] = $plan_number_data;

						// 생산계획번호 정보 생성되었음으로 변경
						$plan_number_push = true;
					}

					// 생산계획sid 정보 생성 안되었다면 생성
					if ($plan_sid_push === false) {
						
						$plan_sid_data = $plan_sid_list[$plan_sid_keys[$j]];
						$temp['plan_sid'] = $plan_sid_data;

						// 생산계획sid 생성되었음으로 변경
						$plan_sid_push = true;
					}

					// 생산등록번호 정보 생성 안되었다면 생성
					if($production_number_push === false){

						$production_number_data = $production_number_list[$production_number_keys[$k]];
						$temp['production_number'] = $production_number_data;

						// 생산등록 번호 생성되었음으로 변경
						$production_number_push = true;

					}
					// 생산등록sid 정보 생성
					$production_sid_data = $production_sid_list[$production_sid_keys[$l]];
					$temp['production_sid'] = $production_sid_data;
					// 출력할 색인배열 list에 temp 배열 PUSH
					array_push($total_list, $temp);

					}
				}
			}
		}
	} 
 
$total_list_cnt = count($total_list);


//========================================================================
//페이징처리

	//전체생산번호
	$pro_cnt = count($total_pro_num);

	//페이지바
	$pagging_cnt = ceil($pro_cnt /10);

	//시작페이지
	$start_page = (floor($page/10)*10)+1;

	//마지막페이지
	$end_page = $start_page + 9;

	if($end_page > $pagging_cnt){
		$end_page = $pagging_cnt;
	}
//==========화살표=============
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
<h2>생산등록관리</h2>
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
#production_cnt,
#product_name,
#production_note,
#plan_date_start,
#plan_date_end,
#product_date_start,
#production_date_end
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
         <span class='search_field'>수량</span>
         <input type='number' id='production_cnt' placeholder="생산수량" autocomplete="off" value='<?=$is_production_cnt?>' />
      </div>
   </section>
   <section id='searchoption_two' class="search_section">
      <div class='group'>
         <span class='search_field'>상품명</span>
         <input type='text' id='product_name' placeholder="상품명" autocomplete="off" value='<?=$is_product_name?>' />
      </div>
      <div class='group'>
         <span class='search_field'>비고</span>
         <input type='text' id='production_note' placeholder="비고" autocomplete="off"  value='<?=$production_note?>' />
      </div>
	</section>
	<section id='searchoption_three' class="search_section">
		<div class='group'>
			<span class='search_field'>계획날짜</span>
			<input type='date' id='plan_date_start'autocomplete="off" value='<?= $is_plan_date_start?>' />
		</div>
		<div class='group'>
         <span class='search_field'>생산날짜</span>
         <input type='date' id='product_date_start' autocomplete="off" value='<?=$is_production_date_start?>' />
      </div>
   </section>
   <section id='searchoption_four' class="search_section">
      <div class='group'>
         <span class='search_field'>~~</span>
         <input type='date' id='plan_date_end'  value='<?=$is_plan_date_end?>' />
      </div>
      <div class='group'>
         <span class='search_field'>~~</span>
         <input type='date' id='production_date_end'  value='<?=$is_production_date_end?>' />
      </div>
	</section>	
</article>

<div style='clear:both;'></div>
<hr>

<!-- 버튼 모음 -->
<div class='btn_area'>
	<button onclick='search();'>조회</button>
	<button onclick="render('production_management/production_registration')">등록</button>
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
				<th>계획번호</th>
				<th>거래처명</th>
				<th>계획날짜</th>
				<th>상품명</th>
				<th>상품가격</th>
				<th>계획수량</th>
				<th>계획비고</th>
				<th>생산등록번호</th>
				<th>생산등록날짜</th>
				<th>생산등록수량</th>
				<th>생산등록소계</th>
				<th>비고</th>
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

                    // 계획번호 정보 있다면 출력
                    if(isset($row['plan_number']) === true){
                        ?>
                        <td rowspan='<?=$row['plan_number']['rowspan']?>'><?=$row['plan_number']['plan_number']?></td>
                        <td rowspan='<?=$row['plan_number']['rowspan']?>'><?=$row['plan_number']['account_name']?></td>
                        <td rowspan='<?=$row['plan_number']['rowspan']?>'><?=$row['plan_number']['plan_date']?></td>
                        <?php
                    }

                    // 계획sid 정보 있다면 출력
                    if(isset($row['plan_sid']) === true){
                        ?>
                        <td rowspan='<?=$row['plan_sid']['rowspan']?>'><?=$row['plan_sid']['product_name']?></td>
                        <td rowspan='<?=$row['plan_sid']['rowspan']?>'><?=number_format($row['plan_sid']['product_price'])?></td>
                        <td rowspan='<?=$row['plan_sid']['rowspan']?>'><?=number_format($row['plan_sid']['plan_cnt'])?></td>
                        <td rowspan='<?=$row['plan_sid']['rowspan']?>'><?=$row['plan_sid']['plan_note']?></td>
                        <?php
                    }

                    // 생산등록번호 정보 있다면 출력
                    if(isset($row['production_number']) === true){
                        ?>
                        <td rowspan='<?=$row['production_number']['rowspan']?>'><?=$row['production_number']['production_number']?></td>
                        <td rowspan='<?=$row['production_number']['rowspan']?>'><?=$row['production_number']['production_date']?></td>
                        <?php
                    }

                    // 생산등록sid 정보 출력
                    ?>
                    <td><?=number_format($row['production_sid']['production_cnt'])?></td>
                    <td><?=number_format($row['production_sid']['amount'])?></td>
                    <td><?=$row['production_sid']['production_note']?></td>
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
