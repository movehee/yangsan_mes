//납품 조회 화면 js

//납품 조회
function search(page = 1) {

	let account_name = $('#account_name').val();
	let product_name = $('#product_name').val();
	let delivery_cnt = $('#delivery_cnt').val();
	let delivery_note = $('#delivery_note').val();
	let order_date_start = $('#order_date_start').val();
	let order_date_end = $('#order_date_end').val();
	let delivery_date_start = $('#delivery_date_start').val();
	let delivery_date_end = $('#delivery_date_end').val();


	senddata = new Object();
	senddata.page = page;
	senddata.account_name = account_name;
	senddata.product_name = product_name;
	senddata.delivery_cnt = delivery_cnt;
	senddata.delivery_note = delivery_note;
	senddata.order_date_start = order_date_start;
	senddata.order_date_end = order_date_end;
	senddata.delivery_date_start = delivery_date_start;
	senddata.delivery_date_end = delivery_date_end;
	
	render('sales_management/delivery',senddata);

	return null;
}


//검색어 초기화
function resetSearchFields() {

	    $('#account_name').val('');
		$('#product_name').val('');
		$('#delivery_cnt').val('');
		$('#delivery_note').val('');
		$('#order_date_start').val('');
		$('#order_date_end').val('');
		$('#delivery_date_start').val('');
		$('#delivery_date_end').val('');

		search();
}