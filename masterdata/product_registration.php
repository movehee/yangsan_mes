<?php 

	//기준정보 품목 등록/수정 페이지

	define('__CORE_TYPE__', 'view');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//신규 일때, 초기값
	$product_sid = '';
	$title = '품목 등록 화면';
	$button = '등록';
	$api_url = 'api_product_insert';

	$product_name = '';
	$product_price = '';
	$product_code = '';
	$product_note = '';
	
	//수정인지 판단
	$is_update = true;
	$query_result = array();
	$company_sid = false;

	//유효성 검사(품목sid)
	if(isset($_POST['product_sid']) === false){
		$is_update = false;
	}
	if($is_update === true && ($_POST['product_sid'] === '' || $_POST['product_sid'] === null)){
		$is_update = false;
	}
	if($is_update === true){
		$product_sid = $_POST['product_sid'];
		
		//넘어온 품목 sid로 품목데이터 조회
		$sql = "SELECT product_name,product_price,product_code,company_sid,product_note from product_info WHERE sid='$product_sid';";
		$query_result = sql($sql);
		$query_result = select_process($query_result);
	}

	if(count($query_result) === 2){
		$company_sid = $query_result[0]['company_sid'];
	}
	if($company_sid !== false && $company_sid !== __COMPANY_SID__){
		$is_update = false;
		$product_sid = '';
	}

	//확정적으로 수정일때
	if($is_update === true){
		$title = '품목 수정 화면';
		$button = '수정';
		$api_url = 'api_product_update';

		$product_name = $query_result[0]['product_name'];
		$product_price = $query_result[0]['product_price'];
		$product_code = $query_result[0]['product_code'];
		$product_note = $query_result[0]['product_note'];
	}
	echo '<script>var product_sid = "'.$product_sid.'";</script>';

?>
<section id="edit">
<h2><?=$title; ?></h2>
	<input type="text" id="product_name" placeholder="품목명" autocomplete="off" value="<?=$product_name?>" />
	<br>
	<input type="text" id="product_price" placeholder="품목 가격" autocomplete="off" value="<?=$product_price?>" >
	<br>
	<input type="text" id="product_code" placeholder="품목 코드" autocomplete="off" value="<?=$product_code?>" >
	<br>
	<input type="text" id="product_note" placeholder="비고" autocomplete="off" value="<?=$product_note?>" >
	<br>
	<button value="등록" onclick="registration('<?=$api_url?>');" ><?=$button; ?></button>
</section>