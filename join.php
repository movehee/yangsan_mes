<?php

	define('__CORE_TYPE__', 'view');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

?>
<script>
	function join(){

		let id = $('#id').val();
		let pw = $('#pw').val();
		let pw_check = $('#pw_check').val();
		let company_number = $('#company_number').val();
		let company_name = $('#company_name').val();
		let company_ceo = $('#company_ceo').val();
		let company_tel = $('#company_tel').val();

		// 입력 확인 검사
		if(id === ''){
			alert('아이디를 입력하세요.');
			return false;
		}
		if(pw === ''){
			alert('비밀번호를 입력하세요.');
			return false;
		}
		if(pw_check === ''){
			alert('비밀번호확인을 입력하세요.');
			return false;
		}
		if(company_number === ''){
			alert('회사 사업자번호를 입력하세요.');
			return false;
		}
		if(company_name === ''){
			alert('회사명을 입력하세요.');
			return false;
		}
		if(company_ceo === ''){
			alert('회사 대표자를 입력하세요.');
			return false;
		}
		if(company_tel === ''){
			alert('회사 연락처를 입력하세요.');
			return false;
		}

		// 글자수 제한 검사
		if(id.length > 12){
			alert('아이디의 글자수가 12자를 초과했습니다.');
			return false;
		}
		if(pw.length > 12){
			alert('비밀번호의 글자수가 12자를 초과했습니다.');
			return false;
		}
		if(pw_check.length > 12){
			alert('비밀번호 확인의 글자수가 12자를 초과했습니다.');
			return false;
		}
		if(company_number.length > 100){
			alert('회사 사업자번호의 글자수가 100자를 초과했습니다.');
			return false;
		}
		if(company_name.length > 100){
			alert('회사명의 글자수가 100자를 초과했습니다.');
			return false;
		}
		if(company_ceo.length > 100){
			alert('회사 대표자의 글자수가 100자를 초과했습니다.');
			return false;
		}
		if(company_tel.length > 100){
			alert('회사 연락처의 글자수가 100자를 초과했습니다.');
			return false;
		}

		//비밀번호 일치 확인
		if(pw !== pw_check){
			alert('비밀번호 확인이 일치하지않습니다.');
			return false;
		}

		let senddata = new Object();
		senddata.id = id;
		senddata.pw = pw;
		senddata.company_number = company_number;
		senddata.company_name = company_name;
		senddata.company_ceo = company_ceo;
		senddata.company_tel = company_tel;

		api('api_join', senddata, function(output){
			if(output.is_success){
				render('index');
			}
			alert(output.msg);
		});
	};
</script>

<div class="container">
	<h2>회원가입</h2>
	<div id="login-form">
		<input type='text' id='id' placeholder="아이디" />
		<br/>
		<input type='password' id='pw' placeholder="비밀번호" />
		<br/>
		<input type='password' id='pw_check' placeholder="비밀번호 확인" />
		<br/>
		<input type='text' id='company_number' placeholder="회사 사업자번호" />
		<br/>
		<input type='text' id='company_name' placeholder="회사명" />
		<br/>
		<input type='text' id='company_ceo' placeholder="회사 대표자" />
		<br/>
		<input type='text' id='company_tel' placeholder="회사 연락처" />
		<br/>
		<input type="submit" onclick="join();" value="회원가입"/>
	</div>
</div>