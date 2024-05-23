function is_onchange(_this, index){
	index = parseInt(index);
	let parent = $(_this).parent().parent();

	//입력값
	let player_key = parent.find('select[key="home_batter_' + index + '_name"]').val(); // 선수명
	let pa_key = parent.find('input[key="home_batter_' + index + '_pa"]').val(); // 타석
	let ab_key = parent.find('input[key="home_batter_' + index + '_ab"]').val(); // 타수
	let hits_key = parent.find('input[key="home_batter_' + index + '_hits"]').val(); // 안타
}