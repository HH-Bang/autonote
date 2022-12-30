<?php
// 컨트롤러에서는 namespace를 사용하지 않는다.

use lib\func_class as func; // 공용함수
use app\models\test_model as testModel; // model

class Db_test extends func
{
	function __construct()
	{
		parent::__construct();

		// db model 로드
		$this->test_model = new testModel;

		//$cmd = $this->params['cmd'] ?? null;
		$cmd = @$this->params['cmd'] ? @$this->params['cmd'] : null;
		switch( $cmd ){
			case "ins": $this->dataInsert(); break;
			case "del": $this->dataDel(); break;
			case "up": $this->dataUpdate(); break;
			default: $this->Init(); break;
		}
	}

	# main
	public function Init()
	{
		$phpStart = $this->GetTimeChk(); // 수행시간 측정 시작

		

		$arr = ["위드", "1"];
		$rs = $this->test_model->newGet($arr);
		$data1 = json_encode($rs, JSON_UNESCAPED_UNICODE);



		$rs = $this->test_model->newGetAll();
		$data2 = '';
		if( $rs ){
			foreach( $rs as $val ){
				$data2 .= $val['fa_id'].' | '.$val['fm_id'].' | '.$val['fa_subject'].' | '.strip_tags($val['fa_content']).' | '.$val['fa_datetime'].'<br>';
			}
		}else{
			$data2 .= '<p>등록된 내용이 없습니다.</p>';
		}

		print_r($_SESSION);

		$dataArr = [
			'data1' => $data1
			, 'data2' => $data2
		];
		$this->Load_view( '/db_test.html', $dataArr );



		$phpEnd = $this->GetTimeChk(); // 수행시간 측정 끝
		$chkTime = $phpEnd - $phpStart;
		echo number_format($chkTime, 3)." 초 걸림";
	}

	# insert
	public function dataInsert()
	{
		$rs = $this->test_model->bindInsert();
		echo $rs;
	}

	# delete
	public function dataDel()
	{
		$rs = $this->test_model->bindDel();

		if($rs) echo 'delete 성공';
		else echo 'delete 실패';
	}

	# update
	public function dataUpdate()
	{
		$rs = $this->test_model->bindUpDate();

		if($rs) echo 'Update 성공';
		else echo 'Update 실패';
	}
}
