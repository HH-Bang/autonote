<?
use lib\func_class as func; // 공용함수
use app\models\login_model as loginModel; // model

class login extends func
{
	private $login_model;
	private $baseUrl;

	function __construct()
	{
		parent::__construct();
		
		$this->login_model = new loginModel; // db 로그인 model 로드
		$this->baseUrl = '/db_test'; // 기본 url

		switch( $this->params['cmd'] )
		{
			case "proc": $this->Proc(); break; // 로그인 process
			case "logout": $this->Logout(); break; // 로그아웃
			default: $this->Init(); break;
		}
	}

	# main
	public function Init()
	{
		echo '<h1>Login form</h1>';exit;
		/*
		// 하단
		$this->Load_view( '/inc/top.html', $this->menuArr );

		$paramArr = array();
		$this->Load_view( '/front/login.html', $paramArr );

		// 하단
		$this->Load_view( '/inc/bottom.html', $this->menuArr );
		*/
	}

	# main
	public function Proc()
	{
		//print_r($this->params);exit;
		$id = $this->params['id'];
		$pass = $this->params['pass'];

		$memArr = $this->login_model->member( $id );		
		if (empty($memArr)) {
			echo "<script>alert('가입되지 않은 ID 입니다. 다시 확인 후 로그인 하시기 바랍니다.'); history.back();</script>";
			exit;
		}
		
		// 비밀번호 암호화
		$password = $this->Hash512( $pass );
		if ($memArr['mb_password'] == $password) {
			$id = session_id();
			$mb_id = $memArr['mb_id'];
			$ip_address = $_SERVER['REMOTE_ADDR'];
			$mb_level = $memArr['mb_level'];

			// session 생성
			$_SESSION["id"] = $id;
			$_SESSION["mb_id"] = $mb_id;
			$_SESSION["ip_address"] = $ip_address;
			$_SESSION["mb_level"] = $mb_level;
			
			$url = ($this->params['url'])? $this->params['url']: $this->baseUrl; //이전 URL	
			echo "<script>/*alert('로그인 성공.');*/ location.href = '".$url."';</script>";
		} else {
			echo "<script>alert('비밀번호가 틀렸습니다 다시 입력하시기 바랍니다.'); history.back();</script>";
		}		
	}

	# logout
	public function Logout()
	{
		$_SESSION = [];
		session_destroy();
		echo "<script>/*alert('로그아웃 성공.');*/ location.href = '".$this->baseUrl."';</script>";
	}
}
