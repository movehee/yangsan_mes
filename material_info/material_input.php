<?php

   define('__CORE_TYPE__', 'view');
   include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

   // 검색필터 체크
   // 페이지
   $page = 1;

   if(isset($_POST['page']) === true){
      if($_POST['page'] !== '' && $_POST['page'] !== null){
         $page = $_POST['page'];
      }
   }
   // 거래처명
   $is_account_name = '';
   if(isset($_POST['is_account_name']) === true){
     if($_POST['is_account_name'] !== ''&& $_POST['is_account_name'] !== null){
         $is_account_name = $_POST['is_account_name'];
      }
   }
   // 자재명
   $is_material_name = '';
   if(isset($_POST['is_material_name']) === true){
     if($_POST['is_material_name'] !== ''&& $_POST['is_material_name'] !== null){
         $is_material_name = $_POST['is_material_name'];
      }
   }
   // 발주시작범위
   $is_order_startdate =  date('Y-m-d', strtotime('-1 months'));
   if(isset($_POST['is_order_startdate']) === true){
      if($_POST['is_order_startdate'] !== ''&& $_POST['is_order_startdate'] !== null){
         $is_order_startdate = $_POST['is_order_startdate'];
      }
   }
   // 발주종료범위
   $is_order_enddate = date('Y-m-d');
   if(isset($_POST['is_order_enddate']) === true){
      if($_POST['is_order_enddate'] !== ''&& $_POST['is_order_enddate'] !== null){
         $is_order_enddate = $_POST['is_order_enddate'];
      }
   }
    // 입고시작범위
   $is_input_startdate = date('Y-m-d', strtotime('-1 months'));
   if(isset($_POST['is_input_startdate']) === true){
      if($_POST['is_input_startdate'] !== ''&& $_POST['is_input_startdate'] !== null){
         $is_input_startdate = $_POST['is_input_startdate'];
      }
   }
   // 입고종료범위
   $is_input_enddate = date('Y-m-d');
   if(isset($_POST['is_input_enddate']) === true){
      if($_POST['is_input_enddate'] !== ''&& $_POST['is_input_enddate'] !== null){
         $is_input_enddate = $_POST['is_input_enddate'];
      }
   }
   
   // 수량
   $is_change_cnt = '';
   if(isset($_POST['is_change_cnt']) === true){
     if($_POST['is_change_cnt'] !== ''&& $_POST['is_change_cnt'] !== null){
         $is_change_cnt = $_POST['is_change_cnt'];
      }
   }
   // 비고
   $is_note = '';
   if(isset($_POST['is_note']) === true){
      if($_POST['is_note'] !== ''&& $_POST['is_note'] !== null){
         $is_note = $_POST['is_note'];
      }
   }

   //**********조회***********
   //1. 거래처
   $select_account_sql = "SELECT sid, account_name FROM account_info WHERE company_sid='".__COMPANY_SID__."'";

   if($is_account_name !== ''){
      $select_account_sql .= "AND account_name like '%$is_account_name%'";
   }

   $select_account_sql .= ';';

   $query_result_account = sql($select_account_sql);
   $query_result_account = select_process($query_result_account);

   //거래처 sid와 name 매핑
   $account_sid = array();
   $account_name = array();

   for($i = 0; $i < $query_result_account['output_cnt']; $i++){

      array_push($account_sid, $query_result_account[$i]['sid']);
      $account_name[$query_result_account[$i]['sid']] = $query_result_account[$i]['account_name'];
   }

   $account_sid_cnt = count($account_sid);

   //2.자재
   $select_material_sql = "SELECT sid, material_name, material_price FROM material_info WHERE company_sid='".__COMPANY_SID__."'";

   if($is_material_name !== ''){
      $select_material_sql .= "AND material_name like '%$is_material_name%'";
   }

   $select_material_sql .= ';';

   $query_result_material = sql($select_material_sql);
   $query_result_material = select_process($query_result_material);

   $material_sid = array();
   $material_name = array();
   $material_price = array();

   for($i = 0; $i < $query_result_material['output_cnt']; $i++){

      array_push($material_sid, $query_result_material[$i]['sid']);
      
      $material_name[$query_result_material[$i]['sid']] = $query_result_material[$i]['material_name'];
      $material_price[$query_result_material[$i]['sid']] = $query_result_material[$i]['material_price'];

   }
   $material_sid_cnt = count($material_sid);

///////////////////////////////
   // 전체 발주 넘버 조회
   $select_order_num_sql = "SELECT material_order_number FROM material_order_info WHERE company_sid= '".__COMPANY_SID__."'";

   //거래처 검색어가 있을경우
   if($is_account_name !== '' && $account_sid_cnt !== 0){
      $select_account_sid_sql = implode("','", $account_sid);
      $select_order_num_sql .= "AND account_sid IN ('$select_account_sid_sql')";
   }
   //자재 검색어가 있을경우
   if($is_material_name !== '' && $material_sid_cnt !== 0){
      $select_material_sid_sql = implode("','", $material_sid);
      $select_order_num_sql .= "AND material_sid IN ('$select_material_sid_sql')";
   }
   
   //발주날짜가 둘다 선택되었을 경우
   if($is_order_startdate !== '' && $is_order_enddate !== ''){
      $select_order_num_sql .= " AND `material_order_date` BETWEEN '$is_order_startdate 00:00:01' and '$is_order_enddate 23:59:59'";
   }

   $select_order_num_sql .= " GROUP BY material_order_number ORDER BY material_order_number";

   $total_sql = $select_order_num_sql.';';

   $query_result_order_num = sql($total_sql);

   //발주넘버만 가져온 값
   $order_num_arr = select_process($query_result_order_num);

   //전체 발주넘버
   $total_order_num = array();
   
   for($i=0; $i<$order_num_arr['output_cnt']; $i++){

      array_push($total_order_num , $order_num_arr[$i]['material_order_number']);
   }

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

      array_push($limit_number,$select_limit_sql[$i]['material_order_number']);
   }

   //******************************************

//limit 조회결과가 있으면
if($select_limit_sql['output_cnt'] > 0){

   //발주정보조회    
   $select_material_order_sql = "SELECT material_order_number, sid, material_order_date, account_sid, material_sid, material_order_cnt, material_order_note FROM material_order_info WHERE company_sid= '".__COMPANY_SID__."'";

   $order_num_in = implode("','" , $limit_number);

   $select_material_order_sql .= "AND material_order_number IN ('$order_num_in')";

   $select_material_order_sql .= ';';
   
   $query_result_material_order = sql($select_material_order_sql);
   $query_result_material_order = select_process($query_result_material_order);

   //발주정보가공 $total_data[발주number][list][발주sid][list]
   $total_data = array();
   $material_order_sid_num_map = array();
   $sid_material_map = array();
   $material_order_sid = array();

   for($i = 0; $i <$query_result_material_order['output_cnt']; $i++){

      // data[material_order_number] 없으면 선언
      if(isset($total_data[$query_result_material_order[$i]['material_order_number']]) === false){

         $temp = array();
         $temp['material_order_number'] = $query_result_material_order[$i]['material_order_number'];
         $temp['material_order_date'] = $query_result_material_order[$i]['material_order_date'];
         $temp['account_name'] = $account_name[$query_result_material_order[$i]['account_sid']];
         $temp['rowspan'] = 0;
         $temp['list'] = array();

         $total_data[$query_result_material_order[$i]['material_order_number']] = $temp;
      }
      
      // data[material_order_number][list][material_order_sid] 없으면 선언
      if(isset($total_data[$query_result_material_order[$i]['material_order_number']]['list'][$query_result_material_order[$i]['sid']]) === false){

         array_push($material_order_sid,$query_result_material_order[$i]['sid']);

         $temp = array();
         $temp['material_name'] = $material_name[$query_result_material_order[$i]['material_sid']];
         $temp['material_order_cnt'] = $query_result_material_order[$i]['material_order_cnt'];
         $temp['order_note'] = $query_result_material_order[$i]['material_order_note'];
         $temp['order_ammount'] = (int)$material_price[$query_result_material_order[$i]['material_sid']]*$query_result_material_order[$i]['material_order_cnt'];
         $temp['rowspan'] = 0;
         $temp['list'] = array();

         $total_data[$query_result_material_order[$i]['material_order_number']]['list'][$query_result_material_order[$i]['sid']] = $temp;

      }

      //발주sid -> 발주넘버
      $material_order_sid_num_map[$query_result_material_order[$i]['sid']] = $query_result_material_order[$i]['material_order_number'];

      //발주sid -> 자재sid
      $sid_material_map[$query_result_material_order[$i]['sid']] = $query_result_material_order[$i]['material_sid'];

   }

}
//===================================================================

//입고조회(발주sid in)
if(count($material_order_sid) > 0){

   $order_in_sql = implode("','", $material_order_sid);

   //자재 재고 테이블 조회 (조건 : 자재발주 sid)
   $select_stock_info_sql = "SELECT sid, material_sid, parent_sid, type, change_cnt, stock_date, stock_note FROM material_stock WHERE company_sid= '".__COMPANY_SID__."' AND type = 'in' AND parent_sid IN('$order_in_sql') ";

   //수량이 있을시 필터적용
      if($is_change_cnt !== ''){
         $select_stock_info_sql .= "AND change_cnt like '%$is_change_cnt%'";
   }

   //비고가 있을시 필터적용
   if($is_note !== ''){
      $select_stock_info_sql .= "AND stock_note like '%$is_note%'";
   }

   //발주날짜가 둘다 선택되었을 경우
   if($is_input_startdate !== '' && $is_input_enddate !== ''){
      $select_stock_info_sql .= " AND `stock_date` BETWEEN '$is_input_startdate 00:00:01' and '$is_input_enddate 23:59:59'";
   }

   $total_sql = $select_stock_info_sql.';';

   $query_result_stock_info = sql($total_sql);
   $query_result_stock_info = select_process($query_result_stock_info);

   $stock_parent_sid = array();

   //입고테이블 전체 정보
   for($i=0; $i<$query_result_stock_info['output_cnt']; $i++){

      $temp_material_order_sid_num_map = $material_order_sid_num_map[$query_result_stock_info[$i]['parent_sid']];
     
      //data[material_order_number][list][material_order_sid][list][stock_sid] 없을시
      if(isset($total_data[$temp_material_order_sid_num_map]['list'][$query_result_stock_info[$i]['parent_sid']]['list'][$query_result_stock_info[$i]['sid']]) === false){

         $temp = array();
         // $sid_material_map[$query_result_material_order[$i]['sid']] = $query_result_material_order[$i]['material_sid'];
         $temp['input_sid'] = $query_result_stock_info[$i]['sid'];
         $temp['stock_date'] = $query_result_stock_info[$i]['stock_date'];
         $temp['material_name'] = $material_name[$query_result_stock_info[$i]['material_sid']];
         $temp['change_cnt'] = $query_result_stock_info[$i]['change_cnt'];
         $temp['input_ammount'] =  (int)$query_result_stock_info[$i]['change_cnt']*(int)$material_price[$query_result_stock_info[$i]['material_sid']];
         $temp['input_note'] = $query_result_stock_info[$i]['stock_note'];

         array_push($stock_parent_sid,$query_result_stock_info[$i]['parent_sid']);

         $total_data[$temp_material_order_sid_num_map]['list'][$query_result_stock_info[$i]['parent_sid']]['list'][$query_result_stock_info[$i]['sid']] = $temp;

         //rowspan가공
         $total_data[$temp_material_order_sid_num_map]['rowspan']++;

         $total_data[$temp_material_order_sid_num_map]['list'][$query_result_stock_info[$i]['parent_sid']]['rowspan']++;

      }
   }
}





//************************************************************************************************************
//2차 가공(3번의 for문) = 발주, 입고 색인배열 만들기
if(count($total_data) >0){
$total_list = array();

//2차 가공
$total_data_keys = array_keys($total_data);
$total_data_keys_cnt = count($total_data_keys);

if($total_data_keys_cnt > 0){

   // 발주넘버 루프 부분
   for($i=0; $i<$total_data_keys_cnt; $i++){

      // 발주sid 정보가 없는 발주넘버 정보 UNSET
      if($total_data[$total_data_keys[$i]]['rowspan'] === 0){
         unset($total_data[$total_data_keys[$i]]);
         continue;
      }

      // order_number 데이터 생성 여부
      $order_number_push = false;

      $order_sid_list = $total_data[$total_data_keys[$i]]['list'];
      $order_sid_keys = array_keys($order_sid_list);
      $order_sid_keys_cnt = count($order_sid_keys);

         // 발주sid 루프 부분
         for($j=0; $j<$order_sid_keys_cnt; $j++){

            // 발주번호 정보가 없는 발주sid 정보 UNSET
            if($order_sid_list[$order_sid_keys[$j]]['rowspan']===0){
               unset($total_data[$total_data_keys[$i]]['list'][$order_sid_keys[$j]]);
               continue;
            }

            // order_sid 데이터 생성 여부
            $order_sid_push = false;

            $input_sid_list = $order_sid_list[$order_sid_keys[$j]]['list'];
            $input_sid_keys = array_keys($input_sid_list);
            $input_sid_keys_cnt = count($input_sid_keys);

               //입고sid 루프 부분
               for($k=0; $k<$input_sid_keys_cnt; $k++){

                  // list에 PUSH할 임시 배열
                  $temp = array();


                  // 발주번호 정보 생성 안되었다면 생성
                  if($order_number_push === false){

                     $order_number_data = $total_data[$total_data_keys[$i]];
                     $temp['order_number'] = $order_number_data;

                     // 발주 번호 생성되었음으로 변경
                     $order_number_push = true;
                  }

                  // 발주sid 정보 생성 안되었다면 생성
                  if ($order_sid_push === false) {
                     
                     $order_sid_data = $order_sid_list[$order_sid_keys[$j]];
                     $temp['order_sid'] = $order_sid_data;

                     // 발주 sid 생성되었음으로 변경
                     $order_sid_push = true;
                  }

                  // 입고sid 정보 생성
                  $input_sid_data = $input_sid_list[$input_sid_keys[$k]];
                  $temp['input_sid'] = $input_sid_data;
                  // 출력할 색인배열 list에 temp 배열 PUSH
                  array_push($total_list, $temp);

               }//입고sid
         }//발주sid 루프
      }//발주넘버 루프
   }
}
$total_list_cnt = count($total_list);

   //**********페이지처리**********

   //전체 계획번호 개수
   $order_cnt = count($total_order_num);

   //페이지 바 개수
   $pagging_cnt = ceil($order_cnt / 10);

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

<h2>자재입고 관리</h2>

<!-- 검색어 조회 -->
<article id='search_area'>

   <section id='searchoption_left'>

       <div class='group'>
           <span class='search_field'>거래처</span>
           <input type='text' id='is_account_name' placeholder="거래처명" autocomplete="off" value='<?=$is_account_name?>' />
       </div>
       <div class='group'>
           <span class='search_field'>자재명</span>
           <input type='text' id='is_material_name' placeholder="자재명" autocomplete="off" value='<?=$is_material_name?>' />
       </div>
       <div class='group'>
         <span class='search_field'>발주일 </span>
         <input type='date' id='is_order_startdate' autocomplete="off" value='<?=$is_order_startdate?>' />
      </div>
      <div class='group'>
         <span class='search_field'>입고일 </span>
         <input type='date' id='is_input_startdate' autocomplete="off" value='<?=$is_input_startdate?>' />
      </div>
   </section>       

   <section id='searchoption_right'>
      <div class='group'>
           <span class='search_field'>입고 수량</span>
           <input type='number' id='is_change_cnt' placeholder="입고 수량" autocomplete="off" value='<?=$is_change_cnt?>' />
      </div>
      <div class='group'>
           <span class='search_field'>입고 비고</span>
           <input type='text' id='is_note' placeholder="입고 비고" autocomplete="off" value='<?=$is_note?>' />
      </div>
      <div class='group'>
         <span class='search_field'> ~~ </span>
         <input type='date' id='is_order_enddate' autocomplete="off" value='<?=$is_order_enddate?>'>
      </div>
      <div class='group'>
         <span class='search_field'> ~~ </span>
         <input type='date' id='is_input_enddate' autocomplete="off" value='<?=$is_input_enddate?>'>
      </div>
   </section>
</article>

<div style='clear:both;'></div>
<hr>

<!-- 버튼 모음 -->
<div class='btn_area'>
   <button  onclick='search();'>조회</button>
   <button  onclick="render('material_info/material_input_registration')">등록</button>
   <button onclick="resetSearchFields()">검색 조건 초기화</button>
</div>

<div style='clear:both;'></div>

<section id='table_area'>
   <table id='grid_table' >
      <thead>
         <tr>
            <th>거래처명</th>
            <th>발주등록일</th>
            <th>발주자재</th>
            <th>발주수량</th>
            <th>발주소계</th>
            <th>발주비고</th>
            <th>입고등록일</th>
            <th>입고품명</th>
            <th>입고수량</th>
            <th>입고소계</th>
            <th>입고비고</th>
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

                    // 발주번호 정보 있다면 출력
                    if(isset($row['order_number']) === true){
                        ?>
                        <td rowspan='<?=$row['order_number']['rowspan']?>'><?=$row['order_number']['account_name']?></td>
                        <td rowspan='<?=$row['order_number']['rowspan']?>'><?=$row['order_number']['material_order_date']?></td>
                        <?php
                    }

                    // parent_sid 정보 있다면 출력
                    if(isset($row['order_sid']) === true){
                        ?>
                        <td rowspan='<?=$row['order_sid']['rowspan']?>'><?=$row['order_sid']['material_name']?></td>
                        <td rowspan='<?=$row['order_sid']['rowspan']?>'><?=number_format($row['order_sid']['material_order_cnt'])?></td>
                        <td rowspan='<?=$row['order_sid']['rowspan']?>'><?=number_format($row['order_sid']['order_ammount'])?></td>
                        <td rowspan='<?=$row['order_sid']['rowspan']?>'><?=$row['order_sid']['order_note']?></td>
                        <?php
                    }

                    // 계획sid 정보 출력
                    ?>
                    <td><?=$row['input_sid']['stock_date']?></td>
                    <td><?=$row['input_sid']['material_name']?></td>
                    <td><?=$row['input_sid']['change_cnt']?></td>
                    <td><?=number_format($row['input_sid']['input_ammount'])?></td>
                    <td><?=$row['input_sid']['input_note']?></td>
                    <?php

                ?>
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
         <li <?php if((int)$i === (int)$page){ echo 'id="this_page"'; }?> onclick='search(<?=$i?>);'><?=$i?></li>
         <?php
      }
      ?>
      <li onclick='search(<?=$next?>);'>다음</li>
   </ul>

</section>