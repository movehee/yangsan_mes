// 자재 발주 조회 js 

// 조회 함수
function search(page=1){

	let account_name = $('#account_name').val();
	let startdate = $('#startdate').val();
	let enddate = $('#enddate').val();
	let material_name = $('#material_name').val();
	let material_cnt = $('#material_cnt').val();
	let note = $('#note').val();

	let senddata = new Object();
	senddata.page = page;
	senddata.account_name = account_name;
	senddata.startdate = startdate;
	senddata.enddate = enddate;
	senddata.material_name = material_name;
	senddata.material_cnt = material_cnt;
	senddata.note = note;

	render('material_info/material_order', senddata);

	return null;
};

// 수정화면 이동
function update(material_order_number){

	

		let senddata = new Object();
		senddata.material_order_number = material_order_number;

		render('material_info/material_order_registration', senddata);

		return null;
	
};

// 체크항목 삭제
function del_check(){

	if(confirm('정말로 삭제하시겠습니까?')){

		if(checked_sid.length === 0){
			alert('선택된 발주정보가 없습니다.');
			return null;
		}

		let senddata = new Object();
		senddata.checked_number = checked_sid;

		api('api_material_order_number_delete', senddata, function(output){
			if(output.is_success){
				search();
			}
		});

	}

};

// 단건삭제
function delete_sid(sid){

	if(confirm('정말로 삭제하시겠습니까?')){

		let senddata = new Object();
		senddata.material_order_sid = sid;

		api('api_material_order_sid_delete', senddata, function(output){
			if(output.is_success){
				search();
			}
		});

		return null;
	}
};

//검색어 초기화
function resetSearchFields() {
    document.getElementById('account_name').value = ''; 
    document.getElementById('material_name').value = ''; 
    document.getElementById('startdate').value =''; 
    document.getElementById('material_cnt').value = ''; 
    document.getElementById('note').value = ''; 
    document.getElementById('enddate').value = ''; 

     search();
}