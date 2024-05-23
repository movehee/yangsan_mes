<?php
	
	//생산계획 등록/수정 페이지

	define('__CORE_TYPE__', 'view');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//신규일 경우 초기값
	$plan_number =  null;
	$title = '생산계획 등록 화면';
	$btn_text = '등록';
	$api_url = 'api_plan_insert';

	//수주선택(order_number), 품목(order_sid), 계획수량, 계획비고
	$order_number = '';
	$order_sid = '';
	$plan_cnt = 0;
	$plan_note = '';

	$is_update = true;

	if(isset($_POST['plan_number'])) {
	    $is_update = true;
	} else {
	    $is_update = false;
	}


	//****************거래처 정보 맵핑*******************	
	//거래처테이블에서 회사코드를 조건으로 거래처sid, 거래처명 가져온 후 매핑
	$select_account_sql = "SELECT sid,account_name from account_info where company_sid= '".__COMPANY_SID__."'; ";

	$query_result = sql($select_account_sql);
	$query_result = select_process($query_result);

	//배열 선언(거래처명)
	$account_name = array();
	$account_list = array();

	//for문으로 거래처sid, 거래처명 배열에 push
	for ($i=0; $i<$query_result['output_cnt']; $i++) { 

		//거래처 sid => 거래처명 맵핑
		$account_name[$query_result[$i]['sid']] = $query_result[$i]['account_name'];

		//거래처sid, 거래처명 배열에 푸쉬
		array_push($account_list, $query_result[$i]);

	}

	$account_list_cnt = count($account_list);
		
	//js로 거래처 정보 넘기기
	echo '<script>var account_list = '.json_encode($account_list).';</script>';

	//****************품목 정보 맵핑*******************	
	//품목 테이블에 회사코드를 조건으로 품목sid, 품목명 가져온 후 맵핑
	$select_product_sql = "SELECT sid,product_name,product_price from product_info where company_sid= '".__COMPANY_SID__."'; ";

	$query_result = sql($select_product_sql);
	$query_result = select_process($query_result);

	//배열 선언하기 (품목sid , 품목명, 품목_list)
	$product_name = array();
	$product_price = array(); 
	$product_list = array();
	$price_data = array();
	$product_sid = array(); //전체 품목sid

	//for문으로 품목sid, 품목명 push하기
	for($i=0; $i<$query_result['output_cnt']; $i++){

		//품목sid 배열에 푸쉬
		array_push($product_sid,$query_result[$i]['sid']);

		//품목sid => 품목 가격
		$product_price[$query_result[$i]['sid']] = $query_result[$i]['product_price'];	
		//품목sid => 품목명
		$product_name[$query_result[$i]['sid']] = $query_result[$i]['product_name'];	
		//조회된 정보들 배열에 푸쉬
		array_push($product_list, $query_result[$i]);

	}
	$product_list_cnt = count($product_list);
	
	//js로 넘겨주기
	echo '<script>var product_list = '.json_encode($product_list).';</script>';
	echo '<script>var price_list = '.json_encode($product_price).';</script>';

	//****************수주 정보 맵핑**********************
	// 특정 회사 코드에 대한 수주 정보 조회
	$select_order_info_sql = "SELECT sid, product_sid, product_cnt, order_number, order_date, account_sid, order_note FROM order_info WHERE company_sid = '".__COMPANY_SID__."';";

	$query_result_order_info = sql($select_order_info_sql);
	$query_result_order_info = select_process($query_result_order_info);

	// 각 주문번호에 대한 수량 및 수주SID 정보를 저장할 배열 
	$order_data = array();
   	$sid_pro_map = array();
   	$pro_sid_map = array();
   	$sid_num_map = array();

	// 모든 주문번호에 대한 정보 확인(수주넘버,수주sid,거래처sid,수주날짜)
	for($i=0; $i<$query_result_order_info['output_cnt']; $i++){

		// 키: 수주sid => 수주번호
		$sid_num_map[$query_result_order_info[$i]['sid']] = $query_result_order_info[$i]['order_number'];
		// 키: 수주sid => 품목sid
		$sid_pro_map[$query_result_order_info[$i]['sid']] =  $query_result_order_info[$i]['product_sid'];
		// 키: 품목sid => 수주번호
		$pro_sid_map[$query_result_order_info[$i]['product_sid']] =  $query_result_order_info[$i]['sid'];

        $temp = array();
        $temp['order_sid'] = $query_result_order_info[$i]['sid'];
        $temp['product_name'] = $product_name[$query_result_order_info[$i]['product_sid']];
        $temp['product_cnt'] = $query_result_order_info[$i]['product_cnt'];
        $temp['order_date'] = $query_result_order_info[$i]['order_date'];
        $temp['order_number'] = $query_result_order_info[$i]['order_number'];
        $temp['account_name'] = $account_name[$query_result_order_info[$i]['account_sid']];
        $temp['order_note'] = $query_result_order_info[$i]['order_note'];

        array_push($order_data, $temp);
    }

    // 수주 sid => 품목sid 맵핑 자료 js 보내기
    echo '<script>var sid_pro_map = '.json_encode($sid_pro_map).';</script>';
    $order_data_cnt = count($order_data);

	//***************수정****************
	//계획번호가 있을 경우
	if($is_update === true){

		$title = '생산계획 수정 화면';
		$btn_text = '수정';
		$api_url = 'api_plan_update';

		$update_plan_number = $_POST['plan_number'];

		//계획번호를 조건으로 계획테이블에서  조회
		$plan_sql = "SELECT order_sid, plan_cnt, plan_note, plan_date FROM plan_info WHERE company_sid = '".__COMPANY_SID__."' AND plan_number = '$update_plan_number' ;";

		$query_result_plan = sql($plan_sql);
		$query_result_plan = select_process($query_result_plan);

		$plan_data = array();

		$up_plan_date = $query_result_plan[0]['plan_date'];

		for($i=0; $i<$query_result_plan['output_cnt']; $i++){

			//정보가공하기(수주sid,계획수량,소계금액,비고)
			$temp = array();
			$temp['order_key'] = $query_result_plan[$i]['order_sid'];
			$temp['plan_cnt'] = $query_result_plan[$i]['plan_cnt'];
			$temp['product_price'] = $product_price[$sid_pro_map[$query_result_plan[$i]['order_sid']]];
			$temp['product_name'] = $product_name[$sid_pro_map[$query_result_plan[$i]['order_sid']]];
			$temp['amount'] = $query_result_plan[$i]['plan_cnt']*$product_price[$sid_pro_map[$query_result_plan[$i]['order_sid']]];
			$temp['plan_note'] = $query_result_plan[$i]['plan_note'];

			//가공한 정보 plan_data에 푸쉬
			array_push($plan_data, $temp);
		}

		$plan_data_cnt = count($plan_data);

		//js로 수정일 경우의 plan_data 보내기
		echo '<script>var plan_data = '.json_encode($plan_data).';</script>';

		//수주번호 구하기
		$order_number = $sid_num_map[$query_result_plan[0]['order_sid']];

		//구한 수주번호로 수주테이블 조회
		$order_sql = "SELECT sid, account_sid, product_sid, product_cnt FROM order_info WHERE company_sid = '".__COMPANY_SID__."' AND order_number = '$order_number';";

		$query_result_order = sql($order_sql);
		$query_result_order = select_process($query_result_order);

		//거래처명 구하기
		$up_account_name = $account_name[$query_result_order[0]['account_sid']];

		$remain_cnt = array();
		$order_product_map = array();
		$product_sid_arr = array();

		for($i=0; $i<$query_result_order['output_cnt']; $i++){

			//품목sid 푸쉬
			array_push($product_sid_arr,$query_result_order[$i]['product_sid']);

			//수주 품목 수량 구하기
			$remain_cnt[$query_result_order[$i]['sid']] = $query_result_order[$i]['product_cnt'];

			//키: 수주sid => 값 :품목 sid
			$order_product_map[$query_result_order[$i]['sid']] = $query_result_order[$i]['product_sid'];

			// 오더sid 매핑 데이터 / key : product_sid
			$order_sid_map[$query_result_order[$i]['product_sid']] = $query_result_order[$i]['sid'];
		}

		//수주넘버에 해당하는 품목 정보 조회
		$product_sql_in = implode("','", $product_sid_arr);
		$product_sql = "SELECT sid, product_name, product_price FROM product_info WHERE company_sid = '".__COMPANY_SID__."' AND sid IN('$product_sql_in');";

		$query_result_product = sql($product_sql);
		$query_result_product = select_process($query_result_product);

		$product_name = array();
		$price_data = array();
		$order_sid_arr = array();

		for($i=0; $i<$query_result_product['output_cnt']; $i++){

			//키: 품목sid => 값 : 품목명 매핑
			$product_name[$query_result_product[$i]['sid']] = $query_result_product[$i]['product_name'];
			//키: 품목sid => 값: 품목 가격 매핑
			$price_data[$order_sid_map[$query_result_product[$i]['sid']]] = (int)$query_result_product[$i]['product_price'];
		} 


		//수주넘버로 조회한 수주sid를 조건으로 계획테이블 조회
		$sql_in = implode("','", $order_sid_arr);
		$plan_order_sql = "SELECT order_sid, plan_cnt FROM plan_info WHERE company_sid = '".__COMPANY_SID__."' AND order_sid IN('$sql_in');";

		$query_result_plan_order = sql($plan_order_sql);
		$query_result_plan_order = select_process($query_result_plan_order);

		for($i=0; $i<$query_result_plan_order['output_cnt']; $i++){

			//존재하지 않는 order_sid로 접근시 PASS
			if(isset($remain_cnt[$query_result_plan_order[$i]['order_sid']]) === false){
				continue;
			}
			//수주테이블 수주수량 - 계획테이블 계획 수량 계산
			$remain_cnt[$query_result_plan_order[$i]['order_sid']] -= $query_result_plan_order[$i]['plan_cnt'];

			//계산 수량이 0 이하가 되면 UNSET
			if($remain_cnt[$query_result_plan_order[$i]['order_sid']] <= 0){
				unset($remain_cnt[$query_result_plan_order[$i]['order_sid']]);
			}			
		}


		//remain_cnt 키 추출 후 배열화
		$order_keys = array_keys($remain_cnt);
		$order_keys_cnt = count($order_keys);
		
		$option_list = array();

		for($i=0; $i<$order_keys_cnt; $i++){

			$temp = array();
			$temp['order_sid'] = $order_keys[$i];
			$temp['product_name'] = $product_name[$sid_pro_map[$order_keys[$i]]];

			//option_list에 temp 푸쉬
			array_push($option_list , $temp);
		}

		$option_list_cnt = count($option_list);

		echo '<script>var option_list = '.json_encode($option_list).';</script>';
		echo '<script>var remain_cnt = '.json_encode($remain_cnt).';</script>';
		echo '<script>var price_data = '.json_encode($price_data).';</script>';

		
  }


?>
<section id='pp'>
<h2><?= $title ?></h2>

<!-- *********수주선택******* -->
<!-- 등록일 경우 -->
<?php
	if($is_update === false){
?>
<select key="order_number" id="order_number" name="order_number" onchange="select_order(this)">
	<option value="" selected disabled>수주선택</option>
	<?php 
    $unique_orders = array(); 

    foreach ($order_data as $order) {
        $order_number = $order['order_number'];
        $account_name = $order['account_name'];
        $unique_orders[$order_number] = $account_name;
    }

    foreach ($unique_orders as $order_number => $account_name) {
	?>
    <option value="<?= $order_number ?>">
        주문번호: <?= $order_number ?>  
        거래처명: <?= $account_name ?>
    </option>
	<?php
    }
	?>
<?php
	}
?>
<!-- 수정일 경우 -->
<?php
	if($is_update === true){
?>
	<input type='hidden' id='up_plan_number' name="update_plan_number" value="<?= $update_plan_number?>">
	거래처 : <?= $up_account_name?>
	생산계획 등록일 : <?= $up_plan_date?>
<?php
	}
?>
</select>

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
        <col width="100px" />
	</colgroup>
	 <!-- 목록 출력 영역(헤드) -->
    <thead>
        <tr>
            <th>품목명</th>
            <th>품목가격</th>
            <th>생산계획수량</th>
            <th>소계금액</th>
            <th>비고</th>
            <th style='display:none;'></th>
        </tr>
    </thead>

    <!-- 목록 바디 영역 -->
    
    	<!-- 수정일 경우 -->
    	<?php if($is_update === true) { 
    		$is_match = false;
    		?>
    	<tbody>
			<?php for($i=0; $i<$plan_data_cnt; $i++){				
			 ?>
				<input type='hidden' id='up_plan_number' name="update_plan_number"  onchange="is_onchange(this,0);" value="<?= $update_plan_number?>">
		        <tr>
			    	<td>
			    		<select key='product_key' id="product_sid" onchange="is_onchange(this,<?=$i?>);">
			    			 <option  value="<?=$plan_data[$i]['order_key']?>" selected><?=$plan_data[$i]['product_name']?></option>
			    		</select>
	    			</td>
			    	<td>
	            	<input key= "product_price" name="product_price" type='number' disabled value='<?=$plan_data[$i]['product_price']?>'/>
		            </td>
		            <td>
		            	<input key='plan_cnt' type='number'  key='product_cnt' onchange="is_onchange(this,0);" value='<?=$plan_data[$i]['plan_cnt']?>'/>
		            </td>
		            <td>
		            	<input key='amount' style="float:right" disabled value='<?=$plan_data[$i]['amount']?>'/>
		            </td>
		            <td>
		            	<input key='plan_note' name="plan_note" type='text' onchange="is_onchange(this,0);"  value='<?=$plan_data[$i]['plan_note']?>'/>
		            </td>
		             <td>
		            	<button onclick='add_row();'>추가</button>
		            </td>
				</tr>
			<?php }?>
		</tbody>
    	<?php }?>
    	
    	<!-- 등록일 경우 -->
    	
    	<?php if($is_update === false) { 
    	?>
    	<tbody>
			<tr>
				<td>
					<select id="order_key" key="order_key" onchange="is_onchange(this,0);">
						<option value="" selected disabled>품목선택</option>
					</select>
				</td>
	            <td>
	            	<input key= "product_price" name="product_price" type='number' onchange="is_onchange(this,0);" disabled value='0'/>
	            </td>
	            <td>
	            	<input key='plan_cnt' type='number'onclick="select_order(<?= $update_plan_number?>)" onchange="is_onchange(this,0);" key='product_cnt' value=''/>
	            </td>
	            <td>
	            	<div style="float:right">0</div>
	            </td>
	            <td>
	            	<input key='plan_note' name="plan_note" type='text' onchange="is_onchange(this,0);"  value=''/>
	            </td>
	            <td>
	            	<button onclick='add_row();'>추가</button>
	            </td>
			</tr>
		</tbody>
    	<?php }?>
    	

		
	

</table>