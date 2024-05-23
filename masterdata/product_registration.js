//품목 등록/수정 js

	function registration(url){

		if(confirm('정말 등록/수정하시겠습니까?')){

			let product_name = $('#product_name').val();
			let product_price = $('#product_price').val();
			let product_code = $('#product_code ').val();
			let product_note = $('#product_note').val();
			//거래처 유효성 검사
			if(product_name === ''){
				alert('품목명의 값이 없습니다.');
				return false;
			}
			if(product_name.length > 100){
				alert('품목명의 값의 길이 제한은 100자입니다.');
				return false;
			}
			//
			if(product_price === ''){
				alert('품목 가격의 값이 없습니다.');
				return false;
			}
			if(product_price.length > 100){
				alert('품목 가격의 값의 길이 제한은 100자입니다.');
				return false;
			}
			//
			if(product_code === ''){
				alert('품목 코드 값이 없습니다.');
				return false;
			}
			if(product_code.length > 100){
				alert('품목 코드 값의 길이 제한은 100자입니다.');
				return false;
			}
		
			

			// senddata 만들기
			let senddata = new Object();
			senddata.product_name = product_name;
			senddata.product_price = product_price;
			senddata.product_code = product_code;
			senddata.product_note = product_note;
			if(product_sid !== ''){
				senddata.product_sid = product_sid;
			}
			//api함수로 데이터 보내기 등록/수정
			api(url, senddata, function(output){
				if(output.is_success){
					render('masterdata/product');

					return null;
				}
				alert(output.msg);
				
			});

			return null;

		}
	};