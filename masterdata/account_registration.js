

function registration(url){

	if(confirm('정말 등록/수정하시겠습니까?')){

		let account_name = $('#account_name').val();
		let account_tel = $('#account_tel').val();
		let account_ceo = $('#account_ceo ').val();
		let account_number = $('#account_number').val();
		let account_note = $('#account_note').val();
		
		//거래처 유효성 검사(거래처명, 거래처연락처, 거래처대표, 사업자번호)
		if(account_name === ''){
			alert('거래처명 값이 없습니다.');
			return false;
		}
		if(account_name.length > 100){
			alert('거래처명 값의 길이 제한은 100자입니다.');
			return false;
		}
		//
		if(account_tel === ''){
			alert('거래처 연락처 값이 없습니다.');
			return false;
		}
		if(account_tel.length > 100){
			alert('거래처 연락처 값의 길이 제한은 100자입니다.');
			return false;
		}
		//
		if(account_ceo === ''){
			alert('거래처 대표명 값이 없습니다.');
			return false;
		}
		if(account_ceo.length > 100){
			alert('거래처 대표명 값의 길이 제한은 100자입니다.');
			return false;
		}
		//
		if(account_number === ''){
			alert('사업자번호 값이 없습니다.');
			return false;
		}
		if(account_number.length > 100){
			alert('사업자번호 값의 길이 제한은 100자입니다.');
			return false;
		}
		

		// senddata 만들기
		let senddata = new Object();
		senddata.account_name = account_name;
		senddata.account_ceo = account_ceo;
		senddata.account_tel = account_tel;
		senddata.account_number = account_number;
		senddata.account_note = account_note;

		if(account_sid !== ''){
			senddata.account_sid = account_sid;
		}

		//신규면 신규url 수정이면 수정url 
		//성공하면 거래처로 다시
		api(url, senddata, function(output){
			if(output.is_success){
				render('masterdata/account');

				return null;
			}
			alert(output.msg);
			
		});

		return null;
	};

}