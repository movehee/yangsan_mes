//bom 등록/수정 js

// row 추가가 이루어질때 material_data에 push해줄 빈 데이터셋
var default_set = new Object();
default_set['material_key'] = '';
default_set['material_cnt'] = '';
default_set['note'] = '';


// 인풋값의 변화를 실시간으로 반영하는 데이터
var material_data = new Array();
material_data.push(default_set);

// material_data row 추가 함수
function row_add(){

	//row 추가가 이루어질때 material_data에 push해줄 빈 데이터셋
	default_set = new Object();
	default_set['material_key'] = '';
	default_set['material_cnt'] = '';
	default_set['note'] = '';

	material_data.push(default_set);

	// 0.1초후에 tbody 그려주기
	setTimeout(function(){
		tbody_draw();
	}, 100);

	return null;
};

// material_data row 삭제 함수
function row_delete(index){


	index = parseInt(index);

	//기존에 받은 material_data를 temp_data에 넣어주기
	let temp_data = material_data;
	//material_data를 배열로 초기화
	material_data = new Array();
	//temp_data의 길이 만큼 i를 돌린다
	for(let i=0; i<temp_data.length; i++){
		//i와 삭제버튼이 일치할 경우 나오기
		if(i === index){
			continue;
		}
		//temp_data[i]만큼 material_data에 배열 푸쉬
		material_data.push(temp_data[i]);

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
	for(let i=0; i<material_data.length; i++){

		tbody_html += '<tr>';

			tbody_html += '<td>';
				tbody_html += '<select key="material_key" onchange="is_onchange(this, ' + i + ');">'
					tbody_html += '<option value="" selected disabled>자재 선택</option>';
					for(let j=0; j<material_list.length; j++){
						tbody_html += '<option value="' + material_list[j]['sid'] + '" ';
						if(material_data[i]['material_key'] === material_list[j]['sid']){
							tbody_html += 'selected';
						}
						tbody_html += '>';
						tbody_html += material_list[j]['material_name'] + '</option>';
					}
				tbody_html += '</select>';
			tbody_html += '</td>';

			tbody_html += '<td>';
				tbody_html += '<input key="material_cnt" type="number" value="' + material_data[i]['material_cnt'] + '" onchange="is_onchange(this, ' + i + ');" />';
			tbody_html += '</td>';

			tbody_html += '<td>';
				tbody_html += '<input key="note" type="text" value="' + material_data[i]['note'] + '" onchange="is_onchange(this, ' + i + ');" />';
			tbody_html += '</td>';

			let btn_text = '추가';
			let btn_function = 'row_add();';
			if(i < material_data.length - 1){
				btn_text = '삭제';
				btn_function = 'row_delete(' + i + ');';
			}

			tbody_html += '<td>';
				tbody_html += '<button onclick="' + btn_function + '">' + btn_text + '</button>';
			tbody_html += '</td>';

		tbody_html += '</tr>';

	}

	

	$('#grid_table tbody').html(tbody_html);

	return null;
};

// 데이터의 변화 적용
function is_onchange(_this, index){

	index = parseInt(index);
	let parent = $(_this).parent().parent();

	//속성값 변경
	let material_key = parent.find('select[key="material_key"]').val();
	let material_cnt = parent.find('input[key="material_cnt"]').val();
	let note = parent.find('input[key="note"]').val();

	//수량을 0보다 작게 넣었을경우 ex) -1,-100,-1231231
	if(material_cnt < 0){
		alert('음수를 입력할 수 없습니다.')
		material_cnt = 0;
		}
	
	//중복된 자재가 있을경우
	let duplication = false;
	for(let i = 0; i < material_data.length; i++){
		if ( i !== index && material_data[i].material_key === material_key){
			duplication = true;
			alert('자재선택중복');
			material_key = "자재선택";
			
		}
	}

    setTimeout(function(){
	tbody_draw();
	}, 100)


	if (duplication === false) {    
	    
	    // 수정된 자재만 order_data에 대치
	    material_data[index]['material_key'] = material_key;
	    material_data[index]['material_cnt'] = material_cnt;
	   	material_data[index]['note'] = note;
	   	

	    // 수정된 자재만 화면에 적용
	    parent.find('input[key="material_cnt"]').val(material_cnt);
	   
	} else {
	    // 중복이 발생한 경우 이전 값으로 복원
	    parent.find('select[key="material_key"]').val(material_data[index]['material_key']);
	    parent.find('input[key="material_cnt"]').val(material_data[index]['material_cnt']);
	   
	}

    return null;
}


// BOM 등록
function registration(url){

	if(confirm('정말 등록/수정하시겠습니까?')){

		let product_sid = $('#product_sid').val();
		//유효성 검사
		if(product_sid === null){
			alert('품목을 선택해주세요.');
			return false;
		}
		if(material_data.length === 0){
			alert('자재 정보를 등록해주세요.');
			return false;
		}
		for(let i=0; i<material_data.length; i++){
			if(material_data[i]['material_key'] === ''){
				alert((i+1) + '번째 줄의 자재가 선택되지 않았습니다.');
				return false;
			}
			if(material_data[i]['material_cnt'] === ''){
				alert((i+1) + '번째 줄의 자재수량이 입력되지 않았습니다.');
				return false;
			}
			if(isNaN(material_data[i]['material_cnt']) === true){
				alert((i+1) + '번째 줄의 자재수량의 유효성이 올바르지 않습니다.\n숫자만 입력가능합니다.');
				return false;
			}
		}
		//샌드데이터 보내기
		let senddata = new Object();
		senddata.product_sid = product_sid;
		senddata.material_data = material_data;

		console.log(senddata);

		api(url, senddata , function(output){
			if(output.is_success){
				render('masterdata/bom');
				return null;
			}
			alert(output.msg);

		})

		return null;

	}
};

