 <?
 # 타임존 설정
date_default_timezone_set('Asia/Seoul');

# db
//define('DBCONFIG_PATH', $_SERVER['DOCUMENT_ROOT'].'/lib/conf.ini');
//define('DBCONFIG_PATH', 'E:/dev_www/dev_8080/conf.ini');
define('DBCONFIG_PATH', 'D:/public_html/www/config/conf.ini');

# html 경로
define('HTML_PATH', 'view');

# 이미지
define('IMAGES_PATH', '/images');

# upload 파일 경로
define('UPLOAD_PATH', '/data');

# 컨트롤러 경로
define('CONTROLLERS_PATH', '/app/controllers');

# view 경로
define('VIEW_PATH', '/view');

# front main 페이지 경로
define('INDEX_PAGE_PATH', '/main');

# db이름 - conf.ini 안에 이름 [local_db]
define('DB_SEl_NAME', 'local_db');

# 암호화/복화화 키
define('CRYPT_KEY', '@ahxjvpdl!'); // @모터페이!

#https 통신일때 daum 주소 js
//if($_SERVER['REQUEST_SCHEME']=='https'){
//	$jsSrc = '<script src="https://spi.maps.daum.net/imap/map_js_init/postcode.js"></script>';
//}else{
	$jsSrc = '<script src="http://dmaps.daum.net/map_js_init/postcode.js"></script>';
//}
define('G5_POSTCODE_JS', $jsSrc);

# 개발서버 or 실서버
define('LOCATION_URL', 'http://127.0.0.1:8080');

/*
# 푸시 알람 아이디
define('PUSH_APP_ID', '418bb2cf-0750-4a51-9758-2c8b6f8a0099');

# 푸시 알람 키값
define('PUSH_RESTAPI_KEY', 'MDBhMzUxODItNTNiMS00MjkxLWJlYzAtM2ZkMWRhZWNhMmZi');

# 네이버 아이디 로그인
define('NAVER_CLIENT_ID', 'n6F6FWoK46KWD2uXtnMd');
define('NAVER_CLIENT_SECRET', 'A9uxdXFVnU');
define('NAVER_CALLBACK_URL', 'http://modooclean.com/front/login?cmd=callback');

# 카카오 아이디 로그인
define('KAKAO_ID_KEY', 'cb6b097bc30969262906087dc1113f55');
define('KAKAO_API_KEY', '7bac7d8ccdf04d3e2df3824cab3a8636');
define('KAKAO_CALLBACK_URL', 'http://modooclean.com/front/login?cmd=callback_kakao');
*/
?>