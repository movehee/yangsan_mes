<?php

	define('__CORE_TYPE__', 'view');
	include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';


?>
<head>
  <meta charset='utf-8' />
  <!-- 화면 해상도에 따라 글자 크기 대응(모바일 대응) -->
  <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
  <!-- jquery CDN -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- fullcalendar CDN -->
  <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.8.0/main.min.css' rel='stylesheet' />
  <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.8.0/main.min.js'></script>
  <!-- fullcalendar 언어 CDN -->
  <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.8.0/locales-all.min.js'></script>
<style>
  /* body 스타일 */
  html, body {
    overflow: hidden;
    font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
    font-size: 14px;

  }
.fc-button-group {
  display: flex; /* 버튼들을 가로로 배치하기 위해 */
  justify-content: space-around; /* 버튼들을 동일한 간격으로 배치 */
}

.fc-button-group button {
  width: 50px; /* 버튼의 너비 */
  height: 50px; /* 버튼의 높이 */
  border-radius: 5px; /* 버튼의 모서리를 둥글게 만듦 */
  background-color: #0f5b63; /* 버튼의 배경 색상 */
  color: #ffffff; /* 버튼의 글자 색상 */
  border: none; /* 버튼의 테두리 제거 */
}
.fc-button-group button:hover {
  background-color: #0b3438; /* 마우스를 올렸을 때 버튼의 배경 색상 변경 */
}
.fc-today-button.fc-button.fc-button-primary {
  width: 50px !important; /* 너비 */
  height: 50px !important; /* 높이 */
}
.fc-header-toolbar {
  padding-top: 1em;
  padding-left: 1em;
  padding-right: 1em;
  background-color: skyblue;
  padding-bottom: 14px;
}
.fc-header-toolbar.fc-toolbar.fc-toolbar-ltr{
	background-color: skyblue;
	margin: 10px;
	border: 1px solid skyblue;
}
.fc-col-header-cell {
  background-color: skyblue; /* 배경색 지정 */
  border-bottom: 1px solid #ccc; /* 아래쪽 테두리 추가 */
}
.fc-daygrid-day.fc-day.fc-day-tue.fc-day-today  {
  background-color: skyblue; 
  border: 2px solid white;
}
#calendar-container{
  margin: auto;
  align-items: center;
  width: 1350px;
}
#home_btn {
    font-size: 20px; 
}
#calendar-container{
  width: 80%;
  height: 80%;
}
</style>
</head>
<body style="padding:30px;">
  <!-- calendar 태그 -->
  <div id='calendar-container'>
    <div id='calendar'></div>
  </div>