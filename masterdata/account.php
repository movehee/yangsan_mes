<?php

	//거래처 조회 php

	define('__CORE_TYPE__', 'view');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//유효성 검사(페이지)
	$page = 1;
	if(isset($_POST['page']) === true){
		if($_POST['page'] !== '' && $_POST['page'] !== null){
			$page = $_POST['page'];
		}
	}
	// 거래처정보 유효성 검사(거래처명, 거래처대표, 거래처연락처, 사업자번호, 비고)
	$account_name = '';
	if(isset($_POST['account_name']) === true){
		if($_POST['account_name'] !== '' && $_POST['account_name'] !== null){
			$account_name = $_POST['account_name'];
		}
	}
	$account_ceo = '';
	if(isset($_POST['account_ceo']) === true){
		if($_POST['account_ceo'] !== '' && $_POST['account_ceo'] !== null){
			$account_ceo = $_POST['account_ceo'];
		}
	}
	$account_tel = '';
	if(isset($_POST['account_tel']) === true){
		if($_POST['account_tel'] !== '' && $_POST['account_tel'] !== null){
			$account_tel = $_POST['account_tel'];
		}
	}
	$account_number = '';
	if(isset($_POST['account_number']) === true){
		if($_POST['account_number'] !== '' && $_POST['account_number'] !== null){
			$account_number = $_POST['account_number'];
		}
	}
	$account_note = '';
	if(isset($_POST['account_note']) === true){
		if($_POST['account_note'] !== '' && $_POST['account_note'] !== null){
			$account_note = $_POST['account_note'];
		}
	}

	//거래처 테이블 조회 및 가공(회사코드 조건)
	$select_sql = "SELECT sid, account_name, account_tel, account_ceo, account_number ,account_note FROM account_info WHERE company_sid = '".__COMPANY_SID__."'";
	
	// 검색필터 적용(값이 있을 경우 where조건에 sql 붙여주기)
	if($account_name !== ''){
		$select_sql .= " AND account_name like '%$account_name%'";
	}
	if($account_tel !== ''){
		$select_sql .= " AND account_tel like '%$account_tel%'";
	}
	if($account_ceo !== ''){
		$select_sql .= " AND account_ceo like '%$account_ceo%'";
	}
	if($account_number !== ''){
		$select_sql .= " AND account_number like '%$account_number%'";
	}
	if($account_note !== ''){
		$select_sql .= " AND account_note like '%$account_note%'";
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

<h2>거래처 관리</h2>

<article id='search_area'>
	<section id='searchoption_left'>
		<div class='group'>
			<span class='search_field'>거래처명</span>
			<input type='text' id='account_name' placeholder="거래처명" autocomplete="off" value='<?=$account_name?>' />
		</div>
		<div class='group'>
			<span class='search_field'>대표자</span>
			<input type='text' id='account_ceo' placeholder="대표자" autocomplete="off" value='<?=$account_ceo?>' />
		</div>
	</section>

	<section id='searchoption_right'>
		<div class='group'>
			<span class='search_field'>연락처</span>
			<input type='text' id='account_tel' placeholder="연락처" autocomplete="off" value='<?=$account_tel?>' />
		</div>
		<div class='group'>
			<span class='search_field'>사업자번호</span>
			<input type='text' id='account_number' placeholder="사업자번호" autocomplete="off" value='<?=$account_number?>' />
		</div>
	</section>
	
</article>

<div style='clear:both;'></div>
<hr />

<div class='btn_area'>
	<button onclick='search();'>조회</button>
	<button onclick='render("masterdata/account_registration");'>등록</button>
	<button onclick='account_delete();'>선택삭제</button>
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
			<col width='100px' />
			<col width='200px' />
			<col />
		</colgroup>
		<!-- 목록 출력 영역(헤드) -->
		<thead>
			<tr>
				<th><input type='checkbox' id='checked_all' onclick='check_all(this);' /></th>
				<th>수정</th>
				<th>거래처명</th>
				<th>거래처연락처</th>
				<th>거래처 대표</th>
				<th>사업자번호</th>
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
		 			<td><?=$data[$i]['account_name']?></td>
		 			<td><?=$data[$i]['account_tel']?></td>
		 			<td><?=$data[$i]['account_ceo']?></td>
		 			<td><?=$data[$i]['account_number']?></td>
		 			<td><?=$data[$i]['account_note']?></td>
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


