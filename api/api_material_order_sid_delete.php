<?php

	// 자재발주sid 삭제 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	//발주sid 유효성 검사
	if(isset($_POST['material_order_sid']) === false){
		nowexit(false, '발주sid 정보를 불러올 수 없습니다.');
	}
	if($_POST['material_order_sid'] === null || $_POST['material_order_sid'] === ''){
		nowexit(false, '발주sid 정보가 없습니다.');
	}
	$material_order_sid = $_POST['material_order_sid'];

	//입고테이블에서 삭제
	$input_sql = "DELETE FROM material_stock WHERE parent_sid = '$material_order_sid';";

	$query_result = sql($input_sql);

	if(is_bool($query_result) === false){
		nowexit(false, '입고테이블에서 발주sid 정보 삭제를 실패했습니다.');
	}
	if($query_result === false){
		nowexit(false, '입고테이블에서 발주sid 정보 삭제를 실패했습니다.');
	}

	//발주테이블에서 삭제
	$sql = "DELETE FROM material_order_info WHERE sid='$material_order_sid';";
	$query_result = sql($sql);

	if(is_bool($query_result) === false){
		nowexit(false, '발주 sid 정보 삭제를 실패했습니다.');
	}
	if($query_result === false){
		nowexit(false, '발주 sid 정보 삭제를 실패했습니다.');
	}

	


	nowexit(true , '발주 삭제를 완료했습니다.');
?>