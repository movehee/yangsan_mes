<?php

	//수주 조회 페이지

	define('__CORE_TYPE__', 'view');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';
	//유효성 검사(페이지,거래처명,품목명,품목수량,품목가격,수주비고,시작날짜,마지막날짜)
	$page = 1;
	if(isset($_POST['page']) === true){
		if($_POST['page'] !== '' && $_POST['page'] !== null){
			$page = $_POST['page'];
		}
	}
	$is_account_name = '';
	if(isset($_POST['account_name']) === true){
		if($_POST['account_name'] !== ''&& $_POST['account_name'] !== null){
			$is_account_name = $_POST['account_name'];
		}
	}

	$product_cnt = '';
	if(isset( $_POST['product_cnt']) === true){
		if($_POST['product_cnt'] !== '' && $_POST['product_cnt'] !== null){
			$product_cnt = $_POST['product_cnt'];
		}
	}
	$is_product_name = '';
	if(isset($_POST['product_name']) === true){
		if($_POST['product_name'] !== '' && $_POST['product_name'] !== null){
			$is_product_name = $_POST['product_name'];
		}
	}
	
	$order_note = '';
	if(isset($_POST['order_note']) === true){
		if($_POST['order_note'] !== '' && $_POST['order_note'] !== null){
			$order_note = $_POST['order_note'];
		}
	}
	$order_date_start = date('Y-m-d', strtotime('-1 months'));
	if(isset($_POST['order_date_start']) === true){
		if($_POST['order_date_start'] !== '' && $_POST['order_date_start'] !== null){
			$order_date_start = $_POST['order_date_start'];
		}
	}

	$order_date_end = date('Y-m-d');
	if(isset($_POST['order_date_end']) === true){
		if($_POST['order_date_end'] !== '' && $_POST['order_date_end'] !== null){
			$order_date_end = $_POST['order_date_end'];
		}
	}


	
	//거래처테이블에서 조회(거래처sid, 거래처명)
	$select_account_sql = "SELECT sid,account_name FROM account_info WHERE company_sid ='".__COMPANY_SID__."'";

	//거래처 검색어가 있을 시
	if($is_account_name !== ''){
		$select_account_sql .= " AND account_name like '%$is_account_name%'";
	}

	$select_account_sql .= ';';
	
	$query_result_account = sql($select_account_sql);
	$query_result_account = select_process($query_result_account);

	$account_sid = array();
	$account_name = array();

	for($i = 0; $i < $query_result_account['output_cnt']; $i++){

		//수주sid 배열에 푸쉬
		array_push($account_sid, $query_result_account[$i]['sid']);
		//거래처sid => 거래처명
		$account_name[$query_result_account[$i]['sid']] = $query_result_account[$i]['account_name'];

	}

	

	//품목테이블에 조회(품목sid, 품목명, 품목가격)
	$select_product_sql = "SELECT sid,product_name,product_price FROM product_info WHERE company_sid = '".__COMPANY_SID__."' ";

	//품목 검색어가 있을 시
	if($is_product_name !== ''){
		$select_product_sql .= "AND product_name like '%$is_product_name%'";
	}
	
	$select_product_sql .= ';';

	$query_result_product = sql($select_product_sql);
	$query_result_product = select_process($query_result_product);

	$product_sid = array();
	$product_name = array();
	$product_price = array();

	for($i = 0; $i < $query_result_product['output_cnt']; $i++){

		//품목sid 배열에 푸쉬
		array_push($product_sid,$query_result_product[$i]['sid']);
		//품목sid => 품목명
		$product_name[$query_result_product[$i]['sid']] = $query_result_product[$i]['product_name'];
		//품목sid => 품목가격
		$product_price[$query_result_product[$i]['sid']] = $query_result_product[$i]['product_price'];

	}	

	//수주조회 수주넘버 검색
	$select_order_num_sql = "SELECT  order_number FROM order_info WHERE company_sid= '".__COMPANY_SID__."'";

	
	//거래처 겸색어가 있다면 account_sid를 in으로 검색
	if($is_account_name !== ''){
            
            $account_in_sql = implode("','", $account_sid);

            $select_order_num_sql .= "AND account_sid IN ('$account_in_sql')";
            
 	}

 	//품목 겸색어가 있다면 product_sid를 in으로 검색
	if($is_product_name !== ''){
            
            $product_in_sql = implode("','", $product_sid);

            $select_order_num_sql .= "AND product_sid IN ('$product_in_sql')";            
 	}

	//수량이 있을시 검색필터 적용
	if($product_cnt !== ''){
		$select_order_num_sql .= "AND product_cnt like '%$product_cnt%'";
	}
	//비고가 있을시 검색필터 적용
	if($order_note !== ''){
		$select_order_num_sql .= "AND order_note like '%$order_note%'";
	}
	//날짜데이account_sid터가 둘다 선택되었을 시 필터 적용
	if ($order_date_start !== '' && $order_date_end !== '') {
    $select_order_num_sql .= " AND `order_date` BETWEEN '$order_date_start 00:00:01' and '$order_date_end 23:59:59'";
	}

	$select_order_num_sql .= " GROUP BY order_number ORDER BY order_number";

	$total_sql = $select_order_num_sql.';';

	$query_result2 = sql($total_sql);

	//수주넘버만 가져온 값
	$order_num_arr = select_process($query_result2);
	
	
	//전체 수주번호
	$total_order_num = array();
	for($i=0; $i<$order_num_arr['output_cnt']; $i++){

		array_push($total_order_num , $order_num_arr[$i]['order_number']);
	}


	
// ---------------------------------------------------------------------------------------------------------

	//내가 선택한 페이지
	$search_start = ($page - 1) * 10; 

	//limit 걸어주기
	$select_order_num_sql .= " LIMIT $search_start,10";
	$select_order_num_sql .= ";";

	$select_limit_sql = sql($select_order_num_sql);

	//페이지 범위설정
	$select_limit_sql = select_process($select_limit_sql);

	$limit_number = array();
	for($i=0; $i<$select_limit_sql['output_cnt']; $i++){

		array_push($limit_number,$select_limit_sql[$i]['order_number']);
	}


	$order_data = array();

	//limit 조회결과가 있으면
	if($select_limit_sql['output_cnt'] > 0){

		//수주조회
		$select_order_sql = "SELECT sid,account_sid,product_sid,product_cnt, order_number, order_date, order_note FROM order_info WHERE company_sid ='".__COMPANY_SID__."'";
		
		$order_num_in = implode("','" , $limit_number);
		
		$select_order_sql .= "AND order_number IN ('$order_num_in')";

		$select_order_sql .= ';';


		//수주조회
		$query_result = sql($select_order_sql);
		$query_result = select_process($query_result);

		
		for($i=0; $i<$query_result['output_cnt']; $i++){

			$temp = array();
			// 수주sid,품목명,품목가격,품목수량,수주비고,수주날짜
			$temp['product_name']= $product_name[$query_result[$i]['product_sid']];
			$temp['product_cnt'] = $query_result[$i]['product_cnt'];
			$temp['product_price'] = $product_price[$query_result[$i]['product_sid']];
			$temp['order_date'] = $query_result[$i]['order_date'];
			$temp['order_note'] = $query_result[$i]['order_note'];
			$temp['order_sid'] = $query_result[$i]['sid'];
			
			//수주 넘버가 없을 시 배열화 
			if(isset($order_data[$query_result[$i]['order_number']]) === false){
            $order_data[$query_result[$i]['order_number']] = array();
				$order_data[$query_result[$i]['order_number']]['account_sid'] = $query_result[$i]['account_sid'];
      	}

			array_push($order_data[$query_result[$i]['order_number']], $temp);
		}
	}
	

	$order_list = array();

	//2차 후처리
	$data_keys = array_keys($order_data);
	$data_keys_cnt = count($data_keys);

	if($data_keys_cnt > 0){

		for($i=0; $i<$data_keys_cnt; $i++){

			$temp = array();
			$temp['account_name'] = $account_name[$order_data[$data_keys[$i]]['account_sid']];
			unset($order_data[$data_keys[$i]]['account_sid']);
			$temp['order_number'] = $data_keys[$i];
			$temp['order_data'] = $order_data[$data_keys[$i]];//품목명,품목수량,품목가격,날짜,비고,삭제(order_sid)
			$temp['rowspan'] = count($temp['order_data']);

			
			array_push($order_list,$temp);
		}
	}
	
	$order_list_cnt = count($order_list);

	//**********페이지처리**********

	//전체 수주번호 개수
	$order_number_cnt = count($total_order_num);

	//페이지 바 개수
	$pagging_cnt = ceil($order_number_cnt / 10);


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

<h2>수주관리</h2>

<!-- 검색어 조회 -->
	<article id='search_area'>
	<section id='searchoption_left'>
		<div class='group'>
			<span class='search_field'>거래처명</span>
			<input type='text' id='account_name' placeholder="거래처명" autocomplete="off" value='<?= $is_account_name?>' />
		</div>
		<div class='group'>
         <span class='search_field'>수량</span>
         <input type='number' id='product_cnt' placeholder="수량" autocomplete="off" value='<?=$product_cnt?>' />
      </div>
      <div class='group'>
         <span class='search_field'>시작날짜</span>
         <input type='date' id='order_date_start'  value='<?=$order_date_start?>' />
      </div>
	</section>

	<section id='searchoption_right'>
		<div class='group'>
			<span class='search_field'>품목명</span>
			<input type='text' id='product_name' placeholder="품목명" autocomplete="off" value='<?= $is_product_name?>' />
		</div>
		<div class='group'>
         <span class='search_field'>비고</span>
         <input type='text' id='order_note' placeholder="비고" autocomplete="off" value='<?=$order_note?>' />
      </div>
      <div class='group'>
         <span class='search_field'>마지막날짜</span>
         <input type='date' id='order_date_end'  value='<?=$order_date_end?>' />
      </div>
	</section>
	
</article>

<div style='clear:both;'></div>
<hr />

<!-- 버튼 모음 -->
<div class='btn_area'  >
	<button onclick='search();'>조회</button>
	<button onclick='render("sales_management/order_registration");'>등록</button>
	<button onclick='order_delete();'>선택삭제</button>
	<button onclick="resetSearchFields()">검색 조건 초기화</button>
</div>

<div style='clear:both;'></div>

<!-- 조회 화면 -->

<section id="table_area">
	<table id='grid_table'>
		<colgroup>
			<col width='50px' />
			<col width='50px' />
			<col width='100px' />
			<col width='100px' />
			<col width='50px' />
			<col width='100px' />
			<col width='50px' />
			<col width='50px' />
			<col width='50px'/>
			<col width='50px'/>
			<col width='50px'/>
		</colgroup>

		<!-- 목록 출력 영역(헤드) -->
		<thead>
			<tr>
				<th><input type='checkbox' id='checked_all' onclick='check_all(this);' /></th>
				<th>수주번호</th>
				<th>거래처명</th>
				<th>품목명</th>
				<th>품목가격</th>
				<th>품목수량</th>
				<th>소계금액</th>
				<th>비고</th>
				<th>삭제</th>
				<th>날짜</th>
				<th>수정</th>
			</tr>
		</thead>
		<!-- 바디 영역 -->
		<!-- 목록 바디 영역 -->
		<tbody>
		   <?php
		   for ($i = 0; $i < $order_list_cnt; $i++) { //수주번호 부분 
     		?>
	        <tr>
	            <td rowspan="<?= $order_list[$i]['rowspan'] ?>">
	                <input type='checkbox' id='<?= $order_list[$i]['order_number']?>' name='checked' sid='<?= $order_list[$i]['order_number']?>' onclick='check_one(this);' />
	            </td>
	            <td rowspan="<?= $order_list[$i]['rowspan'] ?>"><?= $order_list[$i]['order_number'] ?></td>
	            <td rowspan="<?= $order_list[$i]['rowspan'] ?>"><?= $order_list[$i]['account_name'] ?></td>
	            <?php
	            for ($j = 0; $j < $order_list[$i]['rowspan']; $j++) { // 수주sid 부분

	            		$product_price = $order_list[$i]['order_data'][$j]['product_price'];
	            		$product_cnt = $order_list[$i]['order_data'][$j]['product_cnt'];
	            		$ammount = $product_price*$product_cnt;
	                ?>
	               <td><?= $order_list[$i]['order_data'][$j]['product_name'] ?></td>
	               <td><?= number_format($product_price) ?></td>
	               <td><?= $product_cnt  ?></td>
	               <td><?= number_format($ammount)?></td>
	               <td><?= $order_list[$i]['order_data'][$j]['order_note'] ?></td>
	               <td><button onclick='deleteRow("<?= $order_list[$i]['order_data'][$j]['order_sid'] ?>");'>삭제</button></td>
	            	<?php
	               if ($j === 0) { // 수주번호 부분
             		?>
             			<td rowspan="<?= $order_list[$i]['rowspan'] ?>"><?= $order_list[$i]['order_data'][$j]['order_date'] ?></td>
	                  <td rowspan="<?= $order_list[$i]['rowspan'] ?>">
                			<button onclick='update("<?= $order_list[$i]['order_number']?>");'>수정</button>
            			</td>
	               <?php
	               }
	               ?>
          	</tr>
          <?php
            }
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