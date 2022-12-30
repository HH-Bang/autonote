<?
// 컨트롤러에서는 namespace를 사용하지 않는다.

use lib\func_class as func; // 공용함수

class main extends func
{
	function __construct()
	{
		parent::__construct();
		$this->Init();
	}

	# main
	public function Init()
	{
		$_SESSION['test'] = 'my_session_test_'.date("Y-m-d H:i:s");
		echo "<h1>프레임워크 테스트</h1>";
		phpinfo();
	}
}
?>
