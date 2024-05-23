
//자재 입고 조회 페이지 js

// 조회 함수
function search(page=1){

	let is_account_name = $('#is_account_name').val();
	let is_order_startdate = $('#is_order_startdate').val();
	let is_order_enddate = $('#is_order_enddate').val();
	let is_input_startdate = $('#is_input_startdate').val();
	let is_input_enddate = $('#is_input_enddate').val();
	let is_material_name = $('#is_material_name').val();
	let is_change_cnt = $('#is_change_cnt').val();
	let is_note = $('#is_note').val();

	let senddata = new Object();
	senddata.page = page;
	senddata.is_account_name = is_account_name;
	senddata.is_order_startdate = is_order_startdate;
	senddata.is_order_enddate = is_order_enddate;
	senddata.is_input_startdate = is_input_startdate;
	senddata.is_input_enddate = is_input_enddate;
	senddata.is_material_name = is_material_name;
	senddata.is_change_cnt = is_change_cnt;
	senddata.is_note = is_note;

	render('material_info/material_input', senddata);

	return null;
};

//검색어 초기화
function resetSearchFields() {
    document.getElementById('is_account_name').value = ''; 
    document.getElementById('is_order_startdate').value = ''; 
    document.getElementById('is_order_enddate').value =''; 
    document.getElementById('is_input_startdate').value = ''; 
    document.getElementById('is_input_enddate').value = ''; 
    document.getElementById('is_material_name').value = ''; 
    document.getElementById('is_change_cnt').value = ''; 
    document.getElementById('is_note').value = ''; 

     search();
}

