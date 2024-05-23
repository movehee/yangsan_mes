//생산계획 조회 페이지 js

//plan 조회
function search(page = 1) {

	let account_name = $('#account_name').val();
	let product_name = $('#product_name').val();
	let product_cnt = $('#product_cnt').val();
	let plan_note = $('#plan_note').val();
	let plan_date_start = $('#plan_date_start').val();
	let plan_date_end = $('#plan_date_end').val();

	senddata = new Object();
	senddata.page = page;
	senddata.account_name = account_name;
	senddata.product_name = product_name;
	senddata.product_cnt = product_cnt;
	senddata.plan_note = plan_note;
	senddata.plan_date_start = plan_date_start;
	senddata.plan_date_end = plan_date_end;
	
	render('production_management/plan',senddata);

	return null;
}

//plan 수정
function update(plan_number){

	

		let senddata = new Object();

		senddata.plan_number = plan_number;

		render('production_management/plan_registration',senddata);

		return null;  
	
}

//plan 단건 삭제
function plan_sid_delete(sid){

	if(confirm('정말로 삭제하시겠습니까?')){

		let senddata = new Object();

		senddata.plan_sid = sid;

		api('api_plan_sid_delete',senddata,function(output){
			if(output.is_success){
				search();
			}
			alert(output.msg);
		});
	}
}

//plan 번호 삭제
function plan_number_delete(plan_number){

	if(confirm('정말로 삭제하시겠습니까?')){

		let senddata = new Object();

		senddata.plan_number = plan_number;

		api('api_plan_number_delete',senddata,function(output){
			if(output.is_success){
				search();
			}
			alert(output.msg);
		});

	}
}

//검색어 초기화
function resetSearchFields() {
    document.getElementById('account_name').value = ''; 
    document.getElementById('product_cnt').value = ''; 
    document.getElementById('plan_date_start').value =''; 
    document.getElementById('product_name').value = ''; 
    document.getElementById('plan_note').value = ''; 
    document.getElementById('plan_date_end').value = ''; 

    search();
}