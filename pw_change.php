<?php

	define('__CORE_TYPE__', 'view');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

?>

<script>
	
	function find_id(){

		let id = $('#id').val();

		if(id === ''){
			alert('아이디 값이 없습니다.');
			return false;
		}
		if(id.length > 12){
			alert('아이디 길이 제한은 12자입니다.');
			return false;
		}

		let senddata = new Object();
		senddata.id = id;

		api('api_id_exist', senddata, function(output){
			if(output.is_success){
				render('new_pw', senddata);
			}
		});

	};

</script>
<div class="container">
	<h2>아이디 확인 화면</h2>
	<div id="login-form">
		<input type='text' id='id' placeholder="아이디 입력" autocomplete="off" />
		<input type="submit" onclick='find_id();' value="아이디 찾기">
	</dic>
</div>

