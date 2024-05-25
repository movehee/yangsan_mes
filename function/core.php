<?php
session_start();

include $_SERVER['DOCUMENT_ROOT'].'/function/php.php';

$domain = 'http://' . $_SERVER['SERVER_NAME']; // Domain 변수
$file_path = str_replace('.php', '', $_SERVER['PHP_SELF']); // PHP 파일 경로

// 코어타입 상수가 선언되어 있을 때 수행
if (defined('__CORE_TYPE__') === true) {
    $core_type_arr = array('view', 'api');

    // 코어타입의 유효성 검사
    if (in_array(__CORE_TYPE__, $core_type_arr) === true) {
        // 로그인 검사를 하지 않아도 되는 페이지 목록
        $not_login = array('/index', '/join', '/new_pw', '/pw_change', '/api/api_id_exist', '/api/api_join', '/api/api_login', '/api/api_pw_change');

        // 로그인 검사가 필요한 경우
        if (in_array($file_path, $not_login) === false) {
            // 세션 변수 유효성 검사
            if (!isset($_SESSION['id']) || !isset($_SESSION['company_sid'])) {
                echo '<script>location.href="' . $domain . '";</script>';
                exit();
            }

            // 세션 변수 상수화
            define('__USER_ID__', $_SESSION['id']);
            define('__COMPANY_SID__', $_SESSION['company_sid']);

            // 세션 ID를 조건으로 유저 테이블에 회사 코드를 조회
            $select_sql = "SELECT company_sid FROM user_data WHERE id = '" . __USER_ID__ . "'";
            $result_sql = sql($select_sql);
            $result_sql = select_process($result_sql);

            // 조회된 회사 코드가 없을 경우
            if ($result_sql['output_cnt'] === 0) {
                echo '<script>location.href="' . $domain . '";</script>';
                exit();
            }

            // 조회된 회사 코드와 세션의 회사 코드가 일치하지 않을 경우
            if ($result_sql[0]['company_sid'] !== __COMPANY_SID__) {
                echo '<script>location.href="' . $domain . '";</script>';
                exit();
            }
        }

        // 코어타입이 'view'일 때 수행
        if (__CORE_TYPE__ === 'view') {
            $echo_js = 'var domain = "' . $domain . '";'; // 도메인 변수 선언
            $echo_js .= file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/function/js.js'); // js.js 이어 붙이기
            echo '<script>' . $echo_js . '</script>'; // js Core 출력
            echo '<script src="' . $domain . '/lib/jquery.js"></script>'; // Jquery 출력

            // skin 호출(js, css)
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/skin/skin.js')) {
                $skin_js = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/skin/skin.js');
                echo '<script id="skin_js">' . $skin_js . '</script>';
            }
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/skin/skin.css')) {
                $skin_css = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/skin/skin.css');
                echo '<style id="skin_css">' . $skin_css . '</style>';
            }

            // page 호출(js, css)
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $file_path . '.js')) {
                $page_js = file_get_contents($_SERVER['DOCUMENT_ROOT'] . $file_path . '.js');
                echo '<script id="page_js">' . $page_js . '</script>';
            }
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $file_path . '.css')) {
                $page_css = file_get_contents($_SERVER['DOCUMENT_ROOT'] . $file_path . '.css');
                echo '<style id="page_css">' . $page_css . '</style>';
            }

            // 메뉴바 호출
            if (in_array($file_path, $not_login) === false) {
                $topbar = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/topbar.html');
                echo $topbar;
            }
        }

        // 코어타입이 'api'일 때 수행
        if (__CORE_TYPE__ === 'api') {
            $_POST = file_get_contents('php://input');
            $_POST = json_decode($_POST, true);
        }
    }
}
?>
