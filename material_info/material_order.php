<?php 

   //자재 발주 조회 페이지
	
	define('__CORE_TYPE__', 'view');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	// 검색필터 체크
   // 페이지
   $page = 1;
   $start_index = 0;
   if(isset($_POST['page']) === true){
      $page = $_POST['page'];
      $start_index = ($page - 1) * 10;
      if($page === '' || ctype_digit($page) === false){
         $page = 1;
         $start_index = 0;
      }
   }
   // 거래처명
   $account_name = '';
   if(isset($_POST['account_name']) === true){
      if($_POST['account_name'] !== ''&& $_POST['account_name'] !== null){
         $account_name = $_POST['account_name'];
      }
   }
   // 발주시작범위
   $startdate = date('Y-m-d', strtotime('-1 months'));
   if(isset($_POST['startdate']) === true){
      if($_POST['startdate'] !== ''&& $_POST['startdate'] !== null){
         $startdate = $_POST['startdate'];
      }
   }
   // 발주종료범위
   $enddate = date('Y-m-d');
   if(isset($_POST['enddate']) === true){
      if($_POST['enddate'] !== ''&& $_POST['enddate'] !== null){
         $enddate = $_POST['enddate'];
      }
   }
   // 자재명
   $material_name = '';
   if(isset($_POST['material_name']) === true){
      if($_POST['material_name'] !== ''&& $_POST['material_name'] !== null){
         $material_name = $_POST['material_name'];
      }
   }
   // 수량
   $material_cnt = '';
   if(isset($_POST['material_cnt']) === true){
      if($_POST['material_cnt'] !== ''&& $_POST['material_cnt'] !== null){
         $material_cnt = $_POST['material_cnt'];
      }
   }
   // 비고
   $note = '';
   if(isset($_POST['note']) === true){
      if($_POST['note'] !== ''&& $_POST['note'] !== null){
         $note = $_POST['note'];
      }
   }

   //*****************************************************************************************************************
   // 거래처 조회
   $sql = "SELECT sid, account_name FROM account_info WHERE company_sid='".__COMPANY_SID__."'";
   if($account_name !== false){
      $sql .= " AND account_name LIKE '%$account_name%'";
   }
   $sql .= ';';
   $query_result = sql($sql);
   $query_result = select_process($query_result);

   $account_name_map = array();
   $account_sid = array(); 
   for($i=$query_result['output_cnt']-1; $i>=0; $i--){

		// 발주 조회용 sid 배열
		array_push($account_sid, $query_result[$i]['sid']); 

	    // 거래처명 매핑
	    $account_name_map[$query_result[$i]['sid']] = $query_result[$i]['account_name']; 
   }
   //*****************************************************************************************************************
   // 자재 조회
   $sql = "SELECT sid, material_name, material_price FROM material_info WHERE company_sid='".__COMPANY_SID__."'";
   if($material_name !== false){
      $sql .= " AND material_name LIKE '%$material_name%'";
   }
   $sql .= ';';
   $query_result = sql($sql);
   $query_result = select_process($query_result);

   $material_name_map = array();
   $material_price_map = array();
   $material_sid = array();

   for($i=$query_result['output_cnt']-1; $i>=0; $i--){
      array_push($material_sid, $query_result[$i]['sid']); // 발주 조회용 sid 배열
      $material_name_map[$query_result[$i]['sid']] = $query_result[$i]['material_name']; // 자재명 매핑
      $material_price_map[$query_result[$i]['sid']] = $query_result[$i]['material_price']; // 자재가격 매핑
   }

   //*****************************************************************************************************************
   // 발주 번호 조회
   // SQL 만들기
   $sql = "SELECT material_order_number FROM material_order_info WHERE company_sid='".__COMPANY_SID__."' ";

   // 거래처 검색필터 있을경우
   if($account_name !== '' && count($account_sid) !== 0){
      $account_sid = implode("','", $account_sid);
      $sql .= " AND account_sid IN('$account_sid')";
   }
   // 자재 검색필터 있을경우
   if($material_name !== '' && count($material_sid) !== 0){
      $material_sid = implode("','", $material_sid);
      $sql .= " AND material_sid IN('$material_sid')";
   }
    // 날짜 검색필터 있을경우
   if($startdate !== '' && $enddate !== ''){
      $sql .= "AND `material_order_date` BETWEEN '$startdate 00:00:01' and '$enddate 23:59:59'";
   }
   // 수량 필터 있을 경우
   if($material_cnt !== ''){
     
      $sql .= " AND material_order_cnt LIKE'%$material_cnt%'";
   }
   // 비고 필터 있을 경우
   if($note !== ''){
      $sql .= " AND material_order_note LIKE'%$note%'";
   }

   $sql .= " GROUP BY material_order_number ORDER BY material_order_number";

   // 전체 개수 조회용 sql
   $total_sql = $sql.';';

   $total_query_result = sql($total_sql);
   $total_query_result = select_process($total_query_result);

   $total_cnt = $total_query_result['output_cnt']; // 전체 발주번호 개수

   // ***** 페이징 만들기
   // 전체페이지의 마지막
   $last_page = ceil($total_cnt / 10);

   if($last_page === 0){
      $last_page = 1;
   }
   // 시작 페이지
   $start_page = (($page - 1) * 10) + 1;
   if($start_page > $last_page){
      $start_page = 1;
   }

   // 종료 페이지
   $end_page = $start_page + 9;
   if($end_page > $last_page){
      $end_page = $last_page;
   }
   // 이전페이지
   $prev = $start_page - 1;
   if($prev <= 0){
      $prev = 1;
   }
   // 다음 페이지
   $next = $end_page + 1;
   if($next > $last_page){
      $next = $last_page;
   }

   // 페이징 LIMIT
   $sql .= " LIMIT $start_index, 10;";

   // 해당되는 자재발주 번호 조회
   $query_result = sql($sql);
   $query_result = select_process($query_result);

   $order_number = array(); // 자재발주 번호 저장 배열

   for($i=$query_result['output_cnt']-1; $i>=0; $i--){
      array_push($order_number, $query_result[$i]['material_order_number']);
   }

   $data = array();

   // 조회된 자재발주 번호가 있을 때만 수행
   if(count($order_number) > 0){

      $order_number = implode("','", $order_number);

      $sql = "SELECT sid, material_order_number, material_order_date, account_sid, material_sid, material_order_cnt ,material_order_note FROM material_order_info WHERE company_sid='".__COMPANY_SID__."' AND material_order_number IN('$order_number') ORDER BY material_order_number DESC;";
      $query_result = sql($sql);
      $query_result = select_process($query_result);
      
      for($i=0; $i<$query_result['output_cnt']; $i++){

         // data[material_order_number] 초기선언
         if(isset($data[$query_result[$i]['material_order_number']]) === false){

            $temp = array();
            $temp['material_order_number'] = $query_result[$i]['material_order_number'];
            $temp['date'] = $query_result[$i]['material_order_date'];
            $temp['account_name'] = $account_name_map[$query_result[$i]['account_sid']];
            $temp['rowspan'] = 0;
            $temp['amount'] = 0;
            $temp['list'] = array();

            $data[$query_result[$i]['material_order_number']] = $temp;
         }

         $temp = array();
         $temp['material_order_sid'] = $query_result[$i]['sid'];
         $temp['material_name'] = $material_name_map[$query_result[$i]['material_sid']];
         $temp['material_cnt'] = $query_result[$i]['material_order_cnt'];
         $temp['amount'] = (int)$material_price_map[$query_result[$i]['material_sid']] * (int)$query_result[$i]['material_order_cnt'];
         $temp['note'] = $query_result[$i]['material_order_note'];
         if($temp['note'] === null){
            $temp['note'] = '';
         }

         // data[material_order_number][list][material_order_sid]
         $data[$query_result[$i]['material_order_number']]['list'][$query_result[$i]['sid']] = $temp;

         //rowsapn 가공
         $data[$query_result[$i]['material_order_number']]['rowspan']++;

         //총계 가공
         $data[$query_result[$i]['material_order_number']]['amount'] += (int)$data[$query_result[$i]['material_order_number']]['list'][$query_result[$i]['sid']]['amount'];

      }
   }

   $order_number_keys = array_keys($data);
   $order_number_keys_cnt = count($order_number_keys);
   // 1차 가공된 정보가 있을 때에만 수행
   $list = array();
   if($order_number_keys_cnt > 0){

      for($i=0; $i<$order_number_keys_cnt; $i++){

         $order_number_set = false; // 발주번호 정보 세팅 여부

         $order_sid_list = $data[$order_number_keys[$i]]['list']; // 발주sid 리스트
         $order_sid_keys = array_keys($order_sid_list);
         $order_sid_keys_cnt = count($order_sid_keys);
         
         for($j=0; $j<$order_sid_keys_cnt; $j++){

            $temp = array();

            // 발주번호 정보 셋팅
            if($order_number_set === false){

               $order_number_data = $data[$order_number_keys[$i]];
               unset($order_number_data['list']);

               $temp['material_order_number'] = $order_number_data;
               $order_number_set = true;

            }

            // 발주sid 정보 셋팅
            $temp['material_order_sid'] = $order_sid_list[$order_sid_keys[$j]];
            array_push($list, $temp);

         }

      }

   }
   $list_cnt = count($list);


?>

<h2>자재발주 관리</h2>

<!-- 검색어 조회 -->
<article id='search_area'>

   <section id='searchoption_left'>
	    <div class='group'>
	        <span class='search_field'>거래처</span>
	        <input type='text' id='account_name' placeholder="거래처명" autocomplete="off" value='<?=$account_name?>' />
	    </div>
	    <div class='group'>
	        <span class='search_field'>자재명</span>
	        <input type='text' id='material_name' placeholder="자재명" autocomplete="off" value='<?=$material_name?>' />
	    </div>
	    <div class='group'>
		   <span class='search_field'>발주일 </span>
		   <input type='date' id='startdate' autocomplete="off" value='<?=$startdate?>' />
  		</div>
   </section>

   	

	

   <section id='searchoption_right'>
	  	<div class='group'>
		     <span class='search_field'>수량</span>
		     <input type='number' id='material_cnt' placeholder="수량" autocomplete="off" value='<?=$material_cnt?>' />
	 	</div>
	  	<div class='group'>
		     <span class='search_field'>비고</span>
		     <input type='text' id='note' placeholder="비고" autocomplete="off" value='<?=$note?>' />
	  	</div>
	  	<div class='group'>
	  		<span class='search_field'> ~~ </span>
	     	<input type='date' id='enddate' autocomplete="off" value='<?=$enddate?>'>
		</div>
   </section>



</article>

	

<div style='clear:both;'></div>
<hr>

<!-- 버튼 모음 -->
<div class='btn_area'>
	<button onclick='search();'>조회
		</button>
	<button onclick="render('material_info/material_order_registration')">등록</button>
	<button onclick='del_check();'>선택삭제</button>
   <button onclick="resetSearchFields()">검색 조건 초기화</button>
</div>

<div style='clear:both;'></div>

<section id='table_area'>
   <table id='grid_table' border="1">
      <thead>
         <tr>
            <th><input type='checkbox' id='checked_all' onclick='check_all(this);' /></th>
            <th>수정</th>
            <th>발주번호</th>
            <th>거래처명</th>
            <th>날짜</th>
            <th>총계</th>
            <th>자재명</th>
            <th>발주수량</th>
            <th>소계</th>
            <th>비고</th>
            <th>단건삭제</th>
         </tr>
      </thead>
      <tbody>
         <?php
         for($i=0; $i<$list_cnt; $i++){

            $row = $list[$i]; // 임시 row

            ?>
            <tr>
            <?php

            // 자재발주번호 정보 있을 때에만 수행
            if(isset($row['material_order_number']) === true){
               $rowspan = $row['material_order_number']['rowspan'];
               ?>
               <td rowspan='<?=$rowspan?>'><input type='checkbox' id='<?=$row['material_order_number']['material_order_number']?>' name='checked' sid='<?=$row['material_order_number']['material_order_number']?>' onclick='check_one(this);' /></td>
               <td rowspan='<?=$rowspan?>'><button onclick='update("<?=$row['material_order_number']['material_order_number']?>");'>수정</button></td>
               <td rowspan='<?=$rowspan?>'><?=$row['material_order_number']['material_order_number']?></td>
               <td rowspan='<?=$rowspan?>'><?=$row['material_order_number']['account_name']?></td>
               <td rowspan='<?=$rowspan?>'><?=$row['material_order_number']['date']?></td>
               <td rowspan='<?=$rowspan?>'><?=number_format($row['material_order_number']['amount'])?></td>
               <?php

            }


            ?>
               <td><?=$row['material_order_sid']['material_name']?></td>
               <td><?=number_format($row['material_order_sid']['material_cnt'])?></td>
               <td><?=number_format($row['material_order_sid']['amount'])?></td>
               <td><?=$row['material_order_sid']['note']?></td>
               <td><button onclick='delete_sid("<?=$row['material_order_sid']['material_order_sid']?>");'>삭제</button></td>
            </tr>
            <?php

         }
         ?>
      </tbody>
   </table>

    <!--이전 다음 버튼 설정 -->
   <ul id='pagging'>
      <li onclick='search(<?=$prev?>);'>이전</li>
      <?php
      for($i=$start_page; $i<=$end_page; $i++){
         ?>
         <li <?php if((int)$i === (int)$list_cnt){ echo 'id="this_page"'; }?> onclick='search(<?=$i?>);'><?=$i?></li>
         <?php
      }
      ?>
      <li onclick='search(<?=$next?>);'>다음</li>
   </ul>

</section>
