//bom 조회
function search(page=1) {
	
	let product_name = $('#product_name').val();
	let material_name = $('#material_name').val();
	let material_cnt = $('#material_cnt').val();
	let note = $('#note').val();
	
	let senddata = new Object();
	senddata.page = page;
	senddata.product_name = product_name;
	senddata.material_name = material_name;
	senddata.material_cnt = material_cnt;
	senddata.note = note;

	render('masterdata/bom', senddata);

	return null;
}

//bom 수정
function update(sid) {

	

		let senddata = new Object();

		senddata.product_sid = sid;

		render('masterdata/bom_registration', senddata);

		return null;

	
}


//bom 선택삭제
function bom_delete(){

	if(confirm('정말로 삭제하시겠습니까?')){

		//선택된 항목이 없을 경우
		if(checked_sid.length === 0){
			alert('선택된 항목이 없습니다.');
		}

		let senddata = new Object();
	 	senddata.checked_sid = checked_sid;

		// 삭제 프로그램으로 성공하면 bom페이지로	
		api('api_bom_delete' , senddata , function(output){
			if(output.is_success){
				render('masterdata/bom');
			}
			alert(output.msg);

		});

	}

}

//bom 자재삭제버튼
function deleteRow(sid){

	if(confirm('정말로 삭제하시겠습니까?')){

		let senddata = new Object();

		senddata.bom_sid = sid;

		// 삭제 프로그램으로 성공하면 bom페이지로	
		api('api_bom_row_delete' , senddata , function(output){
			if(output.is_success){
				render('masterdata/bom');
			}
			alert(output.msg);

		});

	}
}

//검색어 초기화
function resetSearchFields() {
    document.getElementById('product_name').value = ''; 
    document.getElementById('material_cnt').value = ''; 
    document.getElementById('material_name').value = ''; 
    document.getElementById('note').value = ''; 

     search();
}


