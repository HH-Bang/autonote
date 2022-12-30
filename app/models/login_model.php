<?php
namespace app\models;

use lib\func_class as model_func; // 공용함수

class login_model extends model_func
{
	function __construct()
	{
		parent::__construct();
	}

	public function member( $id='' )
	{
		if (!$id) return;

		$arr = [$id];
		$query = "SELECT * FROM g5_member WHERE mb_id = ?";
		$data = $this->BuildQueryOne($query, $arr);
		return $data;
	}
}
