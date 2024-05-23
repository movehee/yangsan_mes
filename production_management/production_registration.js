//생산등록 등록 js

// 조회 함수
function search(){

    // 시작일과 종료일 가져오기
    let start_date = $('#start_date').val();
    let end_date = $('#end_date').val();

    // 검색 조건을 객체로 생성
    let senddata = new Object();
    senddata.start_date = start_date;
    senddata.end_date = end_date;

    // 검색 결과를 렌더링하여 화면에 표시
    render('production_management/production_registration', senddata);

    return null;
};

// 기본 설정 객체 생성
var default_set = new Object();
default_set['plan_key'] ='';
default_set['product_price'] = 0;
default_set['production_cnt'] = 0;
default_set['amount'] = 0;
default_set['note'] ='';

// 인풋값의 번호를 반영하는 데이터 만들기
var production_data = new Array();
production_data.push(default_set);

// 행 추가 함수
function add_row(){
    
    // 기본 설정값으로 새로운 행 추가
    default_set['plan_key'] ='';
    default_set['product_price'] = 0;
    default_set['production_cnt'] = 0;
    default_set['amount'] = 0;
    default_set['note'] ='';

    // 새로운 행을 데이터에 추가
    production_data.push(default_set);

    // 화면 다시 그리기
    setTimeout(function(){
        draw();
    },100);

    return null;
}

// 행 삭제 함수
function row_delete(index){
    index = parseInt(index);

    let temp_data =  production_data;
    production_data = [];

    // 선택한 인덱스를 제외하고 데이터 복사
    for(let i=0; i<temp_data.length; i++){

        if(i === index){
            continue;
        }
        production_data.push(temp_data[i]);

    }
    // 화면 다시 그리기
    setTimeout(function(){
        draw();
    },100);

    return null;
}

// 생산 데이터 변경 이벤트 처리 함수
function is_onchange(_this, index) {
    index = parseInt(index);

    // 생산 데이터 업데이트
    let parent = $(_this).parent().parent();
    let plan_key = parent.find('select[key="plan_key"]').val();
    let product_price = 0;
    if (plan_key !== '' && plan_key !== null && plan_key !== undefined) {
        product_price = price_data[plan_key];
    }
    let production_cnt = parent.find('input[key="production_cnt"]').val() || 0;
    if (production_cnt === '' || production_cnt === null || production_cnt === undefined) {
        production_cnt = 0;
    } else {
        production_cnt = parseInt(production_cnt);
    }

    let amount = 0;
    if (plan_key !== '' && plan_key !== null && plan_key !== undefined && production_cnt !== 0) {
        amount = product_price * production_cnt;
    }
    let note = parent.find('input[key="note"]').val();

    let temp = new Object();
    temp.plan_key = plan_key;
    temp.product_price = product_price;
    temp.production_cnt = production_cnt;
    temp.amount = amount;
    temp.note = note;

    // 변경된 데이터를 해당 인덱스에 업데이트
    production_data[index] = temp;

    // 이전 생산 수량 가져오기
    let previous_production_cnt = production_data[index].production_cnt || 0;

    // 생산 수량이 변경되었을 때 재고 조정
    adjust_stock(plan_key, previous_production_cnt, production_cnt);

    // 자재재고현황 담는 데이터
    let copy_cnt = Object.assign({}, remain_material_cnt);

    let next_product = false;
    // 생산 데이터로 루프
    for (let i = 0; i < production_data.length; i++) {
        // 계획 sid와 계획 수량 선언
        let plan_key = production_data[i]['plan_key'];
        // 입력받은 수량
        let new_production_cnt = production_data[i]['production_cnt'];
        // copy_data를 복사
        let temp_stock = Object.assign({}, copy_cnt);
        // bom데이터로 루프
        for (let j = 0; j < bom_data[plan_key].length; j++) {

            // bom데이터의 자재sid 선언
            let material_sid = bom_data[plan_key][j]['material_sid'];
            // 자재 소요수량 계산
            let need_material = bom_data[plan_key][j]['material_cnt'] * new_production_cnt;

            // 현재 자재 재고 - 소요 수량 계산
            let after_stock = temp_stock[material_sid] - need_material;

            // 계산 수량이 0보다 작을 시 0으로 리셋
            if (after_stock < 0) {
                next_product = true;
                alert('자재가 충분하지 않습니다.');
                after_stock = temp_stock[material_sid] += need_material;
                production_data[i]['production_cnt'] = 0; // 생산 데이터를 0으로 설정
                temp_stock[material_sid] = copy_cnt[material_sid]; // 재고를 원래 값으로 복구
                parent.find('input[key="production_cnt"]').val(0); // 입력 필드 값을 0으로 변경
                break;
            } else {
                // 소비된 경우 재고 감소
                after_stock = temp_stock[material_sid] -= need_material;
            }
            // 계산수량을 임시 자재재고에 반영
            temp_stock[material_sid] = after_stock;
        }
        if (next_product === true) {
            continue;
        }
        copy_cnt = Object.assign({}, temp_stock);
        console.log(copy_cnt);
    }

    // 품목코드 중복 검사
    let duplication = production_data.filter(ele => ele.plan_key === plan_key);
    if (duplication.length > 1) {
        production_data[index]['plan_key'] = '';
        production_data[index]['product_price'] = 0;
        production_data[index]['production_cnt'] = 0;
        production_data[index]['amount'] = 0;
        production_data[index]['note'] = '';
        alert('품목정보가 중복됩니다.');
    }

    // 계획 가능 수량 제한
    if (production_cnt > remain_cnt[production_data[index]['plan_key']]) {
        production_data[index]['production_cnt'] = 0;
        production_data[index]['amount'] = 0;
        alert('등록 가능 수량은 ' + remain_cnt[production_data[index]['plan_key']] + '입니다. 줄여주세요');
    }
    // 차감 가능 수량 제한
    if (production_cnt < -plan_cnt[production_data[index]['plan_key']]) {
        production_data[index]['production_cnt'] = 0;
        production_data[index]['amount'] = 0;
        alert('차감 가능 수량은 ' + plan_cnt[production_data[index]['plan_key']] + '입니다. 줄여주세요');
    }

    parent.find('input[key="product_price"]').val(product_price);
    parent.find('input[key="amount"]').val(amount);

    // UI 업데이트
    setTimeout(function () {
        draw();
    }, 100);
}

// 재고 조정 함수
function adjust_stock(plan_key, previous_cnt, new_cnt) {

    //생산계획 sid가 없을시 
    if (bom_data.hasOwnProperty(plan_key)===false) return;

    //새로운 수량 - 이전 수량
    let difference = new_cnt - previous_cnt;

    for (let i = 0; i < bom_data[plan_key].length; i++) {

        let material = bom_data[plan_key][i];
        let material_sid = material['material_sid'];
        let required_material_change = material['material_cnt'] * difference; //재고 소요 수량
        remain_material_cnt[material_sid] += required_material_change; // 재고 증가 또는 감소
    }
}

// 주문 선택 함수
function select_order(_this){

    // 선택된 주문 번호 가져오기
    let number_key = $(_this).val();

    let senddata = new Object();
    senddata.plan_number = number_key;

    // API 호출하여 주문 정보 가져오기
    api('api_production_select', senddata, function(output){
        if(output.is_success){

            // 가져온 데이터로 변수 업데이트
            price_data = output.product_price;
            remain_cnt = output.remain_cnt;
            option_list = output.option_list;
            bom_data = output.bom_data;
            plan_cnt = output.plan_cnt;
            remain_material_cnt = output.remain_material_cnt;

            default_set =  new Object();
            default_set['plan_key'] = '';
            default_set['product_price'] = 0;
            default_set['production_cnt'] = 0;
            default_set['amount'] = 0;
            default_set['note'] = '';

            // 기본 설정값으로 초기화된 생산 데이터 배열 생성
            production_data = new Array();
            production_data.push(default_set);
            draw();

        }else{
            alert(output.msg);
        }
    }); 

};

// 화면 그리기 함수
function draw(){

    let html = '';
    let btn_function = '';
    let btn_text ='';
    let is_match = false;
    
    for(let i=0; i<production_data.length; i++){

        html += '<tr>';
            // select
            html += '<td>';
                html += '<select id="plan_key" key="plan_key" onchange="is_onchange(this, ' + i + ');">';

                // select option
                    html += '<option value="" selected disabled>품목 선택</option>';
                    for(let j=0; j<option_list.length; j++){
                    is_match = option_list[j]['plan_key'].toString() === production_data[i]['plan_key'];
                        if(is_match === true){
                    html += '<option value="' + option_list[j]['plan_key'] + '" selected>' + option_list[j]['product_name'] + '</option>';
                        }else{
                    html += '<option value="' + option_list[j]['plan_key'] + '">' + option_list[j]['product_name'] + '</option>';
                        }
                    }
                html += '</select>';
            html += '</td>';

            // input(price)
            html += '<td>';
                html += '<input key="product_price" type="number" disabled value="' + production_data[i]['product_price'] + '" onchange="is_onchange(this, ' + i + ');" />';
            html += '</td>';

            // input(production_cnt)
            html += '<td>';
                html += '<input key="production_cnt" type="number" onchange="is_onchange(this, ' + i + ');" value="' + production_data[i]['production_cnt'] + '" />';
            html += '</td>';

             // 소계
            html += '<td>';
                html += '<input key="amount" type="number" disabled  value="' +  production_data[i]['amount'] + '" onchange="is_onchange(this, ' + i + ');"  />';
            html += '</td>';

            // note
            html += '<td>';
                html += '<input key="note" type="text" onchange="is_onchange(this, ' + i + ');" value="' + production_data[i]['note'] + '" />';
            html += '</td>';

            // 버튼
            btn_text = '삭제';
            btn_function = 'row_delete(' + i + ');';
            if(i === production_data.length - 1){
                btn_text = '추가';
                btn_function = 'add_row();';
            }
            html += '<td>';
                html += '<button onclick="' + btn_function + '">' + btn_text + '</button>';
            html += '</td>';

        html += '</tr>';
    }
    $('#grid_table tbody').html(html);

    return null;
};

// 자재 입고 등록 함수
function registration() {

    if(confirm('정말 등록/수정하시겠습니까?')){

        let plan_number = $('#plan_number').val();

        for(let i=0; i<production_data.length; i++){

            if(production_data[i]['plan_key'] === '') {
                alert((i+1) + '번째 줄의 자재가 선택되지 않았습니다.');
                return false;
            }
            if(production_data[i]['production_cnt'] === ''|| production_data[i]['production_cnt'] ==='0'){
                alert((i+1) + '번째 줄의 자재수량이 입력되지 않았습니다.1');
                return false;
            }

            if(isNaN(production_data[i]['production_cnt']) === true){
                alert((i+1) + '번째 줄의 자재수량의 유효성이 올바르지 않습니다.\n숫자만 입력가능합니다.2');
                return false;
            }

        }

        // 샌드데이터 보내기
        let senddata = new Object();
        senddata.production_data = production_data;
        senddata.plan_number = plan_number;

        // API 호출하여 자재 입고 등록
        api('api_production_insert', senddata ,  function(output){
            if(output.is_success){
                render('production_management/production');
            }
            alert(output.msg);

        });

        return null;
    }
}
