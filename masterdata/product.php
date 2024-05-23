<?php

	//기준정보 품목 페이지

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
	$product_code = '';
	if(isset($_POST['product_code']) === true){
    if($_POST['product_code'] !== '' && $_POST['product_code'] !== null){
        $product_code = $_POST['product_code']; // 오타 수정
    	}
	}
	$product_note = '';
	if(isset($_POST['product_note']) === true){
    if($_POST['product_note'] !== '' && $_POST['product_note'] !== null){
        $product_note = $_POST['product_note']; // 오타 수정
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
	if($product_code !== ''){
    	$select_sql .= " AND product_code like '$product_code%'";
	}
	if($product_note !== ''){
		$select_sql .= " AND product_note like '%$product_note%'";
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

<h2>품목 관리</h2>

<article id='search_area'>
	<section id='searchoption_left'>
		<div class='group'>
			<span class='search_field'>품목명</span>
			<input type='text' id='product_name' placeholder="품목명" autocomplete="off" value='<?=$product_name?>' />
		</div>
		<div class='group'>
			<span class='search_field'>가격</span>
			<input type='text' id='product_price' placeholder="가격" autocomplete="off" value='<?=$product_price?>' />
		</div>
	</section>

	<section id='searchoption_right'>
		<div class='group'>
			<span class='search_field'>품목코드</span>
			<input type='text' id='product_code' placeholder="품목코드" autocomplete="off" value='<?=$product_code?>' />
		</div>
		<div class='group'>
			<span class='search_field'>품목비고</span>
			<input type='text' id='product_note' placeholder="품목비고" autocomplete="off" value='<?=$product_note?>' />
		</div>
		
	</section>
	
</article>

<div style='clear:both;'></div>
<hr />

<div class='btn_area'>
	<button onclick='search();'>조회</button>
	<button onclick='render("masterdata/product_registration");'>등록</button>
	<button onclick='product_delete();'>선택삭제</button>
	<button onclick="resetSearchFields()">검색 조건 초기화</button>
</div>

<div style='clear:both;'></div>

<section id='table_area'>

	<table id='grid_table'>
		<colgroup>
			<col width='50px' />
			<col width='100px' />
			<col width='200px' />
			<col width='200px' />
			<col width='200px' />
			<col />
		</colgroup>
		<!-- 목록 출력 영역(헤드) -->
		<thead>
			<tr>
				<th><input type='checkbox' id='checked_all' onclick='check_all(this);' /></th>
				<th>수정</th>
				<th>품목명</th>
				<th>가격</th>
				<th>품목 코드</th>
				<th>비고</th>
			</tr>
		</thead>

		 <!-- 목록 바디 영역 -->
		 <tbody>
		 	<?php
		 	for($i=0; $i<$data['output_cnt']; $i++){
		 		?>
		 		<tr>
		 			<td><input type='checkbox' id='<?=$data[$i]['sid']?>' name='checked' sid='<?=$data[$i]['sid']?>' onclick='check_one(this);' /></td>
		 			<td><button onclick='update("<?=$data[$i]['sid']?>");'>수정</button></td>
		 			<td><?=$data[$i]['product_name']?></td>
		 			<td><?=number_format($data[$i]['product_price'])?></td>
		 			<td><?=$data[$i]['product_code']?></td>
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

