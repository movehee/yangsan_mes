//기준정보 품목 조회 js

// 품목 조회
function search(page=1){

	let product_name = $('#product_name').val();
	let product_price = $('#product_price').val();
	let product_code = $('#product_code').val();
	let product_note = $('#product_note').val();
	

	let senddata = new Object();
	senddata.page = page;	
	senddata.product_name = product_name;
	senddata.product_price = product_price;
	senddata.product_code = product_code;
	senddata.product_note = product_note;


	render('masterdata/product', senddata);

	return null;
};

//품목 업데이트
function update(sid){

	
		let senddata = new Object();
		senddata.product_sid = sid;
		//품목(수정,등록 페이지로)
		render('masterdata/product_registration', senddata);

		return null;

	
};

//거래처 삭제함수
function product_delete(){

	if(confirm('정말로 삭제하시겠습니까?')){

		//선택된 항목이 없을 경우
		if(checked_sid.length === 0){
			alert('선택된 항목이 없습니다.');
		}

		let senddata = new Object();
	 	senddata.checked_sid = checked_sid;
			
		//삭제 프로그램으로 성공하면 거래처 페이지로 이동
		api('api_product_delete' , senddata , function(output){
			if(output.is_success){
				render('masterdata/product');
			}
			alert(output.msg);
			return null;
		});
	}

}

//검색어 초기화
function resetSearchFields() {
    document.getElementById('product_name').value = ''; 
    document.getElementById('product_price').value = ''; 
    document.getElementById('product_code').value = ''; 
    document.getElementById('product_note').value = ''; 

     search();
}