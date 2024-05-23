// 자재 재고현황 페이지 js

// 자재 조회

function search(page=1){

	let material_name = $('#material_name').val();
	let material_price = $('#material_price').val();

	let senddata = new Object();
	senddata.page = page;
	senddata.material_name = material_name;
	senddata.material_price = material_price;
	
	render('material_info/material_stock' , senddata);

	return null;

};	

//검색어 초기화
function resetSearchFields() {
    document.getElementById('material_name').value = ''; 
    document.getElementById('material_price').value = ''; 
    

     search();
}