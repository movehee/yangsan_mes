//생산 등록 조회 페이지 js

//생산 등록 조회
function search(page = 1) {

	let account_name = $('#account_name').val();
	let production_cnt = $('#production_cnt').val();
	let product_name = $('#product_name').val();
	let production_note = $('#production_note').val();
	let plan_date_start = $('#plan_date_start').val();
	let plan_date_end = $('#plan_date_end').val();
	let production_date_start = $('#product_date_start').val();
	let production_date_end = $('#production_date_end').val();


	senddata = new Object();
	senddata.page = page;
	senddata.account_name = account_name;
	senddata.product_name = product_name;
	senddata.production_cnt = production_cnt;
	senddata.production_note = production_note;
	senddata.plan_date_start = plan_date_start;
	senddata.plan_date_end = plan_date_end;
	senddata.production_date_start = production_date_start;
	senddata.production_date_end = production_date_end;
	
	render('production_management/production',senddata);

	return null;
}

function resetSearchFields() {
    document.getElementById('account_name').value = ''; // 거래처명 필드 초기화
    document.getElementById('production_cnt').value = ''; // 수량 필드 초기화
    document.getElementById('product_name').value = ''; // 상품명 필드 초기화
    document.getElementById('production_note').value = ''; // 비고 필드 초기화
    document.getElementById('plan_date_start').value = ''; // 계획날짜 시작 필드 초기화
    document.getElementById('product_date_start').value = ''; // 생산날짜 시작 필드 초기화
    document.getElementById('plan_date_end').value = ''; // 계획날짜 종료 필드 초기화
    document.getElementById('production_date_end').value = ''; // 생산날짜 종료 필드 초기화

    search();

}
