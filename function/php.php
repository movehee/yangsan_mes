<?php

	//함수 

	// DB 연결 함수
	function conn(){
		//db host 정보 기본 값
		$host = "localhost";
		$user =	"root";
		$password = "";
		$dbname = "mes";
		$con = new mysqli($host, $user, $password, $dbname);
		
		if(mysqli_error($con)){
			return false;
		}
		return $con;
	}

	// result 반환 함수
	$result = array(); // 로직 종료 후 반환값
	function nowexit($bool, $msg=''){
		global $result;

		// 성공여부의 불린 유효성 검사
		if(is_bool($bool) === false){
			$result['is_success'] = false;
			$result['msg'] = '성공여부 정보의 유효성이 올바르지 않습니다.';
			echo json_encode($result);
			exit();
		}

		// 로직 종료 및 값 반환
		$result['is_success'] = $bool;
		$result['msg'] = $msg;
		echo json_encode($result);
		exit();
	};

	// sql 쿼리 수행 함수
	function sql($sql){
		global $con;

		// DB연결 객체가 없으면 생성
		if(isset($con) === false){
			$con = conn();
		}

		$query_result = mysqli_query($con, $sql); // 쿼리 결과

		// 쿼리 에러 발생시 에러 반환
		if($query_result === false){
			nowexit(false, mysqli_error($con));
		}

		return $query_result;
	}

	// 조회 쿼리 결과 후처리 함수
	function select_process($query_result){

		$data = array(); // 후처리한 결과물을 저장하는 변수
		while($row = mysqli_fetch_array($query_result)){

			$keys = array_keys($row);
			$keys_cnt = count($keys);

			$temp_arr = array(); // row에 대한 정보를 저장할 변수
			for($i=0; $i<$keys_cnt; $i++){

				if(is_numeric($keys[$i]) === true){
					continue;
				}
				$temp_arr[$keys[$i]] = $row[$keys[$i]];

			}
			array_push($data, $temp_arr); // data(색인배열)에 row에대한 정보(temp_arr) PUSH

		}

		$data['output_cnt'] = count($data); // data 배열의 길이

		return $data;
	};

?>

