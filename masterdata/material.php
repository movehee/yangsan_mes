<?php

	// 기준정보 자재 페이지

	define('__CORE_TYPE__', 'view');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//유효성 검사(페이지)
	$page = 1;
	if(isset($_POST['page']) === true){
		if($_POST['page'] !== '' && $_POST['page'] !== null){
			$page = $_POST['page'];
		}
	}
	// 거래처정보 유효성 검사(자재명, 자재단가)
	$material_name = '';
	if(isset($_POST['material_name']) === true){
		if($_POST['material_name'] !== '' && $_POST['material_name'] !== null){
			$material_name = $_POST['material_name'];
		}
	}
	$material_price = '';
	if(isset($_POST['material_price']) === true){
		if($_POST['material_price'] !== '' && $_POST['material_price'] !== null){
			$material_price = $_POST['material_price'];
		}
	}
	
	//자재 테이블 조회 및 가공(회사코드 조건)
	$select_sql = "SELECT sid, material_name, material_price ,material_note FROM material_info WHERE company_sid = '".__COMPANY_SID__."'";
	
	// 검색필터 적용(값이 있을 경우 where조건에 sql 붙여주기)
	if($material_name !== ''){
		$select_sql .= " AND material_name like '%$material_name%'";
	}
	if($material_price !== ''){
		$select_sql .= " AND material_price like '%$material_price%'";
	}
	
	// 최종 쿼리문 (세미콜론 붙여주기)
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

<h2>자재 관리</h2>

<article id='search_area'>
	<section id='searchoption_left'>
		<div class='group'>
			<span class='search_field'>자재명</span>
			<input type='text' id='material_name' placeholder="자재명"
			 autocomplete="off" value='<?=$material_name?>' />
		</div>
	</section>

	<section id='searchoption_right'>
		<div class='group'>
			<span class='search_field'>자재단가</span>
			<input type='text' id='material_price' placeholder="자재단가" autocomplete="off" value='<?=$material_price?>' />
		</div>
	</section>
	
</article>

<div style='clear:both;'></div>
<hr />

<div class='btn_area'>
	<button onclick='search();'>조회</button>
	<button onclick='render("masterdata/material_registration");'>등록</button>
	<button onclick='material_delete();'>선택삭제</button>
	<button onclick="resetSearchFields()">검색 조건 초기화</button>
</div>

<div style='clear:both;'></div>

<section id='table_area'>

	<table id='grid_table'>
		<colgroup>
			<col width='50px' />
			<col width='100px' />
			<col width='150px' />
			<col width='100px' />
			<col />
		</colgroup>
		<!-- 목록 출력 영역(헤드) -->
		<thead>
			<tr>
				<th><input type='checkbox' id='checked_all' onclick='check_all(this);' /></th>
				<th>수정</th>
				<th>자재명</th>
				<th>자재단가</th>
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
		 			<td><?=$data[$i]['material_name']?></td>
		 			<td><?=number_format($data[$i]['material_price'])?></td>
		 			<td><?=$data[$i]['material_note']?></td>
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