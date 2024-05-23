// 품목 재고현황 페이지 js

// 품목 조회

function search(page=1){

	let product_name = $('#product_name').val();
	let product_price = $('#product_price').val();

	let senddata = new Object();
	senddata.page = page;
	senddata.product_name = product_name;
	senddata.product_price = product_price;
	
	render('production_management/product_stock' , senddata);

	return null;

};	

//검색어 초기화
function resetSearchFields() {
    document.getElementById('product_name').value = ''; 
    document.getElementById('product_price').value = ''; 
   

    search();
}