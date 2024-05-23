<?php

	// 품목재고 페이지

	define('__CORE_TYPE__', 'view');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//페이지 유효성 검사
	$page = 1;
	if(isset($_POST['page']) === true){
		if($_POST['page'] !== '' && $_POST['page'] !== null){
			$page = $_POST['page'];
		}
	}

	//품목 정보 유효성 검사(품목 이름, 품목 가격, 품목 코드,비고)
	$product_name = '';
	if(isset($_POST['product_name']) === true){
		if($_POST['product_name'] !== '' && $_POST['product_name'] !== null){
			$product_name = $_POST['product_name'];
		}
	}
	$product_price = '';
	if(isset($_POST['product_price']) === true){
		if($_POST['product_price'] !== '' && $_POST['product_price'] !== null){
			$product_price = $_POST['product_price'];
		}
	}

	// 품목sid별 최신 재고 sid 가져오기
	$stock_sid = "SELECT MAX(sid) as sid, product_sid FROM product_stock WHERE company_sid='".__COMPANY_SID__."' GROUP BY product_sid ORDER BY sid DESC;";
	$query_result = sql($stock_sid);
	$query_result = select_process($query_result);

	$stock_sid_arr = array();
	$product_sid = array();

	for($i=$query_result['output_cnt']-1; $i>=0; $i--){

		//품목sid 배열에 푸쉬
		array_push($product_sid, $query_result[$i]['product_sid']);

		//품목재고sid 배열에 푸쉬
		array_push($stock_sid_arr, $query_result[$i]['sid']);
	}

	// 품목sid 배열 크기
	$product_sid_count = count($product_sid);

	// 품목sid별로 재고 수를 0으로 초기화
	for($i = 0; $i < $product_sid_count; $i++) {

	    $sid = $product_sid[$i];
	    $remain_product_cnt[$sid] = 0;
	}

	// 품목sid별 최신 품목 sid IN 조건 조회
	$select_stock_remain_sql = "SELECT sid, product_sid, product_stock_cnt FROM product_stock WHERE company_sid = '".__COMPANY_SID__."' AND sid IN('".implode("','", $stock_sid_arr)."');";
	$query_result = sql($select_stock_remain_sql);
	$query_result = select_process($query_result);

	// 조회 결과로 $product_stock_cnt 업데이트
	for($i = 0; $i < $query_result['output_cnt']; $i++) {

	    $product_sid = $query_result[$i]['product_sid'];

	    if(isset($remain_product_cnt[$product_sid])) {
	        $remain_product_cnt[$product_sid] = (int)$query_result[$i]['product_stock_cnt'];
	    }    
	}
	
	//거래처 테이블 조회 및 가공(회사코드 조건)
	$select_sql = "SELECT sid, product_name, product_price, product_code, product_note FROM product_info WHERE company_sid = '".__COMPANY_SID__."'";
	// 검색필터 적용(값이 있을 경우 where조건에 sql 붙여주기)
	if($product_name !== ''){
		$select_sql .= " AND product_name like '%$product_name%'";
	}
	if($product_price !== ''){
		$select_sql .= " AND product_price like '%$product_price%'";
	}

	$total_sql = $select_sql.';';

	// page 처리
	// 시작페이지 
	$search_start = ($page - 1) * 10;
	// 페이지설정 범위를 sql문으로 작성
	$select_sql .= " LIMIT $search_start, 10";
	$select_sql .= ";";

	$query_result = sql($select_sql);
	$data = select_process($query_result);

	// ***** 페이징 처리를 위한 영역 *****
	// 값이 있는 조건 sql만 쿼리 수행
	$query_result = sql($total_sql);
	// 수행 결과의 행 갯수 구하기
	$pagging_cnt = mysqli_num_rows($query_result);
	//행갯수 10으로 나누고 소숫점 올림( 페이지바의 갯수)
	$pagging_cnt = ceil($pagging_cnt / 10);

	//시작페이지(현재페이지 / 10 소숫점내림 *10 +1)
	$start_page = (floor($page / 10) * 10) + 1;
	//마지막페이지(시작페이지 +9)
	$end_page = $start_page + 9;
	// 만약 마지막페이지가 페이지갯수보다 클 경우 마지막페이지 = 페이지 갯수
	if($end_page > $pagging_cnt){
		$end_page = $pagging_cnt;
	}



	// 페이징 화살표
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

<h2>품목 재고현황</h2>

<article id='search_area'>
	<section id='searchoption_left'>
		<div class='group'>
			<span class='search_field'>품목명</span>
			<input type='text' id='product_name' placeholder="품목명" autocomplete="off" value='<?=$product_name?>' />
		</div>
	</section>
	<section id='searchoption_right'>
		<div class='group'>
			<span class='search_field'>품목가격</span>
			<input type='text' id='product_price' placeholder="품목가격" autocomplete="off" value='<?=$product_price?>' />
		</div>
	</section>
</article>

<div style='clear:both;'></div>
<hr />

<div class='btn_area'>
	<button onclick='search();'>조회</button>
	<button onclick="resetSearchFields()">검색 조건 초기화</button>
</div>

<div style='clear:both;'></div>

<section id='table_area'>

	<table id='grid_table'>
		<colgroup>
			<col width='300px' />
			<col width='200px' />
			<col width='300px' />
			<col />
		</colgroup>
		<!-- 목록 출력 영역(헤드) -->
		<thead>
			<tr>
				<th>품목명</th>
				<th>가격</th>
				<th>재고현황</th>
				<th>비고</th>
			</tr>
		</thead>

		 <!-- 목록 바디 영역 -->
		 <tbody>
		 	<?php
		 	for($i=0; $i<$data['output_cnt']; $i++){
		 		$product_stock = $remain_product_cnt[$data[$i]['sid']] ??  0 ;
		 		?>
		 		<tr>
		 			<td><?=$data[$i]['product_name']?></td>
		 			<td><?=number_format($data[$i]['product_price'])?></td>
		 			<td><?=number_format($product_stock)?></td>
		 			<td><?=$data[$i]['product_note']?></td>
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

