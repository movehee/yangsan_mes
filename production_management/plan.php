<?php
	
	// 생산계획 조회 페이지 

	define('__CORE_TYPE__', 'view');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//유효성 검사(페이지,거래처명,품목명,품목수량,품목가격,수주비고,시작날짜,마지막날짜)
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
	//생산계획 수량
	$is_product_cnt = '';
	if(isset( $_POST['product_cnt']) === true){
		if($_POST['product_cnt'] !== '' && $_POST['product_cnt'] !== null){
			$is_product_cnt = $_POST['product_cnt'];
		}
	}
	//품목명
	$is_product_name = '';
	if(isset($_POST['product_name']) === true){
		if($_POST['product_name'] !== '' && $_POST['product_name'] !== null){
			$is_product_name = $_POST['product_name'];
		}
	}
	//생산계획비고
	$is_plan_note = '';
	if(isset($_POST['plan_note']) === true){
		if($_POST['plan_note'] !== '' && $_POST['plan_note'] !== null){
			$is_plan_note = $_POST['plan_note'];
		}
	}
	//생산계획 시작 날짜
	$is_plan_date_start = date('Y-m-d', strtotime('-1 months'));
	if(isset($_POST['plan_date_start']) === true){
		if($_POST['plan_date_start'] !== '' && $_POST['plan_date_start'] !== null){
			$is_plan_date_start = $_POST['plan_date_start'];
		}
	}
	//생산계획 종료 날짜
	$is_plan_date_end = date('Y-m-d');
	if(isset($_POST['plan_date_end']) === true){
		if($_POST['plan_date_end'] !== '' && $_POST['plan_date_end'] !== null){
			$is_plan_date_end = $_POST['plan_date_end'];
		}
	}

//1. 기준정보 조회(거래처,품목)
//************************************************************************************************************
//1-1.거래처 조회(if필터 -> 조건추가)
	//거래처sid
	$select_account_sql = "SELECT sid,account_name FROM account_info WHERE company_sid ='".__COMPANY_SID__."'";

	//거래처 검색어가 있을 경우
	if($is_account_name !== ''){
		$select_account_sql .= " AND account_name like '%$is_account_name%'";
	}

	$select_account_sql .= ';';
	
	$query_result_account = sql($select_account_sql);
	$query_result_account = select_process($query_result_account);

	//거래처sid를 키로하는 거래처명 매핑데이터
	$account_sid = array();
	$account_name = array();

	for($i = 0; $i < $query_result_account['output_cnt']; $i++){

		//거래처 sid 배열에 푸쉬
		array_push($account_sid, $query_result_account[$i]['sid']);

		//거래처sid => 거래처명
		$account_name[$query_result_account[$i]['sid']] = $query_result_account[$i]['account_name'];

	}

	$account_sid_cnt = count($account_sid); //거래처sid 수량
//************************************************************************************************************
//1-2.품목정보조회(if필터 -> 조건추가)
	//품목sid
	$select_product_sql = "SELECT sid,product_name,product_price FROM product_info WHERE company_sid = '".__COMPANY_SID__."' ";

	// 품목검색어가 있을 경우
	if($is_product_name !== ''){
		$select_product_sql .= "AND product_name like '%$is_product_name%'";
	}
	
	$select_product_sql .= ';';

	$query_result_product = sql($select_product_sql);
	$query_result_product = select_process($query_result_product);

	$product_sid = array();
	$product_name = array();
	$product_price = array();

	//품목sid를 키로하는 품명 매핑데이터
	for($i = 0; $i < $query_result_product['output_cnt']; $i++){

		//품목sid 배열에 푸쉬
		array_push($product_sid,$query_result_product[$i]['sid']);
		//품목sid => 품목명 맵핑
		$product_name[$query_result_product[$i]['sid']] = $query_result_product[$i]['product_name'];
		//품목sid => 품목가격 맵핑
		$product_price[$query_result_product[$i]['sid']] = $query_result_product[$i]['product_price'];

	}

//************************************************************************************************************
//1.수주정보조회(거래처sid in + 품목sid in)
	$select_order_num_sql = "SELECT sid,order_number,account_sid,product_sid,order_date,product_cnt,order_note FROM order_info WHERE company_sid= '".__COMPANY_SID__."'";
	
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
 	$select_order_num_sql .= ';';

 	$query_result_order = sql($select_order_num_sql);
 	$query_result_order = select_process($query_result_order);

	//수주정보 가공(관계형 배열 미리 만들기 -> $total_data[수주넘버][list][수주번호][list])
	$total_data = array();
	$order_sid_num_map = array();
	$pro_name_map = array();
	$pro_price_map = array();

	//수주sid 배열
	$order_sid = array();
	for($i = 0; $i < $query_result_order['output_cnt']; $i++){

		// data[order_number] 없으면 선언
		if(isset($total_data[$query_result_order[$i]['order_number']]) === false){
			$temp = array();
			$temp['order_number'] = $query_result_order[$i]['order_number'];
			$temp['order_date'] = $query_result_order[$i]['order_date'];
			$temp['account_name'] = $account_name[$query_result_order[$i]['account_sid']];
			$temp['rowspan'] = 0;
			$temp['list'] = array();

			$total_data[$query_result_order[$i]['order_number']] = $temp;
		}
		
		// data[order_number][list][order_sid] 없으면 선언
		if(isset($total_data[$query_result_order[$i]['order_number']]['list'][$query_result_order[$i]['sid']]) === false){

		$temp = array();
		$temp['product_name'] = $product_name[$query_result_order[$i]['product_sid']];
		$temp['product_cnt'] = $query_result_order[$i]['product_cnt'];
		$temp['product_price'] = $product_price[$query_result_order[$i]['product_sid']];
		$temp['ammount'] = (int)$product_price[$query_result_order[$i]['product_sid']]*$query_result_order[$i]['product_cnt']; 
		$temp['order_note'] = $query_result_order[$i]['order_note'];
		$temp['rowspan'] = 0;
		$temp['list'] = array();

		$total_data[$query_result_order[$i]['order_number']]['list'][$query_result_order[$i]['sid']] = $temp;
		}
		// order_sid 배열 푸시
		array_push($order_sid, $query_result_order[$i]['sid']);

		// order_number 매핑 => key: order_sid / product_name 매핑 => key: order_sid / product_price 
		$order_sid_num_map[$query_result_order[$i]['sid']] = $query_result_order[$i]['order_number'];
		// 수주sid => 품목sid 
		$pro_name_map[$query_result_order[$i]['sid']] = $query_result_order[$i]['product_sid'];
		// 수주sid => 품목sid 
		$pro_price_map[$query_result_order[$i]['sid']] = $query_result_order[$i]['product_sid'];
		
	}
	
//*******************************************************************
// 전체 생산계획 넘버 조회
	$select_plan_num_sql = "SELECT plan_number FROM plan_info WHERE company_sid= '".__COMPANY_SID__."'";

	//수량이 있을시 검색필터 적용
	if($is_product_cnt !== ''){
		$select_plan_num_sql .= "AND plan_cnt like '%$is_product_cnt%'";
	}
	//비고가 있을시 검색필터 적용
	if($is_plan_note !== ''){
		$select_plan_num_sql .= "AND plan_note like '%$is_plan_note%'";
	}
	//날짜가 둘다 선택되었을 시 필터 적용
	if ($is_plan_date_start !== '' && $is_plan_date_end !== '') {
    $select_plan_num_sql .= " AND `plan_date` BETWEEN '$is_plan_date_start 00:00:01' and '$is_plan_date_end 23:59:59'";
	}
	
	$select_plan_num_sql .= " GROUP BY plan_number ORDER BY plan_number";

	$total_sql = $select_plan_num_sql.';';

	$query_result_plan_num = sql($total_sql);

	//생산계획넘버만 가져온 값
	$plan_num_arr = select_process($query_result_plan_num);

	//전체 생산계획번호
	$total_plan_num = array();
	for($i=0; $i<$plan_num_arr['output_cnt']; $i++){

		array_push($total_plan_num , $plan_num_arr[$i]['plan_number']);
	}

	//내가 선택한 페이지
	$search_start = ($page - 1) * 10; 

	//limit 걸어주기
	$select_plan_num_sql .= " LIMIT $search_start,10";
	$select_plan_num_sql .= ";";

	$select_limit_sql = sql($select_plan_num_sql);

	//페이지 범위설정
	$select_limit_sql = select_process($select_limit_sql);

	$limit_number = array();
	for($i=0; $i<$select_limit_sql['output_cnt']; $i++){

		array_push($limit_number,$select_limit_sql[$i]['plan_number']);
	}


//************************************************************************************************************

//limit 조회결과가 있으면
if($select_limit_sql['output_cnt'] > 0){

//1.계획조회(수주sid in)
	$order_in_sql = implode("','", $order_sid);

	$selct_plan_sql = "SELECT sid, plan_cnt, order_sid, plan_number, plan_date, plan_note FROM plan_info WHERE company_sid = '".__COMPANY_SID__."' AND order_sid IN('$order_in_sql') ";

	$plan_num_in = implode("','" , $limit_number);

	$selct_plan_sql .= "AND plan_number IN ('$plan_num_in')";

	$selct_plan_sql .= ';';

	$query_result_plan = sql($selct_plan_sql);
	$query_result_plan = select_process($query_result_plan);

	$plan_number = array();
	$plan_sid = array();
	for($i=0; $i<$query_result_plan['output_cnt']; $i++){

		$temp_order_number = $order_sid_num_map[$query_result_plan[$i]['order_sid']]; //조회된 수주sid로 계획넘버가져오기
		$plan_number = $query_result_plan[$i]['plan_number']; 
	
		// data[order_number][list][order_sid][list][plan_number] 없으면 선언
		if(isset($total_data[$temp_order_number]['list'][$query_result_plan[$i]['order_sid']]['list'][$query_result_plan[$i]['plan_number']]) === false){
			
			$temp = array();
			$temp['plan_date'] = $query_result_plan[$i]['plan_date'];
			$temp['plan_number'] = $query_result_plan[$i]['plan_number'];
			$temp['total_ammount'] = 0;
			$temp['rowspan'] = 0;
			$temp['list'] = array();

			$total_data[$temp_order_number]['list'][$query_result_plan[$i]['order_sid']]['list'][$query_result_plan[$i]['plan_number']] = $temp;
		}
		
		//data[order_number][list][order_sid][list][plan_number][list][plan_sid] 선언
		$temp =array();
		$temp['plan_sid'] = $query_result_plan[$i]['sid'];
		$temp['product_name'] = $product_name[$pro_name_map[$query_result_plan[$i]['order_sid']]];
		$temp['plan_note'] = $query_result_plan[$i]['plan_note'];
		$temp['plan_cnt'] = $query_result_plan[$i]['plan_cnt'];
		$temp['plan_price'] = (int)$product_price[$pro_name_map[$query_result_plan[$i]['order_sid']]];
		$temp['ammount'] = (int)$product_price[$pro_name_map[$query_result_plan[$i]['order_sid']]]*(int)$query_result_plan[$i]['plan_cnt'];
		$temp['list'] = array();

		$total_data[$temp_order_number]['list'][$query_result_plan[$i]['order_sid']]['list'][$query_result_plan[$i]['plan_number']]['list'][$query_result_plan[$i]['sid']] = $temp;

		array_push($plan_sid,$query_result_plan[$i]['sid']); // 계획sid 배열에 푸쉬

		// plan_number total_amount 누계
		$total_data[$temp_order_number]['list'][$query_result_plan[$i]['order_sid']]['list'][$query_result_plan[$i]['plan_number']]['total_ammount'] += $total_data[$temp_order_number]['list'][$query_result_plan[$i]['order_sid']]['list'][$query_result_plan[$i]['plan_number']]['list'][$query_result_plan[$i]['sid']]['ammount'];

		// rowspan 증가
		$total_data[$temp_order_number]['rowspan']++;
     	$total_data[$temp_order_number]['list'][$query_result_plan[$i]['order_sid']]['rowspan']++;
      	$total_data[$temp_order_number]['list'][$query_result_plan[$i]['order_sid']]['list'][$query_result_plan[$i]['plan_number']]['rowspan']++;

	}
	
}	


//************************************************************************************************************
//2차 가공(4번의 for문) = 수주, 계획 색인배열 만들기
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

		$plan_number_list = $order_sid_list[$order_sid_keys[$j]]['list'];
		$plan_number_keys = array_keys($plan_number_list);
		$plan_number_keys_cnt = count($plan_number_keys);
		
		// 계획번호 루프 부분
		for($k=0; $k<$plan_number_keys_cnt; $k++){

			// 생산sid 정보가 없는 생산번호 정보 UNSET
			if($plan_number_list[$plan_number_keys[$k]]['rowspan'] === 0){
				unset($total_data[$total_data_keys[$i]]['list'][$order_sid_keys[$j]]['list'][$plan_number_keys[$k]]);
				continue;
			}
			// plan_number 데이터 생성 여부
			$plan_number_push = false;	

			$plan_sid_list = $plan_number_list[$plan_number_keys[$k]]['list'];
			$plan_sid_keys = array_keys($plan_sid_list);
			$plan_sid_keys_cnt = count($plan_sid_keys);
			// 계획 sid 루프 부분
			for($l=0; $l<$plan_sid_keys_cnt; $l++){
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

				// 계획번호 정보 생성 안되었다면 생성
				if($plan_number_push === false){

					$plan_number_data = $plan_number_list[$plan_number_keys[$k]];
					$temp['plan_number'] = $plan_number_data;

					// 계획 번호 생성되었음으로 변경
					$plan_number_push = true;

				}
				
				// 계획sid 정보 생성
				$plan_sid_data = $plan_sid_list[$plan_sid_keys[$l]];
				$temp['plan_sid'] = $plan_sid_data;
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
	$plan_plan_cnt = count($total_plan_num);

	//페이지 바 개수
	$pagging_cnt = ceil($plan_plan_cnt / 10);

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



<h2>생산계획관리</h2>

<!-- 검색어 조회 -->
	<article id='search_area'>
	<section id='searchoption_left'>
		<div class='group'>
			<span class='search_field'>거래처명</span>
			<input type='text' id='account_name' placeholder="거래처명" autocomplete="off" value='<?= $is_account_name?>' />
		</div>
		<div class='group'>
         <span class='search_field'>수량</span>
         <input type='number' id='product_cnt' placeholder="수량" autocomplete="off" value='<?=$is_product_cnt?>' />
      </div>
      <div class='group'>
         <span class='search_field'>시작날짜</span>
         <input type='date' id='plan_date_start'  value='<?=$is_plan_date_start?>' />
      </div>
	</section>

	<section id='searchoption_right'>
		<div class='group'>
			<span class='search_field'>품목명</span>
			<input type='text' id='product_name' placeholder="품목명" autocomplete="off" value='<?= $is_product_name?>' />
		</div>
		<div class='group'>
         <span class='search_field'>비고</span>
         <input type='text' id='plan_note' placeholder="비고" autocomplete="off" value='<?=$is_plan_note?>' />
      </div>
      <div class='group'>
         <span class='search_field'>마지막날짜</span>
         <input type='date' id='plan_date_end'  value='<?=$is_plan_date_end?>' />
      </div>
	</section>
	
</article>

<div style='clear:both;'></div>
<hr>

<!-- 버튼 모음 -->
<div class='btn_area'>
	<button onclick='search();'>조회
		</button>
	<button onclick="render('production_management/plan_registration')">등록</button>
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
                <th>수주 등록일</th>
                <th>수주 품명</th>
                <th>수주 수량</th>
                <th>수주 소계</th>
                <th>수주 비고</th>
                <th>계획 번호</th>
                <th>계획 등록일</th>
                <th>계획 총계</th>
                <th>계획 수정</th>
                <th>계획 삭제</th>
                <th>계획 품명</th>
                <th>계획 수량</th>
                <th>계획 소계</th>
                <th>계획 비고</th>
                <th>단건 삭제</th>
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
                        <td rowspan='<?=$row['order_sid']['rowspan']?>'><?=number_format($row['order_sid']['product_cnt'])?></td>
                        <td rowspan='<?=$row['order_sid']['rowspan']?>'><?=number_format($row['order_sid']['ammount'])?></td>
                        <td rowspan='<?=$row['order_sid']['rowspan']?>'><?=$row['order_sid']['order_note']?></td>
                        <?php
                    }

                    // 계획번호 정보 있다면 출력
                    if(isset($row['plan_number']) === true){
                        ?>
                        <td rowspan='<?=$row['plan_number']['rowspan']?>'><?=$row['plan_number']['plan_number']?></td>
                        <td rowspan='<?=$row['plan_number']['rowspan']?>'><?=$row['plan_number']['plan_date']?></td>
                        <td rowspan='<?=$row['plan_number']['rowspan']?>'><?=number_format($row['plan_number']['total_ammount'])?></td>
                        <td rowspan='<?=$row['plan_number']['rowspan']?>'><button onclick='update("<?=$row['plan_number']['plan_number']?>");'>계획수정</button></td>
                        <td  rowspan='<?=$row['plan_number']['rowspan']?>'><button onclick='plan_number_delete("<?=$row['plan_number']['plan_number']?>");'>계획삭제</button></td>
                        <?php
                    }

                    // 계획sid 정보 출력
                    ?>
                    <td><?=$row['plan_sid']['product_name']?></td>
                    <td><?=number_format($row['plan_sid']['plan_cnt'])?></td>
                    <td><?=number_format($row['plan_sid']['plan_price'])?></td>
                    <td><?=$row['plan_sid']['plan_note']?></td>
                    <td ><button onclick='plan_sid_delete("<?=$row['plan_sid']['plan_sid']?>");'>삭제</button></td>
                    
                </tr>
                <?php
            }
            ?>
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