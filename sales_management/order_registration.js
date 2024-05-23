// 수주 등록/수정 페이지 js

// row 추가가 이루어질때 order_data에 push해줄 빈 데이터셋
var default_set = new Object();
default_set['product_key'] = '';
default_set['product_price'] = 0;
default_set['product_cnt'] = 0;
default_set['ammount'] = 0;
default_set['order_note']  = '';

//값 변화 실시간 반영 데이터
var order_data = new Array();
order_data.push(default_set);

// order_data row 추가 함수
function row_add(){

	//row 추가가 이루어질때 order_data에 push해줄 빈 데이터셋
	default_set = new Object();
	default_set['product_key'] = '';
	default_set['product_price'] = 0;
	default_set['product_cnt'] = 0;
	default_set['ammount'] = 0;
	default_set['order_note']  = '';


	order_data.push(default_set);

	// 0.1초후에 tbody 그려주기
	setTimeout(function(){
		tbody_draw();
	}, 100);

	return null;
};


// order_data row 삭제 함수
function row_delete(index){

	index = parseInt(index);

	//기존에 받은 order_data를 temp_data에 넣어주기
	let temp_data = order_data;

	//order_data를 배열로 초기화
	order_data = new Array();

	//temp_data의 길이 만큼 i를 돌린다
	for(let i=0; i<temp_data.length; i++){
		//i와 삭제버튼이 일치할 경우 나오기
		if(i === index){
			continue;
		}
		//temp_data[i]만큼 order_data에 배열 푸쉬
		order_data.push(temp_data[i]);

	}
	//0.1초 후에 tbody 그려주기
	setTimeout(function(){
		tbody_draw();
	},100);

	return null;
};

// tbody를 그려주는 함수
function tbody_draw(){

	let tbody_html = '';
	let is_match = false;
	let total_cnt = 0;
	let total_ammount = 0;
	for(let i=0; i<order_data.length; i++){

		total_cnt += parseInt(order_data[i]['product_cnt']);
		total_ammount += parseInt(order_data[i]['ammount']);

		tbody_html += '<tr>';

			tbody_html += '<td>';
				tbody_html += '<select key="product_key" onchange="is_onchange(this, ' + i + ');">'
					tbody_html += '<option value="" selected disabled>품목 선택</option>';
					for(let j=0; j<product_list.length; j++){
						is_match = order_data[i]['product_key'] === product_list[j]['sid'];

						if(is_match === true){
							tbody_html += '<option value="' + product_list[j]['sid'] + '" selected >';
						}
						else{
							tbody_html += '<option value="' + product_list[j]['sid'] + '" >';
						}
						tbody_html += product_list[j]['product_name'];
						tbody_html += '</option>';
					}
				tbody_html += '</select>';
			tbody_html += '</td>';

			tbody_html += '<td>';
				tbody_html += '<input key="product_price" name="product_price" type="number" disabled value="' + order_data[i]['product_price'] + '" onchange="is_onchange(this, ' + i + ');" />';
			tbody_html += '</td>';

			tbody_html += '<td>';
				tbody_html += '<input key="product_cnt" name="product_cnt" type="number" value="' + order_data[i]['product_cnt'] + '" onchange="is_onchange(this, ' + i + ');" />';
			tbody_html += '</td>';

			tbody_html += '<td><div style="float:right">';
				tbody_html += order_data[i]['ammount'].toLocaleString();
			tbody_html += '</div></td>';

			tbody_html += '<td>';
				tbody_html += '<input key="order_note" name="order_note" type="text" value="' + order_data[i]['order_note'] + '" onchange="is_onchange(this, ' + i + ');" />';
			tbody_html += '</td>';


			let btn_text = '추가';
			let btn_function = 'row_add();';
			if(i < order_data.length - 1){
				btn_text = '삭제';
				btn_function = 'row_delete(' + i + ');';
			}

			tbody_html += '<td>';
				tbody_html += '<button onclick="' + btn_function + '">' + btn_text + '</button>';
			tbody_html += '</td>';

		tbody_html += '</tr>';

	}

	tbody_html += '<tr>';
		tbody_html += '<td>총계</td>';
		tbody_html += '<td>총수량</td>';
		tbody_html += '<td><div style="float:right">' + total_cnt.toLocaleString() + '</div></td>';
		tbody_html += '<td>총계금액</td>';
		tbody_html += '<td><div style="float:right">' + total_ammount.toLocaleString() + '</div></td>';


	// tbody_html += '</tr>';
	$('#grid_table tbody').html(tbody_html);

	return null;
};

// 선택한 품목 키를 담는 배열
let selected_product_arr = new Array();
// 수정된 is_onchange 함수
function is_onchange(_this, index) {
    index = parseInt(index);
    let parent = $(_this).parent().parent();
    let product_sid = parent.find('select[key="product_key"]').val();

    //선택된 품목만 업데이트
    if(order_data[index] === undefined){
        order_data[index] = {};
    }
    order_data[index]['product_key'] = product_sid;
    //품목 중복 검사
    for (let i=0; i<order_data.length; i++) {
        if (i !== index && order_data[i]['product_key'] === product_sid) {
            alert('이미 선택된 품목입니다.');
            parent.find('select[key="product_key"]').val('');
            return;
        }
    }

    //품목 선택 시 해당 품목 가격 자동 입력
    let product_price = price_list[product_sid] || 0; // 기본값 0으로 설정
    let product_cnt = parent.find('input[name="product_cnt"]').val();
    //품목이 선택되지 않은 경우 수량을 0으로 설정
    if(product_sid === ''){
        parent.find('input[name="product_cnt"]').val(0);
        product_cnt = 0;
    }else{
        //품목이 선택된 경우 수량을 확인
        if (product_cnt === '' || product_cnt === null || product_cnt === undefined) {
            product_cnt = 0;
        }else{
            product_cnt = parseInt(product_cnt);
        }
    }

    let note = parent.find('input[name="order_note"]').val();

    // 품목 선택 시 해당 품목의 가격을 자동으로 입력
    parent.find('input[name="product_price"]').val(product_price);

    //수주 데이터에 넣을 데이터(제품sid, 제품 가격, 제품 수량, 비고)
    order_data[index]['product_key'] = product_sid;
    order_data[index]['product_price'] = product_price;
    order_data[index]['product_cnt'] = product_cnt;
    order_data[index]['order_note'] = note;

    order_data[index]['ammount'] = parseInt(order_data[index]['product_price']) * parseInt(order_data[index]['product_cnt']);

    // 수주 수량이 0 이하일 경우 0으로 강제대치
    if (product_cnt < 0) {
        order_data[index]['product_cnt'] = 0;
        order_data[index]['ammount'] = 0;
        alert('수주수량은 0과 양의 정수만 입력 가능합니다.');
    }

    // 변경 사항 적용 후 테이블을 다시 그림
    setTimeout(function(){
        tbody_draw();
    }, 100);
}


// order 등록
function registration(url){

	if(confirm('정말 등록/수정하시겠습니까?')){

		let order_number = $('#order_number').val();
		let account_sid = $('#account_sid').parent().find('option:selected').val();
		let up_account_sid =  $('#up_account_sid').val();

		//유효성검사
		if(account_sid === ''){
			alert('거래처가 입력되지 않았습니다.');
		}
		for(let i=0; i<order_data.length; i++){
			
			if(order_data[i]['product_key'] === ''){
				alert((i+1) + '번째 줄의 품목이 선택되지 않았습니다.');
				return false;
			}
			if(order_data[i]['product_cnt'] === 0){
				alert((i+1) + '번째 줄의 품목수량이 0 입니다. 수량을 입력해주세요.');
				return false;
			}
			if(isNaN(order_data[i]['product_cnt']) === true){
				alert((i+1) + '번째 줄의 품목수량의 유효성이 올바르지 않습니다.\n숫자만 입력가능합니다.');
				return false;
			}
		}

		//샌드데이터 보내기
		let senddata = new Object();
		senddata.order_data = order_data;
		senddata.account_sid = account_sid;
		senddata.order_number = order_number;
		senddata.up_account_sid = up_account_sid;

		console.log(senddata);

		api(url, senddata , function(output){
			if(output.is_success){
				render('sales_management/order');
				alert(output.msg);
				return null;
			}
			alert(output.msg);

		})

		return null;
	}
};