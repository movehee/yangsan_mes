// 거래처 조회
function search(page=1){

	let account_name = $('#account_name').val();
	let account_ceo = $('#account_ceo').val();
	let account_tel = $('#account_tel').val();
	let account_number = $('#account_number').val();

	let senddata = new Object();
	senddata.page = page;
	senddata.account_name = account_name;
	senddata.account_ceo = account_ceo;
	senddata.account_tel = account_tel;
	senddata.account_number = account_number;

	//ssr로 다시 거래처 페이지로
	render('masterdata/account', senddata);

	return null;
};

//거래처 업데이트
function update(sid){

		let senddata = new Object();
		senddata.account_sid = sid;

		//거래처(수정 등록 페이지로)
		render('masterdata/account_registration', senddata);

		return null;
	}


//거래처 삭제함수
function account_delete(){

	if(confirm('정말로 삭제하시겠습니까?')){

		//선택된 항목이 없을 경우
		if(checked_sid.length === 0){
			alert('선택된 항목이 없습니다.');
		}

		let senddata = new Object();
	 	senddata.checked_sid = checked_sid;

		// 삭제 프로그램으로 성공하면 거래처페이지로	
		api('api_account_delete' , senddata , function(output){
			if(output.is_success){
				render('masterdata/account');
			}
			alert(output.msg);

		});

	}
}

//검색어 초기화
function resetSearchFields() {
    document.getElementById('account_name').value = ''; 
    document.getElementById('account_ceo').value = ''; 
    document.getElementById('account_tel').value = ''; 
    document.getElementById('account_number').value = ''; 

    search();
}