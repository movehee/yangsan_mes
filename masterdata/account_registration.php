<?php

	//거래처 등록/ 수정 페이지

	define('__CORE_TYPE__', 'view');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	
	//신규 일때, 초기값
	$account_sid = '';
	$title = '거래처 등록 화면';
	$button = '등록';
	$api_url = 'api_account_insert';

	$account_name = '';
	$account_tel = '';
	$account_ceo = '';
	$account_number = '';
	$account_note = '';

	// 수정인지 판단
	$is_update = true;
	$query_result = array();
	$company_sid = false;

	// 유효성검사(거래처 sid)
	if(isset($_POST['account_sid']) === false){
		$is_update = false;
	}
	if($is_update === true && ($_POST['account_sid'] === '' || $_POST['account_sid'] === null)){
		$is_update = false;
	}
	if($is_update === true){
		$account_sid = $_POST['account_sid'];

		// 거래처 sid로 거래처데이터 조회
		$sql = "SELECT account_name,account_tel,account_ceo,account_number,company_sid, account_note from account_info WHERE sid='$account_sid';";
		$query_result = sql($sql);
		$query_result = select_process($query_result);
	}

	
	if(count($query_result) === 2){
		$company_sid = $query_result[0]['company_sid'];
	}
	if($company_sid !== false && $company_sid !== __COMPANY_SID__){
		$is_update = false;
		$account_sid = '';
	}

	// 확정적으로 수정임
	if($is_update === true){
		$title = '거래처 수정 화면';
		$button = '수정';
		$api_url = 'api_account_update';

		$account_name = $query_result[0]['account_name'];
		$account_tel = $query_result[0]['account_tel'];
		$account_ceo = $query_result[0]['account_ceo'];
		$account_number = $query_result[0]['account_number'];
		$account_note = $query_result[0]['account_note'];
	}
	echo '<script>var account_sid = "'.$account_sid.'";</script>';
?>




<section id="edit">
	<h2><?=$title; ?></h2>
	<input type="text" id="account_name" placeholder="거래처명" autocomplete="off" value="<?=$account_name?>" />
	<br>
	<input type="text" id="account_tel" placeholder="거래처 연락처" autocomplete="off" value="<?=$account_tel?>" >
	<br>
	<input type="text" id="account_ceo" placeholder="거래처 대표" autocomplete="off" value="<?=$account_ceo?>" >
	<br>
	<input type="text" id="account_number" placeholder="사업자 번호" autocomplete="off" value="<?=$account_number?>" >
	<br>
	<input type="text" id="account_note" placeholder="비고" autocomplete="off" value="<?=$account_note?>" >
	<br>
	<button value="등록" onclick="registration('<?=$api_url?>');" ><?=$button; ?></button>
</section>