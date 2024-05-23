<?php

	//자재 등록/수정 페이지

	define('__CORE_TYPE__', 'view');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	
	//신규 일때, 초기값
	$material_sid = '';
	$title = '자재 등록 화면';
	$button = '등록';
	$api_url = 'api_material_insert';

	$material_name = '';
	$material_price = '';
	$material_note = '';
	
	// 수정인지 판단
	$is_update = true;
	$query_result = array();
	$company_sid = false;

	//유효성 검사(자재sid)
	if(isset($_POST['material_sid']) === false){
		$is_update = false;
	}
	if($is_update === true && ($_POST['material_sid'] === '' || $_POST['material_sid'] === null)){
		$is_update = false;
	}
	if($is_update === true){
		$material_sid = $_POST['material_sid'];

		//넘어온 자재 sid로 자재데이터 조회
		$sql = "SELECT material_name,material_price,company_sid, material_note from material_info WHERE sid='$material_sid';";
		$query_result = sql($sql);
		$query_result = select_process($query_result);
	}
	if(count($query_result) === 2){
		$company_sid = $query_result[0]['company_sid'];
	}
	if($company_sid !== false && $company_sid !== __COMPANY_SID__){
		$is_update = false;
		$material_sid = '';
	}

	// 확정적으로 수정임
	if($is_update === true){
		$title = '자재 수정 화면';
		$button = '수정';
		$api_url = 'api_material_update';

		$material_name = $query_result[0]['material_name'];
		$material_price = $query_result[0]['material_price'];
		$material_note = $query_result[0]['material_note'];
	}
	echo '<script>var material_sid = "'.$material_sid.'";</script>';
?>




<section id="edit">
	<h2><?=$title; ?></h2>
	<input type="text" id="material_name" placeholder="자재명" autocomplete="off"
	 value="<?=$material_name?>" />
	<br>
	<input type="text" id="material_price" placeholder="자재단가" autocomplete="off" value="<?=$material_price?>" >
	<br>
	<input type="text" id="material_note" placeholder=" 비고" autocomplete="off" value="<?=$material_note?>" >
	<br>
	<button value="등록" onclick="registration('<?=$api_url?>');" ><?=$button; ?></button>
</section>