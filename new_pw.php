<?php

	define('__CORE_TYPE__', 'view');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

	if(isset($_POST['id']) === false){
		nowexit(false, '아이디 값이 없습니다.');
	}
	if($_POST['id'] === '' || $_POST['id'] === null){
		nowexit(false, '아이디 값이 없습니다.');
	}
	$id = $_POST['id'];

	echo '<script>var id = "'.$id.'";</script>';

?>

<script>
	
	function pw_change(){

		let pw = $('#pw').val();
		let pw_check = $('#pw_check').val();

		if(pw === ''){
			alert('새 비밀번호 값이 없습니다.');
			return false;
		}
		if(pw.length > 12){
			alert('새 비밀번호 값의 길이 제한은 12자입니다.');
			return false;
		}
		if(pw_check === ''){
			alert('새 비밀번호 확인 값이 없습니다.');
			return false;
		}
		if(pw_check.length > 12){
			alert('새 비밀번호 확인 값의 길이 제한은 12자입니다.');
			return false;
		}

		if(pw !== pw_check){
			alert('새 비밀번호 확인의 값이 일치하지 않습니다.');
			return false;
		}

		let senddata = new Object();
		senddata.id = id;
		senddata.pw = pw;

		api('api_pw_change', senddata, function(output){
			if(output.is_success){
				render('index');
			}
			alert(output.msg);
		});

		return null;
	};

</script>
<div class="container">
<h2>새 비밀번호 입력 화면</h2>
	<div id="login-form">
		<input type='password' id='pw' placeholder="새 비밀번호 입력" autocomplete="off" />
		<input type='password' id='pw_check' placeholder="새 비밀번호 확인 입력" autocomplete="off" />

		<input type="submit" onclick='pw_change();' value="비밀번호 변경">
	</dic>
</div>