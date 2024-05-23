<?php 
	//거래처 수정 api

	define('__CORE_TYPE__', 'api');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';


	//유효성 검사(거래처sid )
	if(isset($_POST['account_sid']) === false){
		nowexit(false,'거래처 sid의 값이 없습니다1');
	}
	if($_POST['account_sid'] === '' || $_POST['account_sid'] === null){
		nowexit(false, '거래처 sid의 값이 없습니다2.');
	}

	$account_sid = $_POST['account_sid'];

	//유효성 검사(거래처명 )
	if(isset($_POST['account_name']) === false){
	nowexit(false,'거래처명의 값이 없습니다');
	}
	if($_POST['account_name'] === '' || $_POST['account_name'] === null){
		nowexit(false, '거래처명의 값이 없습니다.');
	}
	$account_name = $_POST['account_name'];

	//유효성 검사(거래처대표 )
	if(isset($_POST['account_ceo']) === false){
	nowexit(false,'거래처 대표자 값이 없습니다');
	}
	if($_POST['account_ceo'] === '' || $_POST['account_ceo'] === null){
		nowexit(false, '거래처 대표자 값이 없습니다.');
	}

	$account_ceo = $_POST['account_ceo'];

	//유효성 검사( 거래처연락처 )
	if(isset($_POST['account_tel']) === false){
	nowexit(false,'거래처 연락처 값이 없습니다');
	}
	if($_POST['account_tel'] === '' || $_POST['account_tel'] === null){
		nowexit(false, '거래처 연락처 값이 없습니다.');
	}

	$account_tel = $_POST['account_tel'];
	
	//유효성 검사(사업자번호)
	if(isset($_POST['account_number']) === false){
	nowexit(false,'사업자번호의 값이 없습니다');
	}
	if($_POST['account_number'] === '' || $_POST['account_number'] === null){
		nowexit(false, '사업자번호의 값이 없습니다.');
	}	
	$account_number = $_POST['account_number'];

	//거래처 비고란
	$account_note = $_POST['account_note'];

	//입력받은 정보를 조건으로(사업자번호와 거래처명,회사코드) 거래처 sid를 검색
	$select_sql = "SELECT sid FROM account_info WHERE 
		company_sid = '".__COMPANY_SID__."' AND (account_name = '$account_name'
			 OR account_number = '$account_number');";

	// 쿼리 날리기
	$query_result = sql($select_sql);
	// 쿼리 수행 결과 배열로 만들기
	$query_result = select_process($query_result);

	$is_duplication = false;

	// 조회된 거래처 sid가 있을 경우
	if($query_result['output_cnt'] > 0){

		//조회된 거래처 sid와 현재 로그인된 회사코드의 거래처sid와 불일치시
		if($query_result[0]['sid'] !== $account_sid ){
			$is_duplication = true;
		}

	}

	// (사업자번호와 거래처명,회사코드) 중복시 중단
	if($is_duplication === true){

		nowexit(false,'중복된 값이 있습니다');
	}

	//거래처 테이블 수정 쿼리 작성
	$update_sql = "UPDATE account_info SET account_name = '$account_name' , account_tel = '$account_tel' , account_ceo ='$account_ceo', account_number = '$account_number', account_note ='$account_note'  WHERE sid = '$account_sid';";

	$query_result = sql($update_sql);

	nowexit(true, '수정이 완료되었습니다.');


	



?>