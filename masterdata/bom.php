<?php

	//bom 조회 페이지

	define('__CORE_TYPE__', 'view');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//유효성 검사(페이지)
	$page = 1;
	if(isset($_POST['page']) === true){
		if($_POST['page'] !== '' && $_POST['page'] !== null){
			$page = $_POST['page'];
		}
	}

	// 검색어 유효성 검사(품목명,자재명,자재수량, bom비고)

	$is_product_name = '';
	if(isset($_POST['product_name']) === true){
		if($_POST['product_name'] !== '' && $_POST['product_name'] !== null){
			$is_product_name = $_POST['product_name'];
			}
	}
	$is_material_name = '';
	if(isset($_POST['material_name']) === true){
		if($_POST['material_name'] !== '' && $_POST['material_name'] !== null){
			$is_material_name = $_POST['material_name'];
			}
	} 
	$material_cnt = '';
    if(isset($_POST['material_cnt'])===true){
        if($_POST['material_cnt'] !== '' && $_POST['material_cnt'] !== null){
            $material_cnt = $_POST['material_cnt'];
        }
    }
    $note = '';
    if(isset($_POST['note'])===true){
       $note = $_POST['note'];
    }
	
     //bom 조회 가능여부 판단
    $can_search = true;

	// 품목 정보 조회 (조건 : 회사코드)
	$select_product_sql = "SELECT sid,product_name from product_info where company_sid= '".__COMPANY_SID__."' ";

	// 검색필터 적용(값이 있을 경우 where조건에 sql 붙여주기)
	if($is_product_name !== ''){
		$select_product_sql .= " AND product_name like '%$is_product_name%'";
	}
	$select_product_sql .= ';';

	$query_result = sql($select_product_sql);
	$query_result = select_process($query_result);

	//배열 선언하기 (품목sid , 품목명)
	$product_sid = array();
	$product_name = array();

	//for문으로 품목sid, 품목명 push하기
	for($i=0; $i<$query_result['output_cnt']; $i++){

		array_push($product_sid, $query_result[$i]['sid']);

		$product_name[$query_result[$i]['sid']] = $query_result[$i]['product_name'];		
	}

	//품목 sid의 조회 결과가 없으면 bom조회 pass
	if(count($product_sid) === 0 ){
        $can_search = false;
    }

	// 자재 정보 조회(조건 : 회사코드)
	$select_material_sql = "SELECT sid,material_name from material_info where company_sid= '".__COMPANY_SID__."' ";

	// 검색필터 적용(값이 있을 경우 where조건에 sql 붙여주기)
	if($is_material_name !== ''){
		$select_material_sql .= " AND material_name like '%$is_material_name%'";
	}
	$select_material_sql .=';';

	$query_result = sql($select_material_sql);
	$query_result = select_process($query_result);

	
	//배열 선언하기 (자재sid , 자재명)
	$material_sid = array();
	$material_name = array();

	//for문으로 자재sid, 자재명 push하기
	for($i=0; $i<$query_result['output_cnt']; $i++){

		array_push($material_sid, $query_result[$i]['sid']);

		$material_name[$query_result[$i]['sid']] = $query_result[$i]['material_name'];		

	}
	//자재 sid가 없으면 bom조회 pass
	if(count($material_sid) === 0 ){
        $can_search = false;
    }

	$bom_data = array();

    if($can_search === true){

	//bom 정보 조회(product_sid, material_sid , ea(수량), 비고)
	$select_bom_sql = "SELECT sid, product_sid, material_sid, ea ,bom_note from bom_info where company_sid= '".__COMPANY_SID__."' ";

	//자재명 겸색어가 있다면 material_sid를 in으로 검색
	if($is_material_name !== ''){
            
            $material_in_sql = implode("','", $material_sid);

            $select_bom_sql .= "AND material_sid IN ('$material_in_sql')";
            
    }
    
    // 수량이 있으면 검색필터 적용(값이 있을 경우 where조건에 sql 붙여주기)
	if($material_cnt !== ''){
		$select_bom_sql .= " AND ea like '%$material_cnt%'";
	}

	// 비고이 있으면 검색필터 적용(값이 있을 경우 where조건에 sql 붙여주기)
	if($note !== ''){
		$select_bom_sql .= " AND bom_note like '%$note%'";
	}
	
    $select_bom_sql .=';';

	$query_result = sql($select_bom_sql);
	$query_result = select_process($query_result);

	for($i=0; $i<$query_result['output_cnt']; $i++){

		// 자재명 검색 후 일치 품목sid가 있는지 확인
		 if(in_array($query_result[$i]['product_sid'], $product_sid) === false){
	            continue;
        }

		$temp = array();
		// 자재명, 수량, 비고, bom_sid
        $temp['material_name'] = $material_name[$query_result[$i]['material_sid']];
        $temp['ea'] = $query_result[$i]['ea'];
        $temp['bom_note'] = $query_result[$i]['bom_note'];
        $temp['bom_sid'] = $query_result[$i]['sid'];

        if(isset($bom_data[$query_result[$i]['product_sid']]) === false){
            $bom_data[$query_result[$i]['product_sid']] = array();
        }

        array_push($bom_data[$query_result[$i]['product_sid']], $temp);
    }
    //if($can_search === true) 종료

}


	//data 2차 후처리
	$bom_list = array(); //bom 정보(색인)

	$data_keys = array_keys($bom_data);

	$data_keys_cnt = count($data_keys);

	if($data_keys_cnt > 0){
		for($i=0; $i<$data_keys_cnt; $i++){

			$temp = array()	;

			$temp['product_sid'] = $data_keys[$i];
		    $temp['Product_name'] = $product_name[$data_keys[$i]];//품목명
		    $temp['material_data'] = $bom_data[$data_keys[$i]]; // 자재명,수량,비고,bom_sid
		    $temp['rowspan'] = count($temp['material_data']); // rowspan갯수

		    array_push($bom_list, $temp);
		   
		}
	}

	//****** 페이징 처리 ********

	//현재페이지
	$cur_page = ($page -1)*10;

	// 전체 갯수
	$bom_list_cnt = count($bom_list);

	//페이지바 갯수(페이징 단위)
	$pagging_cnt = ceil($bom_list_cnt/10);

	//시작 페이지
	$start_page = (floor($page/10)*10)+1;

	//마지막 페이지(시작페이지 + 9)
	$end_page = $start_page + 9;

	// 만약 마지막페이지가 페이지갯수보다 클 경우 마지막페이지 = 페이지 갯수
	if($end_page > $pagging_cnt){
		$end_page = $pagging_cnt;
	}

	$arr_page =  array_slice($bom_list, $cur_page, 10);
	$arr_page_cnt = count($arr_page);

   
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

<h2>BOM 관리</h2>

<!-- 검색어 조회 -->
	<article id='search_area'>
	<section id='searchoption_left'>
		<div class='group'>
			<span class='search_field'>품목명</span>
			<input type='text' id='product_name' placeholder="품목명" autocomplete="off" value='<?= $is_product_name?>' />
		</div>
		<div class='group'>
         <span class='search_field'>수량</span>
         <input type='number' id='material_cnt' placeholder="수량" autocomplete="off" value='<?=$material_cnt?>' />
      </div>
	</section>

	<section id='searchoption_right'>
		<div class='group'>
			<span class='search_field'>자재명</span>
			<input type='text' id='material_name' placeholder="자재명" autocomplete="off" value='<?= $is_material_name?>' />
		</div>
		<div class='group'>
         <span class='search_field'>비고</span>
         <input type='text' id='note' placeholder="비고" autocomplete="off" value='<?=$note?>' />
      </div>
	</section>
	
</article>

<div style='clear:both;'></div>
<hr />

<!-- 버튼 모음 -->
<div class='btn_area'>
	<button onclick='search();'>조회</button>
	<button onclick='render("masterdata/bom_registration");'>등록</button>
	<button onclick='bom_delete();'>선택삭제</button>
	<button onclick="resetSearchFields()">검색 조건 초기화</button>
</div>

<div style='clear:both;'></div>

<!-- table -->
	<section id='table_area'>

	<table id='grid_table'>
		<colgroup>
			<col width='50px' />
			<col width='100px' />
			<col width='100px' />
			<col width='50px' />
			<col width='100px' />
			<col width='50px' />
			<col width='100px' />
		</colgroup>
		<!-- 목록 출력 영역(헤드) -->
		<thead>
			<tr>
				<th><input type='checkbox' id='checked_all' onclick='check_all(this);' /></th>
				<th>품목명</th>
				<th>자재명</th>
				<th>수량</th>
				<th>비고</th>
				<th>삭제</th>
				<th>수정</th>
			</tr>
		</thead>

	   <!-- 목록 바디 영역 -->
			<tbody>

			    <?php //품목명 출력
			    
				    for ($i = 0; $i <$arr_page_cnt; $i++) {
				        ?>
				        <tr>
				            <td rowspan="<?=$arr_page[$i]['rowspan']?>">
				            	<input type='checkbox' id='<?=$arr_page[$i]['product_sid']?>' name='checked' sid='<?=$arr_page[$i]['product_sid']?>' onclick='check_one(this);' /></td>
				            <td rowspan="<?=$arr_page[$i]['rowspan']?>"><?=$arr_page[$i]['Product_name']?></td>
				            <?php //자재명, 수량, 비고 
				            for ($j = 0; $j < $arr_page[$i]['rowspan']; $j++) {
				                ?>
				                <td><?=$arr_page[$i]['material_data'][$j]['material_name']?></td>
				                <td><?=number_format($arr_page[$i]['material_data'][$j]['ea'])?></td>
				                <td><?=$arr_page[$i]['material_data'][$j]['bom_note']?></td>
				                <td ><button onclick='deleteRow("<?=$arr_page[$i]['material_data'][$j]['bom_sid']?>");'>삭제</button></td>
				                <?php
				                if ($j === 0) {
				                    ?>
				                    <td rowspan="<?=$arr_page[$i]['rowspan']?>"><button onclick='update("<?=$arr_page[$i]['product_sid']?>");'>수정</button></td>
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

	<!-- pagging -->
		<ul id='pagging'>
		<li onclick='search(<?=$prev?>);'>이전</li>
		<?php
		for($i=$start_page; $i<=$end_page; $i++){
			?>
			<li <?php if((int)$i === (int)$page){ echo 'id="this_page"'; } ?> onclick='search(<?=$i?>);'><?=$i?> </li>
			<?php
		}
		?>
		<li onclick='search(<?=$next?>);'>다음</li>
	</ul>

</section>
	

	






