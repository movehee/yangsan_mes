//납품 등록 js

//초기화 시키는 함수
function reset(){

	delivery_list = new Array();

	default_set = new Object();
	default_set['order_sid'] = '';
	default_set['delivery_cnt'] = 0;
	default_set['ammount'] = 0;
	default_set['note'] = '';
	delivery_list.push(default_set);

	setTimeout(function(){
		draw();
	}, 100);

	return null;
};

//그리기 함수
function draw(){

	let tbody = '';
	for(let i=0; i<delivery_list.length; i++){

		let btn_text = '삭제';
		let btn_function = 'row_delete(' + i + ');';
		if(i === delivery_list.length - 1){
			btn_text = '추가';
			btn_function = 'row_add();';
		}

		tbody += '<tr>';
			// 품목
			tbody += '<td>';
				tbody += '<select key="order_sid" onchange="is_onchange(this, ' + i + ');">';
					tbody += '<option value="" selected disabled>품목 선택</option>';
					for(let j=0; j<product_list.length; j++){

						let selected = delivery_list[i]['order_sid'] === product_list[j]['order_sid'];
						if(selected === true){
							tbody += '<option value="' + product_list[j]['order_sid'] + '" selected>' + product_list[j]['product_name'] + '</option>';
						}
						else{
							tbody += '<option value="' + product_list[j]['order_sid'] + '">' + product_list[j]['product_name'] + '</option>';
						}

					}
				tbody += '</select>';
			tbody += '</td>';
			// 수량
			tbody += '<td>';
				tbody += '<input type="number" key="delivery_cnt" onchange="is_onchange(this, ' + i + ');" value="' + delivery_list[i]['delivery_cnt'] + '" />';
			tbody += '</td>';
			// 소계
			tbody += '<td>';
				tbody += delivery_list[i]['ammount'].toLocaleString();
			tbody += '</td>';
			// 비고
			tbody += '<td>';
				tbody += '<input type="text" key="note" onchange="is_onchange(this, ' + i + ');" value="' + delivery_list[i]['note'] + '" />';
			tbody += '</td>';
			// 버튼
			tbody += '<td>';
				tbody += '<button onclick="' + btn_function + '">' + btn_text + '</button>';
			tbody += '</td>';
		tbody += '</tr>';

	}

	$('#grid_table tbody').html(tbody);

	return null;
};

//수주 선택 함수
function search_order(){

	let startdate = $('#startdate').val();
	let enddate = $('#enddate').val();

	let senddata = new Object();
	senddata.startdate = startdate;//수주시작날짜
	senddata.enddate = enddate;//수주끝날짜

	api('api_search_order', senddata, function(output){
		if(output.is_success){

			let order_list = output.order_list;//수주 뿌리는 리스트

			let html = '<option value="" selected disabled>수주 선택</option>';
			for(let i=0; i<order_list.length; i++){

				html += '<option value="' + order_list[i]['order_number'] + '">';
				html += '수주번호 : ' + order_list[i]['order_number'] + ' / 수주날짜 : ' + order_list[i]['order_date'] + ' / 거래처명 : ' + order_list[i]['account_name'];
				html += '</option>';

			}

			$('#order_list').html(html);

			product_data = new Object();
			product_list = new Array();
			reset();
		}
	});
	return null;
};

var product_data = new Object();
var product_list = new Array();
var delivery_list = new Array();

var default_set = new Object();
default_set['order_sid'] = '';
default_set['delivery_cnt'] = 0;
default_set['ammount'] = 0;
default_set['note'] = '';
delivery_list.push(default_set);

//수주 선택시 발동 함수
function select_order(_this){

	let senddata = new Object();
	senddata.order_number = $(_this).val();//수주번호 넘기기

	api('api_delivery_select_order', senddata, function(output){
		if(output.is_success){

			product_list = output.product_list;//품목리스트 
			product_data = output.product_data;//품목가격

			reset();

		}
	});

	return null;
};

//열 추가 함수
function row_add(){

	default_set = new Object();
	default_set['order_sid'] = '';
	default_set['delivery_cnt'] = 0;
	default_set['ammount'] = 0;
	default_set['note'] = '';
	delivery_list.push(default_set);

	setTimeout(function(){
		draw();
	}, 100);

	return null;
};

//열 삭제 함수
function row_delete(index){

	index = parseInt(index);
	let temp_list = new Array();
	for(let i=0; i<delivery_list.length; i++){
		if(i === index){
			continue;
		}
		temp_list.push(delivery_list[i]);
	}
	delivery_list = temp_list;

	setTimeout(function(){
		draw();
	}, 100);

	return null;
};

//값이 바뀔 때 마다 데이터 업데이트
function is_onchange(_this, index){

	index = parseInt(index);

	let parent = $(_this).parent().parent();
	let order_sid = parent.find('select[key="order_sid"]').val();
	if(order_sid === null){
		order_sid = '';
	}else{
		order_sid = parseInt(order_sid);
	}
	let delivery_cnt = parent.find('input[key="delivery_cnt"]').val();
	delivery_cnt = parseInt(delivery_cnt);
	let ammount = 0;
	let note = parent.find('input[key="note"]').val();

	if(order_sid !== ''){
		// 재고 검사
		if(delivery_cnt > product_data[order_sid]['product_stock']){
			delivery_cnt = 0;
			alert('품목의 현재 재고는 ' + product_data[order_sid]['product_stock'] + ' 개입니다.');
		}
		// 납품 수량 검사
		if(delivery_cnt > product_data[order_sid]['remain_cnt']){
			delivery_cnt = 0;
			alert('납품 가능 수량은 최대 ' + product_data[order_sid]['remain_cnt'] + ' 개입니다.');
		}
		//납품 수량 차감 가능 수량
		if(product_data[order_sid]['order_cnt'] < product_data[order_sid]['remain_cnt'] - delivery_cnt){
			delivery_cnt = 0;
			alert('최대 차감 가능한 납품 수량은 ' + (product_data[order_sid]['order_cnt'] - product_data[order_sid]['remain_cnt']) + ' 개입니다.');
		}
		// 품목 중복 검사
		let duplication = false;
		for(let i=0; i<delivery_list.length; i++){
			if(delivery_list[i]['order_sid'] === order_sid){
				duplication = i;
				break;
			}
		}
		if(duplication !== false){
			if(index !== duplication){
				alert('선택 품목이 중복되었습니다.');
				order_sid = '';
				delivery_cnt = 0;
			}
		}
	}
	if(order_sid !== ''){
		ammount = product_data[order_sid]['product_price'] * delivery_cnt;
	}

	delivery_list[index]['order_sid'] = order_sid;
	delivery_list[index]['delivery_cnt'] = delivery_cnt;
	delivery_list[index]['ammount'] = ammount;
	delivery_list[index]['note'] = note;

	setTimeout(function(){
		draw();
	}, 100);

	return null;
};

//납품 등록 함수
function registration(){

	if(confirm('정말 등록/수정하시겠습니까?')){

		if(Object.keys(product_data).length === 0){
			alert('수주를 먼저 선택해주세요.');
			return null;
		}

		for(let i=0; i<delivery_list.length; i++){
			if(delivery_list[i]['order_sid'] === ''){
				alert((i + 1) + '번째 납품정보의 품목이 선택되지 않았습니다.');
				return null;
			}
			if(delivery_list[i]['delivery_cnt'] === 0){
				alert((i + 1) + '번째 납품정보의 수량이 입력되지 않았습니다.');
				return null;
			}
		}
		//납품 등록 api로 이동 후 조회화면으로
		api('api_delivery_registration', {delivery_list : delivery_list}, function(output){
			if(output.is_success){
				render('sales_management/delivery');
			}
		});

		return null;

	}
};