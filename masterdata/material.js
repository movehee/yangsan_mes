// 자재 조회 페이지 js

// 자재 조회
function search(page=1){

	let material_name = $('#material_name').val();
	let material_price = $('#material_price').val();

	let senddata = new Object();
	senddata.page = page;
	senddata.material_name = material_name;
	senddata.material_price = material_price;
	
	render('masterdata/material' , senddata);

	return null;

};	

//자재 업데이트
function update(sid){

	

		let senddata = new Object();
		senddata.material_sid = sid;

		render('masterdata/material_registration', senddata);

		return null;
	
};

//자재 삭제함수
function material_delete(){

	if(confirm('정말로 삭제하시겠습니까?')){

		//선택된 항목이 없을 경우
		if(checked_sid.length === 0){
			alert('선택된 항목이 없습니다.');
		}

		let senddata = new Object();
	 	senddata.checked_sid = checked_sid;
			
		api('api_material_delete' , senddata , function(output){
			if(output.is_success){
				render('masterdata/material');
			}
			alert(output.msg);
			return null;
		});

	}


}

//검색어 초기화
function resetSearchFields() {
    document.getElementById('material_name').value = ''; 
    document.getElementById('material_price').value = ''; 

     search();
   
}