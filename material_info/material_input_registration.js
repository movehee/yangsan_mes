//자재 입고 등록/수정 js

// 조회 함수
function search(){

    let startdate = $('#startdate').val();
    let enddate = $('#enddate').val();

    let senddata = new Object();
    senddata.startdate = startdate;
    senddata.enddate = enddate;

    render('material_info/material_input_registration', senddata);

    return null;
};

//빈 데이터셋에 기본값을 설정
//row 추가가 이루어질때 stock_data에 push해줄 빈 데이터셋
var default_set = new Object();

default_set['material_order_sid'] ='';
default_set['material_price'] = 0;
default_set['change_cnt'] = 0;
default_set['amount'] = 0;
default_set['note'] ='';



//인풋값의 번호를 반영하는 데이터 만들기
var order_data = new Array();
order_data.push(default_set);

//추가열
function add_row(){
    
    default_set['material_order_sid'] ='';
    default_set['material_price'] = 0;
    default_set['change_cnt'] = 0;
    default_set['amount'] = 0;
    default_set['note'] ='';


    order_data.push(default_set);

    setTimeout(function(){
        draw();
    },100);

    return null;

}

//열삭제
function row_delete(index){
    index = parseInt(index);

    let temp_data =  order_data;
    order_data = [];

    for(let i=0; i<temp_data.length; i++){

        if(i === index){
            continue;
        }
        order_data.push(temp_data[i]);

    }
    setTimeout(function(){
        draw();
    },100);

    return null;

}

function is_onchange(_this,index){

    index = parseInt(index);

    let parent = $(_this).parent().parent();
    let material_order_sid = parent.find('select[key="material_order_sid"]').val();
    let material_price = 0;
    if (material_order_sid !== '' && material_order_sid !== null && material_order_sid !== undefined) {
        material_price = price_data[material_order_sid];
    }
    let change_cnt = parent.find('input[key="change_cnt"]').val();
    if(change_cnt === '' || change_cnt === null && change_cnt === undefined){
        change_cnt = 0;
    }else{
        change_cnt = parseInt(change_cnt);
    }

    let amount = 0;
    if (material_order_sid !== '' && material_order_sid !== null && material_order_sid !== undefined && change_cnt >= 0) {
        amount = material_price*change_cnt;
    }
    let note = parent.find('input[key="note"]').val();

    let temp = new Object();
    temp.material_order_sid = material_order_sid;
    temp.material_price = material_price;
    temp.change_cnt = change_cnt;
    temp.amount = amount;
    temp.note = note;

    order_data[index] = temp;

    // 계획 가능 수량 제한
    if(change_cnt > remain_cnt[order_data[index]['material_order_sid']]){
        order_data[index]['change_cnt'] = 0;
        order_data[index]['amount'] = 0;
        alert('등록 가능 수량은' + remain_cnt[order_data[index]['material_order_sid']] + '입니다. 줄여주세요');
    }

    // 차감 가능 수량 제한
    if(change_cnt < -(input_cnt[order_data[index]['material_order_sid']])){
        order_data[index]['change_cnt'] = 0;
        order_data[index]['amount'] = 0;
        alert('차감 가능 수량은' + input_cnt[order_data[index]['material_order_sid']] + '입니다. 줄여주세요');
    }

    //품목코드 중복 검사
    let duplication = order_data.filter(ele => ele.material_order_sid === material_order_sid);
    if(duplication.length > 1){
        order_data[index]['material_order_sid'] = '';
        order_data[index]['material_price'] = 0;
        order_data[index]['amount'] = 0;
        order_data[index]['note'] = '';
        alert('자재정보 중복됩니다.');
    }

    parent.find('input[key="material_price"]').val(material_price);
    parent.find('input[key="amount"]').val(amount);

    setTimeout(function(){
        draw();
    },100);

    
}

// 자재발주 선택 함수(해당 발주 리스트 들고오기)
function select_order(_this){

    let number_key = $(_this).val();

    let senddata = new Object();
    senddata.order_number = number_key;

    api('api_material_order_select', senddata, function(output){
        if(output.is_success){

            price_data = output.material_price;// 자재가격
            remain_cnt = output.remain_cnt;    //입고가능 수량
            option_list = output.option_list;  //자재 발주 리스트
            input_cnt = output.input_cnt;      //현재 입고 수량

            default_set =  new Object();
            default_set['material_order_sid'] ='';
            default_set['material_price'] = 0;
            default_set['change_cnt'] = 0;
            default_set['amount'] = 0;
            default_set['note'] ='';


            order_data = new Array();

            order_data.push(default_set);
            console.log(order_data);
            draw();

        }else{
            alert(output.msg);
        }
    }); 

};

// 데이터 그려주는 함수
function draw(){

    let html = '';
    let btn_function = '';
    let btn_text ='';
    let is_match = false;
    
    for(let i=0; i<order_data.length; i++){

        html += '<tr>';
            // select
            html += '<td>';
                html += '<select id="material_order_sid" key="material_order_sid" onchange="is_onchange(this, ' + i + ');">';

                // select option
                    html += '<option value="" selected disabled>자재 선택</option>';
                    for(let j=0; j<option_list.length; j++){
                    is_match = option_list[j]['material_order_sid'].toString() === order_data[i]['material_order_sid'];
                        if(is_match === true){
                    html += '<option value="' + option_list[j]['material_order_sid'] + '" selected>' + option_list[j]['material_name'] + '</option>';
                        }else{
                    html += '<option value="' + option_list[j]['material_order_sid'] + '">' + option_list[j]['material_name'] + '</option>';
                        }
                    }
                html += '</select>';
            html += '</td>';

            // input(price)
            html += '<td>';
                html += '<input key="material_price" type="number" disabled value="' + order_data[i]['material_price'] + '" onchange="is_onchange(this, ' + i + ');" />';
            html += '</td>';

            // input(change_cnt)
            html += '<td>';
                html += '<input key="change_cnt" type="number" onchange="is_onchange(this, ' + i + ');" value="' + order_data[i]['change_cnt'] + '" />';
            html += '</td>';

             // 소계
            html += '<td>';
                html += '<input key="amount" type="number" disabled  value="' +  order_data[i]['amount'] + '" onchange="is_onchange(this, ' + i + ');"  />';
            html += '</td>';

            // note
            html += '<td>';
                html += '<input key="note" type="text" onchange="is_onchange(this, ' + i + ');" value="' + order_data[i]['note'] + '" />';
            html += '</td>';

            // 버튼
            btn_text = '삭제';
            btn_function = 'row_delete(' + i + ');';
            if(i === order_data.length - 1){
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

//자재입고 등록
function registration() {

    if(confirm('정말 등록하시겠습니까?')){

        let order_number = $('#order_number').val();

        for(let i=0; i<order_data.length; i++){

            if(order_data[i]['material_order_sid'] === '') {
                alert((i+1) + '번째 줄의 자재가 선택되지 않았습니다.');
                return false;
            }
            if(order_data[i]['change_cnt'] === ''|| order_data[i]['change_cnt'] ==='0'){
                alert((i+1) + '번째 줄의 자재수량이 입력되지 않았습니다.1');
                return false;
            }

            if(isNaN(order_data[i]['change_cnt']) === true){
                alert((i+1) + '번째 줄의 자재수량의 유효성이 올바르지 않습니다.\n숫자만 입력가능합니다.2');
                return false;
            }

        }

        //샌드데이터 보내기
        let senddata = new Object();
        senddata.order_data = order_data;
        senddata.order_number = order_number;

        api('api_material_input_insert', senddata ,  function(output){
            if(output.is_success){
                render('material_info/material_input');
            }
            alert(output.msg);

        });

        return null;
    }


}
