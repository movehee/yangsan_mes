//수주 조회 js

//order 조회
function search(page = 1) {

	let account_name = $('#account_name').val();
	let product_name = $('#product_name').val();
	let product_cnt = $('#product_cnt').val();
	let order_note = $('#order_note').val();
	let order_date_start = $('#order_date_start').val();
	let order_date_end = $('#order_date_end').val();

	senddata = new Object();
	senddata.page = page;
	senddata.account_name = account_name;
	senddata.product_name = product_name;
	senddata.product_cnt = product_cnt;
	senddata.order_note = order_note;
	senddata.order_date_start = order_date_start;
	senddata.order_date_end = order_date_end;
	
	render('sales_management/order',senddata);

	return null;
}
//order 수정
function update(order_number) {

		let senddata = new Object();

		senddata.order_number = order_number;

		render('sales_management/order_registration', senddata);

		return null;

	
}


//order_sid 선택삭제
function order_delete(){

	if(confirm('정말 삭제하시겠습니까?')){

		let senddata = new Object();
		senddata.checked_number = checked_sid;

		api('api_order_number_delete', senddata, function(output){
			if(output.is_success){
				search();
			}
			alert(output.msg);
		});

	}
}
//단건 삭제
function deleteRow(sid){

	if(confirm('정말 삭제하시겠습니까?')){

		let senddata = new Object();
		senddata.order_sid = sid;

		api('api_order_sid_delete', senddata, function(output){
			if(output.is_success){
				search();
			}
			alert(output.msg);
		});

		return null;

	}
}

//검색어 초기화
function resetSearchFields() {
    document.getElementById('account_name').value = ''; 
    document.getElementById('product_cnt').value = ''; 
    document.getElementById('order_date_start').value =''; 
    document.getElementById('product_name').value = ''; 
    document.getElementById('order_note').value = ''; 
    document.getElementById('order_date_end').value = ''; 

    search();
}