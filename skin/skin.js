
// 드랍다운 메뉴바 열기
function dropdown(_this, bool){
	if(bool === true){
		
		// $('.dropdown').removeClass('hide');
		$(_this).find('.dropdown_list').removeClass('hide');
	}
	if(bool === false){
		
		// $('.dropdown').addClass('hide');
		$(_this).find('.dropdown_list').addClass('hide');
	}
	return null;
};

// 로그아웃 함수
function logout(){

	api('api_logout', { }, function(output){
		if(output.is_success){
			render('index');
			alert('로그아웃이 되었습니다.');
		}
	});

	return null;
};

// 조회화면 전체 선택&해제
// 체크선택 배열 만들기
var checked_sid = new Array();
// 전체선택 함수
function check_all(_this){

	// bool로 변수에 넣어주기
	let is_checked = $(_this).prop('checked');

	checked_sid = new Array();

	let checked = $('input[name="checked"]');

	//조회된 결과의 체크박스들을 is_checked로 넣기
	for(let i=0; i<checked.length; i++){
		checked[i].checked = is_checked;

		//checked들의 거래처sid 값 가져오기
		let this_id = checked[i].getAttribute('id');

		// checkall 클릭시 모두체크 -> 배열에 거래처 sid 넣기
		if(is_checked === true){
			checked_sid.push(this_id);
		}
	}

	return null;
};

// 개별항목 선택&해제
function check_one(_this){

	let is_checked = $(_this).prop('checked');
	let this_sid = $(_this).attr('sid');

	// 선택인 경우
	if(is_checked === true){
		checked_sid.push(this_sid);
	}
	// 해제인 경우
	if(is_checked === false){
		checked_sid = checked_sid.filter(ele => ele !== this_sid);
	}

	return null;
};