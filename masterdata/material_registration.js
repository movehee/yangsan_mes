

// 등록/수정 함수
function registration(url){

	if(confirm('정말 등록/수정하시겠습니까?')){

		let material_name = $('#material_name').val();
		let material_price = $('#material_price').val();
		let material_note = $('#material_note').val();

		//자재 유효성 검사
		if(material_name === ''){
			alert('자재명 값이 없습니다.');
			return false;
		}
		if(material_name.length > 100){
			alert('자재명 값의 길이 제한은 100자입니다.');
			return false;
		}
		//
		if(material_price === ''){
			alert('자재단가 값이 없습니다.');
			return false;
		}
		if(material_price.length > 100){
			alert('자재단가 길이 제한은 100자입니다.');
			return false;
		}
		
		
		

		// senddata 만들기
		let senddata = new Object();
		senddata.material_name = material_name;
		senddata.material_price = material_price;
		senddata.material_note = material_note;
		if(material_sid !== ''){
			senddata.material_sid = material_sid;
		}

		//신규일땐 신규url , 수정일 땐 수정url 
		//성공시 자재 페이지로
		api(url, senddata, function(output){
			if(output.is_success){
				render('masterdata/material');

				return null;
			}
			alert(output.msg);
			
		});

		return null;
	}
};