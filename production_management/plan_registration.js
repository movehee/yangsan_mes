//생산계획 등록/수정 js

// row 추가가 이루어질때 order_data에 push해줄 빈 데이터셋
var default_set = new Object();
default_set['order_key'] = '';
default_set['plan_cnt'] = 0;
default_set['amount'] = 0;
default_set['plan_note'] = '';

//값 변화 실시간 반영 데이터
var plan_data = new Array();
plan_data.push(default_set);

//열추가
function add_row(){

	default_set = new Object();
	default_set['order_key'] = '';
	default_set['plan_cnt'] = 0;
	default_set['amount'] = 0;
	default_set['plan_note'] = '';

	plan_data.push(default_set);

	setTimeout(function(){
		draw_list();
	},100);

	return null;

} 

//열삭제
function row_delete(index){
	index = parseInt(index);

	let temp_data = plan_data;
	plan_data = [];

	for(let i=0; i<temp_data.length; i++){
		if(i === index){
			continue;
		}
		plan_data.push(temp_data[i]);
	}
	setTimeout(function(){
		draw_list();
	},100);

	return null;
}
//값이 바뀔 때마다 데이터 업데이트
function is_onchange(_this, index) {

	index = parseInt(index);

	let parent = $(_this).parent().parent();

	let order_sid = parent.find('select[key="order_key"]').val();
	let plan_cnt = parent.find('input[key="plan_cnt"]').val();
	if(plan_cnt === '' || plan_cnt === null && plan_cnt === undefined){
		plan_cnt = 0;
	}else{
		plan_cnt = parseInt(plan_cnt);
	}
	let product_price = 0;
	if(order_sid !== '' && order_sid !== null && order_sid !== undefined){
		product_price = price_data[order_sid];
	}
	let amount = product_price * plan_cnt;
	let plan_note = parent.find('input[key="plan_note"]').val();

	let temp = new Object();
	temp.product_price = product_price;
	temp.order_key = order_sid;
	temp.plan_cnt = plan_cnt;
	temp.amount = amount;
	temp.plan_note = plan_note;

	plan_data[index] = temp;

	// 수주수량 0이하일 경우 0으로 강제 대치
	if(plan_cnt < 0){
		plan_data[index]['plan_cnt'] = 0;
		plan_data[index]['amount'] = 0;
		alert('계획수량은 0이상의 정수를 입력해주세요.');
	}

	// 계획 가능 수량 제한
	if(plan_cnt > remain_cnt[plan_data[index]['order_key']]){
		plan_data[index]['plan_cnt'] = 0;
		plan_data[index]['amount'] = 0;
		alert('최대 계획 가능 수량은' + remain_cnt[plan_data[index]['order_key']] + '입니다. 줄여주세요');
	}
	

	//  품목코드 중복 검사
	let duplication = plan_data.filter(ele => ele.order_key === order_sid);
	if(duplication.length > 1){
		plan_data[index]['order_key'] = '';
		plan_data[index]['product_price'] = 0;
		plan_data[index]['amount'] = 0;
		alert('품목정보의 조합이 중복됩니다.');
	}

	setTimeout(function(){
		draw_list();
	}, 100);

	if (duplication === false) {
        // 유효성 검사를 통과한 경우에만 order_data 및 화면에 변경된 값 적용
        if (order_sid !== '' && order_sid !== null && order_sid !== undefined && order_sid >= 0) {
            product_price = parseInt(price_list[sid_pro_map[order_sid]]) ;
            amount = parseInt(price_list[sid_pro_map[order_sid]]) * plan_cnt;
        }

        // 수정된 자재만 order_data에 대치
        plan_data[index]['order_key'] = order_sid;
        plan_data[index]['product_price'] = product_price;
        plan_data[index]['amount'] = amount;
        plan_data[index]['plan_cnt'] = plan_cnt;
        plan_data[index]['plan_note'] = plan_note;
		
        // 수정된 자재만 화면에 적용
        parent.find('input[key="plan_cnt"]').val(plan_cnt);
        parent.find('input[key="amount"]').val(amount);
        parent.find('input[key="plan_note"]').val(plan_note);
    } else {
        // 중복이 발생한 경우 이전 값으로 복원
        parent.find('select[key="order_key"]').val(plan_data[index]['order_key']);
        parent.find('input[key="plan_cnt"]').val(plan_data[index]['plan_cnt']);
        parent.find('input[key="plan_note"]').val(plan_data[index]['plan_note']);
    }

    return null;
}



// 수주 onchange => 계획 가능 수량 => key: order_sid / 품목의 가격 => key: order_sid / order_sid, product_name 색인배열
// var price_data = new Object();
// var remain_cnt = new Object();
// var option_list = new Array();
// 생산계획 등록시 수주 선택 함수
function select_order(_this){

	let number_key = $(_this).val();

	let senddata = new Object();
	senddata.order_number = number_key;// 넘기는 수주번호

	api('api_product_select', senddata, function(output) {
		if(output.is_success){
			
			price_data = output.product_price;//품목가격
			remain_cnt = output.remain_cnt; //생산계획가능 잔여량
			option_list = output.option_list; //품목 리스트

			default_set = new Object();
			default_set['order_key'] = '';
			default_set['plan_cnt'] = 0;
			default_set['amount'] = 0;
			default_set['plan_note'] = '';

			plan_data = new Array();
			plan_data.push(default_set);

			
			draw_list();
			
		}else{
			alert(output.msg);
		}
	});
};
// html 그려주는 함수
function draw_list(){

	let draw_html = '';
	let btn_text = '';
	let btn_function = '';
	let is_match = false;
	for(let i=0; i<plan_data.length; i++){

		draw_html += '<tr>';
			// select
			draw_html += '<td>';
				draw_html += '<select id="order_key" key="order_key" onchange="is_onchange(this, ' + i + ');">';

					// select option
					draw_html += '<option value="" selected disabled>품목선택</option>';
					for(let j=0; j<option_list.length; j++){

						is_match = option_list[j]['order_sid'].toString() === plan_data[i]['order_key'];
						if(is_match === true){
							draw_html += '<option value="' + option_list[j]['order_sid'] + '" selected>' + option_list[j]['product_name'] + '</option>';
						}else{
							draw_html += '<option value="' + option_list[j]['order_sid'] + '">' + option_list[j]['product_name'] + '</option>';
						}

					}

				draw_html += '</select>';
			draw_html += '</td>';

			// input(price)
			draw_html += '<td>';
				draw_html += '<input name="product_price" type="number" disabled value="' + plan_data[i]['product_price'] + '" onchange="is_onchange(this, ' + i + ');" />';
			draw_html += '</td>';

			// input(plan_cnt)
			draw_html += '<td>';
				draw_html += '<input key="plan_cnt" type="number" onchange="is_onchange(this, ' + i + ');" value="' + plan_data[i]['plan_cnt'] + '" />';
			draw_html += '</td>';

			// 소계
			draw_html += '<td>' + plan_data[i]['amount'] + '</td>';

			// note
			draw_html += '<td>';
				draw_html += '<input key="plan_note" type="text" onchange="is_onchange(this, ' + i + ');" value="' + plan_data[i]['plan_note'] + '" />';
			draw_html += '</td>';

			// 버튼
			btn_text = '삭제';
			btn_function = 'row_delete(' + i + ');';
			if(i === plan_data.length - 1){
				btn_text = '추가';
				btn_function = 'add_row();';
			}
			draw_html += '<td>';
				draw_html += '<button onclick="' + btn_function + '">' + btn_text + '</button>';
			draw_html += '</td>';

		draw_html += '</tr>';

	}

	$('#grid_table tbody').html(draw_html);

	return null;
};

//생산계획 등록 
function registration(url) {

	if(confirm('정말 등록/수정하시겠습니까?')){

		let up_plan_number = $('#up_plan_number').val();

		//유효성검사(품목 중복,공백 방지, 수량 제한,마이너스,숫자)
		for(let i=0; i<plan_data.length; i++) {

			if(plan_data[i]['order_key'] === ''){
				alert((i+1) + '번째 줄의 품목이 선택되지 않았습니다.');
				return false;
			}
			if(plan_data[i]['plan_cnt'] === ''){
				alert((i+1) + '번째 줄의 품목이 선택되지 않았습니다.');
				return false;
			}
			if(isNaN(plan_data[i]['plan_cnt']) === true){
				alert((i+1) + '번째 줄의 품목수량의 유효성이 올바르지 않습니다.\n숫자만 입력가능합니다.');
				return false;
			}
			
		}


		//샌드데이터 보내기
		let senddata = new Object();
		senddata.plan_data = plan_data;
		senddata.up_plan_number = up_plan_number;

		console.log(senddata);

		api(url, senddata , function(output){
			if(output.is_success){
				render('production_management/plan');
				alert(output.msg);
				return null;
			}
			alert(output.msg);
		})	

		return null;
	}
}