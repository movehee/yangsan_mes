<?php

	define('__CORE_TYPE__', 'view');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

?>

<script>
	function login(){
		let id = document.getElementById('id').value;
		let pw = document.getElementById('pw').value;

		// 입력했는지 확인 검사
		if(id == ''){
			alert('아이디 입력하세요.제발');
			return false;
		}
		if(pw == ''){
			alert('비밀번호 입력하세요.제발');
			return false;
		}

		// 길이 넘어가는지 확인 검사
		if(id.length > 12){
			alert('아이디 12자리 초과되었습니다. 줄이세요.');
			return false;
		}
		if(pw.length > 12){
			alert('비밀번호 12자리 초과되었습니다. 줄이세요.');
			return false;
		}

		senddata = new Object();
		senddata.id = id;
		senddata.pw = pw;

		api('api_login', senddata, function(output){
			if(output.is_success){
				render('main');
			}
			alert(output.msg);
		});

	};
	//엔터 눌렀을때 로그인하는 함수
	function enterLogin(){
		if(window.event.keyCode === 13){
			login();
		}
	}

</script>	
<style>


</style>

<img src="img/ys.jpg" style="width: 200px; height: 200px;  display:block; margin:auto auto;">

<div class="container">
		<h2>로그인</h2>
	<div id="login-form">
		<input type='text' name='id' id='id' placeholder='아이디' /><br>
		<input type='password' name='pw' id='pw' onkeyup="enterLogin()" placeholder="비밀번호" /><br>
		<input type="submit" onclick='login();' value="로그인"><br>

		<a onclick='render("join");'  class="mouse_pointer">회원가입</a>
		<a onclick='render("pw_change");' class="mouse_pointer">비밀번호변경</a>

	</div>

</div>