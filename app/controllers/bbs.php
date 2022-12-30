<?php
// 컨트롤러에서는 namespace를 사용하지 않는다.

use lib\func_class as func; // 공용함수
use app\models\bbs_model as bbsModel; // model

class bbs extends func
{
	private $bbs_model;
	private $searchSelArr;

	function __construct()
	{
		parent::__construct();

		// db model 로드
		$this->bbs_model = new bbsModel;

		// select
		$this->searchSelArr = [
			'bn_name' => '이름'
			, 'bn_title' => '제목'
		];

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
		#--------------- list ---------------#
		$record = ($this->params['view_cnt'])? $this->params['view_cnt']: 10;
		$page_view = 10;
		$no = ($this->params['no'])? $this->params['no']: 0;

		$arr = [
			'no' => $no
			, 'record' => $record
			, 'search_sel' => $this->params['search_sel']
			, 'search_val' => $this->params['search_val']
		];
		$data = $this->bbs_model->getList($arr);
		$dataArr = $data['data'];
		$total_row = $data['data_row'];
		
		$html = '';
		$k = 0;
		if ($dataArr) {
			$num = $total_row - $k - $no;
			foreach ($dataArr as $val) {
				$html .= '<tr>';
					$html .= '<td>'.$num.'</td>';
					$html .= '<td>'.$val['bn_name'].'</td>';
					$html .= '<td>'.$val['bn_title'].'</td>';
					$html .= '<td>'.$val['bn_count'].'</td>';
					$html .= '<td>'.$val['bn_reg_date'].'</td>';
				$html .= '</tr>';
				$k++;
			}
		} else {
			$html .= '<tr><td colspan="5">등록된 내용이 없습니다.</td></tr>';
		}

		#--------------- paging 처리 기본값 ---------------#
		$pgArr = ['record'=>$record, 'page_view'=>$page_view, 'no'=>$no, 'total_row'=>$total_row];
		$html_paging = $this->PageMake($pgArr, $this->params);

		$selBox = '';
		foreach ($this->searchSelArr as $k=>$val) {
			$selected = ($k==$this->params['search_sel'])? 'selected': '';
			$selBox .= '<option value="'.$k.'" '.$selected.'>'.$val.'</option>';
		}

		$dataArr = [
			'html' => $html
			, 'html_paging' => $html_paging
			, 'selBox' => $selBox
			, 'selVal' => (($this->params['search_val'])? $this->params['search_val']: '')
		];
		$this->Load_view('/bbs_list.html', $dataArr);
	}

	# insert
	public function dataInsert()
	{
		$rs = $this->bbs_model->bindInsert();
		echo $rs;
	}

	# delete
	public function dataDel()
	{
		$rs = $this->bbs_model->bindDel();

		if($rs) echo 'delete 성공';
		else echo 'delete 실패';
	}

	# update
	public function dataUpdate()
	{
		$rs = $this->bbs_model->bindUpDate();

		if($rs) echo 'Update 성공';
		else echo 'Update 실패';
	}
}
