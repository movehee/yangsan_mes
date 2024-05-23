//자재발주 등록/수정 js

// 자재발주 리스트 정보 변경시 수행
function is_onchange(_this, index) {
    index = parseInt(index); // index int 형변환
    let parent = $(_this).parent().parent(); // tr 부모

    // row의 입력값
    let material_key = parent.find('select[key="material_key"]').val();
    let material_cnt = parent.find('input[key="material_cnt"]').val();
    let amount = 0;
    let note = parent.find('input[key="note"]').val();
    let account_sid = $('#up_account_sid').val();
	let material_order_number = $('#update_material_number').val();

    // 수량 음수 검사
    if (material_cnt < 0) {
        alert('수량은 음수를 입력할 수 없습니다.');
        material_cnt = 0;
    }

    // 변경된 자재가 기존 자재와 중복되는지 검사
    let duplication = false;
    for (let i = 0; i < order_data.length; i++) {
        if (i !== index && order_data[i].material_key === material_key) {
            duplication = true;
            alert('자재선택이 중복되었습니다.');
            break;
        }
    }

    setTimeout(function(){
		draw();
	}, 100)

    if (duplication === false) {
        // 유효성 검사를 통과한 경우에만 order_data 및 화면에 변경된 값 적용
        if (material_key !== '' && material_key !== null && material_key !== undefined && material_cnt >= 0) {
            amount = parseInt(material_price[material_key]) * material_cnt;
        }

        // 수정된 자재만 order_data에 대치
        order_data[index]['material_key'] = material_key;
        order_data[index]['material_cnt'] = material_cnt;
        order_data[index]['amount'] = amount;
        order_data[index]['note'] = note;
        order_data[index]['account_sid'] = account_sid;
		order_data[index]['material_order_number'] = material_order_number;

        // 수정된 자재만 화면에 적용
        parent.find('input[key="material_cnt"]').val(material_cnt);
        parent.find('input[key="amount"]').val(amount);
        parent.find('input[key="note"]').val(note);
    } else {
        // 중복이 발생한 경우 이전 값으로 복원
        parent.find('select[key="material_key"]').val(order_data[index]['material_key']);
        parent.find('input[key="material_cnt"]').val(order_data[index]['material_cnt']);
        parent.find('input[key="note"]').val(order_data[index]['note']);
    }

    return null;
}

// 줄 추가
function row_add(){

	default_set = new Object();
	default_set['material_key'] = '';
	default_set['material_cnt'] = 0;
	default_set['amount'] = 0;
	default_set['note'] = '';

	order_data.push(default_set);

	setTimeout(function(){
		draw();
	}, 100)

	return null;
};

// 줄 삭제
function row_delete(index){

	index = parseInt(index);

	order_data.splice(index, 1);
	order_data = Object.values(order_data);

	setTimeout(function(){
		draw();
	}, 100);

	return null;
};

// 그리기
function draw(){

	let html = '';
	for(let i=0; i<order_data.length; i++){

		html += '<tr>';
			// 자재 SELECT 부분
			html += '<td>';
				html += '<select key="material_key" onchange="is_onchange(this, ' + i + ');">';
					html += '<option value="" selected disabled>자재 선택</option>';
					for(let j=0; j<material_list.length; j++){
						// order_data의 자재sid와 자재리스트의 자재 sid 일치여부
						let is_match = order_data[i]['material_key'] === material_list[j]['material_key'];
						if(is_match === true){
							html += '<option value="' + material_list[j]['material_key'] + '" selected>' + material_list[j]['material_name'] + '</option>';
						}
						else{
							html += '<option value="' + material_list[j]['material_key'] + '">' + material_list[j]['material_name'] + '</option>';
						}
					}
				html += '</select>';
			html += '</td>';
			// 수량
			html += '<td>';
				html += '<input type="number" key="material_cnt" placeholder="자재발주수량" value="' + order_data[i]['material_cnt'] + '" onchange="is_onchange(this, ' + i + ');"';
			html += '</td>';
			// 소계
			html += '<td>' + order_data[i]['amount'] + '</td>';
			// 비고
			html += '<td>';
				html += '<input type="text" key="note" placeholder="비고" value="' + order_data[i]['note'] + '" onchange="is_onchange(this, ' + i + ');"/>';
			html += '</td>';
			// 추가,삭제 버튼
			let btn_text = '삭제';
			let btn_function = 'row_delete(' + i + ');';
			if(i === order_data.length - 1){
				btn_text = '추가';
				btn_function = 'row_add();';
			}
			html += '<td>';
				html += '<button onclick="' + btn_function + '">' + btn_text + '</button>';
			html += '</td>';
		html += '</tr>';

	}

	$('#grid_table tbody').html(html);

	return null;
};



// 등록 함수
function registration(url){

	if(confirm('정말 등록/수정하시겠습니까?')){

		// 거래처 선택 체크
		let account_key = $('select[key="account_key"]').val();
		let account_sid = $('#up_account_sid').val();
		let update_material_number = $('#update_material_number').val();

		if(account_key === null){
			alert('거래처를 선택해주세요.');
			return null;
		}

		// 빈값 체크
		for(let i=0; i<order_data.length; i++){

			// 자재 선택 체크
			if(order_data[i]['material_key'] === '' || order_data[i]['material_key'] === null || order_data[i]['material_key'] === undefined){
				alert((i + 1) + '번째 자재 선택이 되지 않았습니다.');
				return null;
			}
			// 수량 체크
			if(order_data[i]['material_cnt'] === '0' || order_data[i]['material_cnt'] === ''){
				alert((i + 1) + '번째 수량이 입력되지 않았습니다.');
				return null;
			}

		}

		let senddata = new Object();
		senddata.account_key = account_key;
		senddata.account_sid = account_sid;
		senddata.order_data = order_data;
		senddata.update_material_number = update_material_number;


		api(url, senddata, function(output){
			if(output.is_success){
				render('material_info/material_order');
			}
		});

		return null;
	}
};