<?php

	// 수주 등록/수정 페이지
	define('__CORE_TYPE__', 'view');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	// 신규일 경우 초기값
	// order_number는 null
	$order_number = null;
	$title = '수주 등록 화면';
	$btn_text = '등록';
	$api_url = 'api_order_insert';

	// 거래처sid, 품목sid, 품목수량, 비고 기본값
	$account_sid = '';
	$product_sid = '';
	$product_cnt = 0;
	$order_note = '';

	$is_update = false; // 업데이트 초기값
	$query_result_order_data = array();
	$company_sid = false;

	// 유효성 검사(수주번호)
	if (isset($_POST['order_number']) && $_POST['order_number'] !== '') {
	    $is_update = true;
	    $update_order_number = $_POST['order_number'];
	} else {
	    $is_update = false;
	}

	// 수주번호가 있을 경우
	if ($is_update === true) {
	    // 수주번호를 조건으로 조회
	    $select_sql = "SELECT company_sid, account_sid FROM order_info WHERE order_number='$update_order_number'";
	    $query_result_order_data = sql($select_sql);
	    $query_result_order_data = select_process($query_result_order_data);
	}

	// 조회결과가 있을 시 
	if (count($query_result_order_data) > 0) {
	    $company_sid = $query_result_order_data[0]['company_sid']; // 회사코드
	    $up_account_sid = $query_result_order_data[0]['account_sid']; // 거래처 sid
	} 

	if ($company_sid !== false && $company_sid !== __COMPANY_SID__) {
	    $is_update = false;
	    $update_order_number = '';
	}

	// ***************수정*****************
	if ($is_update === true) {
	    $title = '수주 수정 화면';
	    $btn_text = '수정';
	    $api_url = 'api_order_update';

	    // 거래처 테이블에서 거래처명 가져온 후 매핑
	    $search_account_sql = "SELECT account_name FROM account_info WHERE sid = '$up_account_sid' AND company_sid = '".__COMPANY_SID__."'";
	    $query_result_account = sql($search_account_sql);
	    $query_result_account = select_process($query_result_account);
	    $up_account_name = $query_result_account[0]['account_name']; // 수정할 거래처명        

	    // 품목 테이블에서 품목sid, 품목명 가져온 후 매핑
	    $search_product_sql = "SELECT sid, product_name, product_price FROM product_info WHERE company_sid = '".__COMPANY_SID__."'";
	    $query_result_product = sql($search_product_sql);
	    $query_result_product = select_process($query_result_product);

	    $product_price_map = array();
	    for ($i = 0; $i < $query_result_product['output_cnt']; $i++) {
	        // 품목sid => 품목가격
	        $product_price_map[$query_result_product[$i]['sid']] = $query_result_product[$i]['product_price'];
	    }

	    // 수주번호와 회사 sid로 수주조회(품목sid, 품목수량, 비고)
	    $search_order_sql = "SELECT sid, account_sid, product_sid, product_cnt, order_note FROM order_info WHERE order_number = '$update_order_number' AND company_sid = '".__COMPANY_SID__."'";
	    $query_result_order = sql($search_order_sql);
	    $query_result_order = select_process($query_result_order);

	    $order_data = array();
	    for ($i = 0; $i < $query_result_order['output_cnt']; $i++) {
	        $temp = array();
	        $temp['product_key'] = $query_result_order[$i]['product_sid'];
	        $temp['product_price'] = $product_price_map[$query_result_order[$i]['product_sid']];
	        $temp['product_cnt'] = $query_result_order[$i]['product_cnt'];
	        $temp['amount'] = $product_price_map[$query_result_order[$i]['product_sid']] * $query_result_order[$i]['product_cnt'];
	        $temp['order_note'] = $query_result_order[$i]['order_note'];
	        array_push($order_data, $temp);
	    }

	    // js로 수주 정보 보내기
	    echo '<script>var order_data = '.json_encode($order_data).';</script>';
	}

	// ****************등록일 경우*******************    
	// 거래처테이블에서 회사코드를 조건으로 거래처sid, 거래처명 가져온 후 매핑
	$select_account_sql = "SELECT sid, account_name FROM account_info WHERE company_sid= '".__COMPANY_SID__."'";
	$query_result = sql($select_account_sql);
	$query_result = select_process($query_result);

	// 배열 선언(거래처sid, 거래처명, 거래처_list)
	$account_sid = array();
	$account_name = array();
	$account_list = array();

	// for문으로 거래처sid, 거래처명 배열에 push
	for ($i = 0; $i < $query_result['output_cnt']; $i++) { 
	    // 거래처 sid 배열에 푸쉬
	    array_push($account_sid, $query_result[$i]['sid']);

	    // 거래처sid => 거래처명
	    $account_name[$query_result[$i]['sid']] = $query_result[$i]['account_name'];
	    array_push($account_list, $query_result[$i]);
	}
	$account_list_cnt = count($account_list);
	echo '<script>var account_list = '.json_encode($account_list).';</script>';

	// 품목 테이블에 회사코드를 조건으로 품목sid, 품목명 가져온 후 맵핑
	$select_product_sql = "SELECT sid, product_name, product_price FROM product_info WHERE company_sid= '".__COMPANY_SID__."'";
	$query_result = sql($select_product_sql);
	$query_result = select_process($query_result);

	// 배열 선언하기 (품목sid , 품목명, 품목_list)
	$product_name = array();
	$product_price = array(); 
	$product_list = array();

	// for문으로 품목sid, 품목명 push하기
	for ($i = 0; $i < $query_result['output_cnt']; $i++) {
	    // 품목sid => 품목가격
	    $product_price[$query_result[$i]['sid']] = $query_result[$i]['product_price'];

	    // 품목sid => 품목명 
	    $product_name[$query_result[$i]['sid']] = $query_result[$i]['product_name'];
	    array_push($product_list, $query_result[$i]);
	}
	$product_list_cnt = count($product_list);
	echo '<script>var product_list = '.json_encode($product_list).';</script>';
	echo '<script>var price_list = '.json_encode($product_price).';</script>';
?>
<section id='pp'>
<h2><?=$title?></h2>


<!-- *********거래처선택******* -->
<!-- 등록일 경우 -->
<?php if($is_update === false) { ?>

<select key='account_key'  onchange="is_onchange(this,0);">
	<option value="" selected disabled>거래처선택</option>
    <?php
    for ($i = 0; $i < $account_list_cnt; $i++) {
    ?>
    <option id="account_sid" value="<?=$account_list[$i]['sid']; ?>"><?=$account_list[$i]['account_name']; ?></option>
    <?php
    }
    ?>
</select>
<?php } ?>

<!-- 수정일 경우 -->
<?php if($is_update === true) { ?>

    <input type='hidden' id='order_number' name="update_order_number" value="<?= $update_order_number?>">
    <input type='hidden' id='up_account_sid'  value="<?= $up_account_sid?>"> 
    
    <h2><?=$up_account_name?></h2>
    
<?php } ?>


<button onclick="registration('<?=$api_url?>');"><?=$btn_text?></button>

</section>
<hr>

<table id="grid_table">
	<colgroup>
        <col width='200px' />
        <col width='100px' />
        <col width="100px" />
        <col width="100px" />
        <col width="100px" />
        <col width="100px" />
	</colgroup>
	 <!-- 목록 출력 영역(헤드) -->
    <thead>
        <tr>
            <th>품목명</th>
            <th>품목가격</th>
            <th>품목수량</th>
            <th>소계금액</th>
            <th>비고</th>
            <th style='display:none;'></th>
        </tr>
    </thead>

    <!-- 목록 바디 영역 -->
    <tbody>
	<!-- 수정일 경우 -->
	<?php if($is_update === true) { 
    	?>
       
        <?php
        for ($i = 0; $i < $query_result_order['output_cnt']; $i++) {
        	$product_price_val = $product_price[$query_result_order[$i]['product_sid']]; //품목가격
        	$product_cnt_val = $query_result_order[$i]['product_cnt']; //품목수량
        	$ammount = $product_price_val*$product_cnt_val; //소계금액
        	$product_cnt_total += $query_result_order[$i]['product_cnt']; //총 수량
        	$ammount_total += $product_price_val*$product_cnt_val; //총계

        ?>
        <tr>
	    	<td>
	    		<select key='product_key' id="product_sid" onchange="is_onchange(this,<?=$i?>);">
	    			 <option  value="" selected disabled>품목선택</option>
				        <?php
				        for ($j = 0; $j < $product_list_cnt; $j++) {
				        	$selected = '';
				        	 $is_match = $query_result_order[$i]['product_sid'] === $query_result_product[$j]['sid'];
                            $order_product_sid = $query_result_order[$i]['product_sid'];
				       		if ($is_match === true) {
                        ?>
                            <option selected value="<?=$query_result_order[$i]['product_sid']?>"><?=$query_result_product[$j]['product_name']?></option>
                            <?php 
                            } else { 
                            ?>
                            <option value="<?=$query_result_product[$j]['sid']?>"><?=$query_result_product[$j]['product_name']?></option>
                            <?php
                            }
                        } 
                        ?>
	    		</select>
	    	</td>
	    	<td>
	            <input key='product_price' name="product_price" type='number' onchange="is_onchange(this,0);" disabled value='<?=$product_price_val?>'/>
	        </td>
	    	<td>
	            <input key='product_cnt' name="product_cnt" type='number' onchange="is_onchange(this,0);"  value='<?=$product_cnt_val?>'/>
	        </td>
	        <td key='ammount' class="ammount" name="ammount"><div style="float:right"><?= $ammount ?></div></td>
	        <td>
	            <input key='order_note' name="order_note" type='text' onchange="is_onchange(this,0);"  value='<?=$query_result_order[$i]['order_note']?>'/>
	        </td>
	        <td>
	            <button onclick='row_add();'>추가</button>
	        </td>
	    </tr>

	   


	
	 <?php 
        }
        } else { //등록일 경우

    	?>
	    <tr>
	    	<td>
	    		<select key='product_key' id="product_sid" onchange="is_onchange(this,0);">
	    			 <option  value="" selected disabled>품목선택</option>
				        <?php
				        for ($i = 0; $i < $product_list_cnt; $i++) {
				        ?>
				            <option value="<?=$product_list[$i]['sid']?>"><?=$product_list[$i]['product_name'] ?></option>
				        <?php
				        }
				        ?>
	    		</select>
	    	</td>
	    	<td>
	            <input name="product_price" type='number' onchange="is_onchange(this,0);" disabled value='0'/>
	        </td>
	    	<td>
	            <input name="product_cnt" type='number' onchange="is_onchange(this,0);"  value='0'/>
	        </td>
	        <td class="ammount"><div style="float:right">0</div></td>
	        <td>
	            <input key='order_note' name="order_note" type='text' onchange="is_onchange(this,0);"  value=''/>
	        </td>
	        <td>
	            <button onclick='row_add();'>추가</button>
	        </td>
	    </tr>

	 
    	<?php
	 	} //esle 닫힘
	 	?>

    	 <?php if($is_update === true) { ?>
	  	<!-- 수정 총계 -->
	    <tr>
	    	<td>총계</td>
	    	<td>총수량</td>
	    	<td><div style="float:right"><?=$product_cnt_total?></div></td>
	    	<td>총계금액</td>	
	    	<td><div style="float:right"><?= $ammount_total?></div></td>
	    </tr>
	    <?php } ?>

	    <?php if($is_update === false) { ?>
       	<!-- 등록 총계 -->
	    <tr>
	    	<td>총계</td>
	    	<td>총수량</td>
	    	<td><div style="float:right">0</div></td>
	    	<td>총계금액</td>	
	    	<td><div style="float:right">0</div></td>
	    </tr>
	   	<?php } ?>

	</tbody>
</table>