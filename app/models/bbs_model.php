<?php

namespace app\models;

use lib\func_class as model_func; // 공용함수

class bbs_model extends model_func
{
	public $table;

	function __construct()
	{
		parent::__construct();
		$this->table = 'as_bbs_notice';
	}

	/*
	public function bindInsert()
	{
		$arr = [1, "테스트입니다.", "<p>test 입니다.</p>", date("Y-m-d H:i:s")];
		$query = "INSERT ".$this->table." SET fm_id = ?, fa_subject = ?, fa_content = ?, fa_datetime = ?";
		$data = $this->BuildQuery($query, $arr);

		return $data;
	}

	public function bindDel()
	{
		$arr = [20];
		$query = "DELETE FROM ".$this->table." WHERE fa_id = ?";
		$data = $this->BuildQuery($query, $arr);

		return $data;
	}

	public function bindUpDate()
	{
		// update value
		$arr = [
			'바인드쿼리 수정 테스트'
			, '<p>mysqli bind query 테스트</p>'
			, 20
		];
		$query = "UPDATE ".$this->table." SET fa_subject = ?, fa_content = ? WHERE fa_id = ?";
		$data = $this->BuildQuery($query, $arr);

		return $data;
	}

	public function newGet($arr=[])
	{
		if(!$arr) return false;
	
		$arr = [
			"%".$arr[0]."%"
			, $arr[1]
		];
		$query = "SELECT * FROM ".$this->table." WHERE fa_subject LIKE ? AND fm_id = ?";
		$data = $this->BuildQueryOne($query, $arr);

		return $data;
	}

	public function newGetAll()
	{
		// select 기본적으로 넣자
		$arr = ['0', '10'];
		$query = "SELECT * FROM ".$this->table." WHERE 1 = 1 ORDER BY fa_id DESC LIMIT ?, ?";
		$data = $this->BuildQuery($query, $arr);

		return $data;
	}
	*/


	# 리스트
	public function getList($param=[])
	{
		$no = ($param['no'])? $param['no']: 0;
		$viewCnt = ($param['record'])? $param['record']: 10;

		//----- where -----//
		$wQueArr = array();
		$wQueArr[] = "bn_view_flag = ?";
		if( !empty($param['search_sel']) && !empty($param['search_val']) ){
			$wQueArr[] = $param['search_sel']." LIKE ?";
		}
		$wQue = ($wQueArr)? "WHERE ".implode(" AND ", $wQueArr): "";

		//----- data -----//
		$arr = [];
		$arr[] = "Y";
		if( !empty($param['search_sel']) && !empty($param['search_val']) ){
			$arr[] = "%".$param['search_val']."%";
		}
		$arr[] = $no;
		$arr[] = $viewCnt;
		$query = "SELECT * FROM ".$this->table." ".$wQue." ORDER BY bn_idx DESC LIMIT ?, ?";
		$data = $this->BuildQuery($query, $arr);

		//----- count -----//
		$arr = [];
		$arr[] = "Y";
		if( !empty($param['search_sel']) && !empty($param['search_val']) ){
			$arr[] = "%".$param['search_val']."%";
		}
		$query = "SELECT COUNT(*) AS cnt FROM ".$this->table." ".$wQue;
		$dataCnt = $this->BuildQueryOne($query, $arr);
		$data_row = $dataCnt['cnt'];

		//----- return -----//
		return [
			'data' => $data
			, 'data_row' => $data_row
		];
	}
}
