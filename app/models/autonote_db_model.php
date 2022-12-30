<?php

namespace app\models;

use lib\func_class as model_func; // 공용함수

class autonote_db_model extends model_func
{
	public $table;
	public $queSetArr;

	function __construct()
	{
		parent::__construct();
		$this->table = 'cardata_220725082058';

		$this->queSetArr = [
			'modelDepth1' => 'carinfo1'
			, 'modelDepth2' => 'carseries'
			, 'modelDepth3' => 'carinfo2'
			, 'modelDepth4' => 'carinfo3'
			, 'modelDepth5' => 'carinfo4'
		];
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

	public function getData($param=[])
	{
		$arr = [0, 10];
		$query = "SELECT * FROM ".$this->table." ".$wQue." ORDER BY seq DESC LIMIT ?, ?";
		$data = $this->BuildQuery($query, $arr, 'local_autosale_db');
		return $data;
	}

	# 리스트
	public function getList($param=[])
	{
		//print_r($param);exit;
		$modelQueArr = empty($param['modelQue']) ? []: $param['modelQue'];
		$carArr = empty($param['carTypeArr']) ? []: $param['carTypeArr'];
		$yearArr = empty($param['year']) ? []: $param['year'];
		$kmArr = empty($param['km']) ? []: $param['km']; // 주행거리
		$priceArr = empty($param['price']) ? []: $param['price']; // 가격
		$saleAreaArr = empty($param['saleArea']) ? []: $param['saleArea']; // 지역
		$fuelArr = empty($param['fuel']) ? []: $param['fuel']; // 연료
		$gearTypeArr = empty($param['gearType']) ? []: $param['gearType']; // 변속기
		$accident = empty($param['accident']) ? []: $param['accident']; // 사고유무
		$color = empty($param['color']) ? []: $param['color']; // 사고유무
		$option = empty($param['option']) ? []: $param['option']; // 옵션 - 바인드 사용하지 않음

		// 페이네이션 용
		$no = empty($param['listParam']['no']) ? 0: $param['listParam']['no'];
		$viewCnt = empty($param['listParam']['record']) ? 15: $param['listParam']['record'];


		// where 
		if (!empty($modelQueArr) || !empty($carArr) || !empty($yearArr) || !empty($kmArr) || !empty($priceArr) || !empty($saleAreaArr) || !empty($fuelArr) || !empty($gearTypeArr) || !empty($accident) || !empty($color) || !empty($option))
		{
			$wQueArr = [];

			if (!empty($modelQueArr)) 
			{				
				$strQueArr = $this->whereQue($param); // 검색쿼리 배열
				$resetArr = []; // 차종 배열 다시 셋팅
				foreach ($modelQueArr as $value) {
					$strQueData = (count($strQueArr) > 0) ? $value." AND ".implode(" AND ", $strQueArr): $value;
					$wQueArr[] = "(".$strQueData.")"; // 검색쿼리 합체

					// 기존배열 갯수도 쿼리만큼 늘려야 함
					$addArr = $this->queValArr($param);
					$resetArr = array_merge($resetArr, $addArr); // 배열 합체
				}
				
				$wQue = implode(" OR ", $wQueArr); // 쿼리문 합체
				$setArr = $resetArr; // 배열 합체
			} 
			else 
			{
				$strQueArr = $this->whereQue($param); // 검색쿼리 배열
				$wQue = implode(" AND ", $strQueArr); // 쿼리문 합체	
				$setArr = array_merge($carArr, $yearArr, $kmArr, $priceArr, $saleAreaArr, $fuelArr, $gearTypeArr, $accident, $color); // 배열 합체
			}
		} else {
			$setArr = [1];
			$wQue = "1 = ?";
		}


		// bind array 리미트
		$limitArr = [];
		$limitArr[] = $no;
		$limitArr[] = $viewCnt;

		// 배열 합체
		$mergeArr = array_merge($setArr, $limitArr); 

		// data
		$query = "SELECT * FROM ".$this->table." WHERE ".$wQue." ORDER BY seq DESC LIMIT ?, ?";
		$data = $this->BuildQuery($query, $mergeArr, 'local_autosale_db');
		//echo $query; print_r($mergeArr);
		// count
		$query = "SELECT COUNT(*) AS cnt FROM ".$this->table." WHERE ".$wQue;
		$dataCnt = $this->BuildQueryOne($query, $setArr, 'local_autosale_db');
		$data_row = $dataCnt['cnt'];

		return [
			'data' => $data
			, 'data_row' => $data_row
		];
	}

	# 제조사/모델 where 
	public function modelWhereQue($searchArr=[])
	{
		$wQueStr1 = '';
		$wQueStr2 = '';
		$wQueStr3 = '';
		$wQueStr4 = '';
		$wQueStr5 = '';
		$wQueArr = [];

		foreach ($searchArr as $k1=>$val1) {
			$wQueStr1 = "carinfo1 = '".$k1."'";
			if (empty($val1)) {
				$wQueArr[] = $wQueStr1;
			} else {
				foreach ($val1 as $k2=>$val2) {
					$wQueStr2 = $wQueStr1." AND carseries = '".$k2."'";
					if (empty($val2)) {
						$wQueArr[] = $wQueStr2;
					} else {
						foreach ($val2 as $k3=>$val3) {
							$wQueStr3 = $wQueStr2." AND carinfo2 = '".$k3."'";
							if (empty($val3)) {
								$wQueArr[] = $wQueStr3;
							} else {
								foreach ($val3 as $k4=>$val4) {
									$wQueStr4 = $wQueStr3." AND carinfo3 = '".$k4."'";
									if (empty($val4)) {
										$wQueArr[] = $wQueStr4;
									} else {
										foreach ($val4 as $k5=>$val5) {
											$wQueStr5 = $wQueStr4." AND carinfo4 = '".$k5."'";
											$wQueArr[] = $wQueStr5;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		//print_r($wQueArr);

		return $wQueArr;
	}

	# 쿼리문 배열 셋팅
	public function whereQue($param=[], $type='Y')
	{
		// 차종 검색이 있을때
		$carArr = empty($param['carTypeArr']) ? []: $param['carTypeArr'];
		$yearArr = empty($param['year']) ? []: $param['year'];
		$kmArr = empty($param['km']) ? []: $param['km']; // 주행거리
		$priceArr = empty($param['price']) ? []: $param['price']; // 가격
		$saleAreaArr = empty($param['saleArea']) ? []: $param['saleArea']; // 지역
		$fuelArr = empty($param['fuel']) ? []: $param['fuel']; // 연료
		$gearTypeArr = empty($param['gearType']) ? []: $param['gearType']; // 변속기
		$accident = empty($param['accident']) ? []: $param['accident']; // 사고유무
		$color = empty($param['color']) ? []: $param['color']; // 사고유무
		$option = empty($param['option']) ? []: $param['option']; // 옵션
		//$modelQueArr = empty($param['modelQue']) ? []: $param['modelQue'];

		$strQueArr = [];

		// 차종검색
		if (!empty($carArr) && $type == 'Y') {
			$inArr = [];
			foreach ($carArr as $val) $inArr[] = "?";
			$strQueArr[] = "cartype IN (".implode(",", $inArr).")"; // 차종검색
		}

		// 연식
		if (!empty($yearArr)) {
			$strQueArr[] = "caryear2 BETWEEN ? AND ?"; // 연식
		}

		// 주행거리
		if (!empty($kmArr)) {
			// 주행종료거리가 20만이면 쿼리문 변경 
			if ($kmArr[1] == "200000") {
				$kmArr[1] = 1000000; // 100만으로 변경
				$strQueArr[] = "carkm >= ? AND carkm < ?"; 
			} else {
				$strQueArr[] = "carkm BETWEEN ? AND ?"; 
			}
		}

		// 가격
		if (!empty($priceArr)) {
			// 1억원 이상 이면 쿼리문 변경 
			if ($priceArr[1] == "10001") {
				$priceArr[1] = 100000; // 10억으로 변경
				$strQueArr[] = "carmoney >= ? AND carmoney < ?"; 
			} else {
				$strQueArr[] = "carmoney BETWEEN ? AND ?"; 
			}
		}

		// 지역
		if (!empty($saleAreaArr)) {
			$inArr = [];
			foreach ($saleAreaArr as $val) $inArr[] = "?";
			$strQueArr[] = "carsalearea1 IN (".implode(",", $inArr).")";
		}

		// 연료
		if (!empty($fuelArr)) {
			$inArr = [];
			foreach ($fuelArr as $val) $inArr[] = "?";
			$strQueArr[] = "caroil IN (".implode(",", $inArr).")";
		}

		// 변속기
		if (!empty($gearTypeArr)) {
			$inArr = [];
			foreach ($gearTypeArr as $val) $inArr[] = "?";
			$strQueArr[] = "carauto IN (".implode(",", $inArr).")";
		}

		// 사고유무
		if (!empty($accident)) {
			$inArr = [];
			foreach ($accident as $val) $inArr[] = "?";
			$strQueArr[] = "carsafe IN (".implode(",", $inArr).")";
		}

		// 색상
		if (!empty($color)) {
			$inArr = [];
			foreach ($color as $val) $inArr[] = "?";
			$strQueArr[] = "carcolor IN (".implode(",", $inArr).")";
		}

		// 옵션
		if (!empty($option)) {
			$inArr = [];
			foreach ($option as $val) $inArr[] = $val;
			$strQueArr[] = "(MATCH(caroption) AGAINST('".implode(" +", $inArr)."' IN BOOLEAN MODE))";
		}

		return $strQueArr;
	}

	# 바인드 배열값
	public function queValArr($param=[], $type='Y')
	{
		// 차종 검색이 있을때
		$carArr = empty($param['carTypeArr']) ? []: $param['carTypeArr'];
		$yearArr = empty($param['year']) ? []: $param['year'];
		$kmArr = empty($param['km']) ? []: $param['km']; // 주행거리
		$priceArr = empty($param['price']) ? []: $param['price']; // 가격
		$saleAreaArr = empty($param['saleArea']) ? []: $param['saleArea']; // 지역
		$fuelArr = empty($param['fuel']) ? []: $param['fuel']; // 연료
		$gearTypeArr = empty($param['gearType']) ? []: $param['gearType']; // 변속기
		$accident = empty($param['accident']) ? []: $param['accident']; // 사고유무
		$color = empty($param['color']) ? []: $param['color']; // 사고유무

		$resetArr = []; // 차종 배열 다시 셋팅

		// 차종검색
		if (!empty($carArr) && $type == 'Y') {
			foreach ($carArr as $v) {
				$resetArr[] = $v;
			}
		}

		// 연식
		foreach ($yearArr as $v) {
			$resetArr[] = $v;
		}

		// 주행거리
		foreach ($kmArr as $v) {
			$resetArr[] = $v;
		}

		// 가격
		foreach ($priceArr as $v) {
			$resetArr[] = $v;
		}

		// 지역
		foreach ($saleAreaArr as $v) {
			$resetArr[] = $v;
		}

		// 연료
		foreach ($fuelArr as $v) {
			$resetArr[] = $v;
		}

		// 변속기
		foreach ($gearTypeArr as $v) {
			$resetArr[] = $v;
		}

		// 사고유무
		foreach ($accident as $v) {
			$resetArr[] = $v;
		}

		// 색상
		foreach ($color as $v) {
			$resetArr[] = $v;
		}

		return $resetArr;
	}

	# 제조사, 모델 메뉴 : 제조사
	public function menuModelDep1($param=[])
	{
		$aArr = [1];
		$query = "SELECT carinfo1, COUNT(*) AS cnt FROM ".$this->table." WHERE 1 = ? GROUP BY carinfo1 ORDER BY cnt DESC, caryear2 DESC";
		$data = $this->BuildQuery($query, $aArr, 'local_autosale_db');

		// 차종 검색이 있을때
		$carArr = empty($param['carTypeArr']) ? []: $param['carTypeArr'];
		$yearArr = empty($param['year']) ? []: $param['year'];
		$kmArr = empty($param['km']) ? []: $param['km']; // 주행거리
		$priceArr = empty($param['price']) ? []: $param['price']; // 가격
		$saleAreaArr = empty($param['saleArea']) ? []: $param['saleArea']; // 지역
		$fuelArr = empty($param['fuel']) ? []: $param['fuel']; // 연료
		$gearTypeArr = empty($param['gearType']) ? []: $param['gearType']; // 변속기
		$accident = empty($param['accident']) ? []: $param['accident']; // 사고유무
		$color = empty($param['color']) ? []: $param['color']; // 사고유무
		$modelQueArr = empty($param['modelQue']) ? []: $param['modelQue'];
		$option = empty($param['option']) ? []: $param['option']; // 옵션 - 바인드 사용하지 않음

		if (!empty($carArr) || !empty($yearArr) || !empty($kmArr) || !empty($priceArr) || !empty($saleAreaArr) || !empty($fuelArr) || !empty($gearTypeArr) || !empty($accident) || !empty($color) || !empty($modelQueArr) || !empty($option))
		{
			$wQueArr = [];

			if (!empty($modelQueArr)) 
			{				
				$strQueArr = $this->whereQue($param); // 검색쿼리 배열
				$resetArr = []; // 차종 배열 다시 셋팅
				foreach ($modelQueArr as $value) {
					$strQueData = (count($strQueArr) > 0) ? $value." AND ".implode(" AND ", $strQueArr): $value;
					$wQueArr[] = "(".$strQueData.")"; // 검색쿼리 합체

					// 기존배열 갯수도 쿼리만큼 늘려야 함
					$addArr = $this->queValArr($param);
					$resetArr = array_merge($resetArr, $addArr); // 배열 합체
				}
				
				$wQue = implode(" OR ", $wQueArr); // 쿼리문 합체
				$mergeArr = $resetArr; // 배열 합체
			} 
			else 
			{
				$strQueArr = $this->whereQue($param); // 검색쿼리 배열
				$wQue = implode(" AND ", $strQueArr); // 쿼리문 합체	
				$mergeArr = array_merge($carArr, $yearArr, $kmArr, $priceArr, $saleAreaArr, $fuelArr, $gearTypeArr, $accident, $color); // 배열 합체
			}
			
			$query = "SELECT carinfo1, COUNT(*) AS cnt FROM ".$this->table." WHERE ".$wQue." GROUP BY carinfo1";
			$aData = $this->BuildQuery($query, $mergeArr, 'local_autosale_db');

			// 가지고 온 데이터를 자료 배열로 변환
			$reData = [];
			foreach ($aData as $val) {
				$reData[$val['carinfo1']] = $val['cnt'];
			}

			// 수량 치환
			foreach ($data as $k=>$val) {
				if (array_key_exists($val['carinfo1'], $reData)) {
					$data[$k]['cnt'] = $reData[$val['carinfo1']];
				} else {
					$data[$k]['cnt'] = 0;
				}
			}
		}

		return $data;
	}

	# 제조사, 모델 메뉴 : 모델
	public function menuModelDep2($str='', $param=[])
	{
		if (empty($str)) return;
		
		$arr = [$str];
		$strQue = "carinfo1 = ?";

		$query = "SELECT carseries, COUNT(*) AS cnt FROM ".$this->table." WHERE ".$strQue." GROUP BY carseries ORDER BY cnt DESC, caryear2 DESC";
		$data = $this->BuildQuery($query, $arr, 'local_autosale_db');

		// 차종 검색이 있을때
		$carArr = empty($param['carTypeArr']) ? []: $param['carTypeArr'];
		$yearArr = empty($param['year']) ? []: $param['year'];
		$kmArr = empty($param['km']) ? []: $param['km']; // 주행거리
		$priceArr = empty($param['price']) ? []: $param['price']; // 가격
		$saleAreaArr = empty($param['saleArea']) ? []: $param['saleArea']; // 지역
		$fuelArr = empty($param['fuel']) ? []: $param['fuel']; // 연료
		$gearTypeArr = empty($param['gearType']) ? []: $param['gearType']; // 변속기
		$accident = empty($param['accident']) ? []: $param['accident']; // 사고유무
		$color = empty($param['color']) ? []: $param['color']; // 사고유무
		$modelQueArr = empty($param['modelQue']) ? []: $param['modelQue'];
		$option = empty($param['option']) ? []: $param['option']; // 옵션 - 바인드 사용하지 않음

		if (!empty($carArr) || !empty($yearArr) || !empty($kmArr) || !empty($priceArr) || !empty($saleAreaArr) || !empty($fuelArr) || !empty($gearTypeArr) || !empty($accident) || !empty($color) || !empty($modelQueArr) || !empty($option))
		{
			$wQueArr = [];

			if (!empty($modelQueArr)) 
			{
				$strQueArr = $this->whereQue($param); // 검색쿼리 배열
				$resetArr = []; // 차종 배열 다시 셋팅
				foreach ($modelQueArr as $value) {
					$strQueData = (count($strQueArr) > 0) ? $value." AND ".implode(" AND ", $strQueArr): $value;
					$wQueArr[] = "(".$strQueData.")"; // 검색쿼리 합체

					// 기존배열 갯수도 쿼리만큼 늘려야 함
					$addArr = $this->queValArr($param);
					$resetArr = array_merge($resetArr, $addArr); // 배열 합체
				}

				$wQue = implode(" OR ", $wQueArr); // 쿼리문 합체
				$mergeArr = $resetArr; // 배열 합체
			} 
			else 
			{
				$strQueArr = $this->whereQue($param); // 검색쿼리 배열
				$wQue = implode(" AND ", $strQueArr); // 쿼리문 합체
				$mergeArr = array_merge($arr, $carArr, $yearArr, $kmArr, $priceArr, $saleAreaArr, $fuelArr, $gearTypeArr, $accident, $color); // 배열 합체
			}
			

			$query = "SELECT carseries, COUNT(*) AS cnt FROM ".$this->table." WHERE ".$wQue." GROUP BY carseries";
			$aData = $this->BuildQuery($query, $mergeArr, 'local_autosale_db');

			// 가지고 온 데이터를 자료 배열로 변환
			$reData = [];
			foreach ($aData as $val) {
				$reData[$val['carseries']] = $val['cnt'];
			}

			// 수량 치환
			foreach ($data as $k=>$val) {
				if (array_key_exists($val['carseries'], $reData)) {
					$data[$k]['cnt'] = $reData[$val['carseries']];
				} else {
					$data[$k]['cnt'] = 0;
				}
			}
		}

		return $data;
	}

	# 제조사, 모델 메뉴 : 차량이름
	public function menuModelDep3($str='', $param=[])
	{
		if (empty($str)) return;
		
		$arr = [$str];
		$strQue = "carseries = ?";

		$query = "SELECT carinfo2, COUNT(*) AS cnt FROM ".$this->table." WHERE ".$strQue." GROUP BY carinfo2 ORDER BY cnt DESC, caryear2 DESC";
		$data = $this->BuildQuery($query, $arr, 'local_autosale_db');

		// 차종 검색이 있을때
		$carArr = empty($param['carTypeArr']) ? []: $param['carTypeArr'];
		$yearArr = empty($param['year']) ? []: $param['year'];
		$kmArr = empty($param['km']) ? []: $param['km']; // 주행거리
		$priceArr = empty($param['price']) ? []: $param['price']; // 가격
		$saleAreaArr = empty($param['saleArea']) ? []: $param['saleArea']; // 지역
		$fuelArr = empty($param['fuel']) ? []: $param['fuel']; // 연료
		$gearTypeArr = empty($param['gearType']) ? []: $param['gearType']; // 변속기
		$accident = empty($param['accident']) ? []: $param['accident']; // 사고유무
		$color = empty($param['color']) ? []: $param['color']; // 사고유무
		$modelQueArr = empty($param['modelQue']) ? []: $param['modelQue'];
		$option = empty($param['option']) ? []: $param['option']; // 옵션 - 바인드 사용하지 않음

		if (!empty($carArr) || !empty($yearArr) || !empty($kmArr) || !empty($priceArr) || !empty($saleAreaArr) || !empty($fuelArr) || !empty($gearTypeArr) || !empty($accident) || !empty($color) || !empty($modelQueArr) || !empty($option))
		{
			$wQueArr = [];

			if (!empty($modelQueArr)) 
			{
				$strQueArr = $this->whereQue($param); // 검색쿼리 배열
				$resetArr = []; // 차종 배열 다시 셋팅
				foreach ($modelQueArr as $value) {
					$strQueData = (count($strQueArr) > 0) ? $value." AND ".implode(" AND ", $strQueArr): $value;
					$wQueArr[] = "(".$strQueData.")"; // 검색쿼리 합체

					// 기존배열 갯수도 쿼리만큼 늘려야 함
					$addArr = $this->queValArr($param);
					$resetArr = array_merge($resetArr, $addArr); // 배열 합체
				}

				$wQue = implode(" OR ", $wQueArr); // 쿼리문 합체
				$mergeArr = $resetArr; // 배열 합체
			} 
			else 
			{
				$strQueArr = $this->whereQue($param); // 검색쿼리 배열
				$wQue = implode(" AND ", $strQueArr); // 쿼리문 합체
				$mergeArr = array_merge($arr, $carArr, $yearArr, $kmArr, $priceArr, $saleAreaArr, $fuelArr, $gearTypeArr, $accident, $color); // 배열 합체
			}

			$query = "SELECT carinfo2, COUNT(*) AS cnt FROM ".$this->table." WHERE ".$wQue." GROUP BY carinfo2";
			$aData = $this->BuildQuery($query, $mergeArr, 'local_autosale_db');

			// 가지고 온 데이터를 자료 배열로 변환
			$reData = [];
			foreach ($aData as $val) {
				$reData[$val['carinfo2']] = $val['cnt'];
			}

			// 수량 치환
			foreach ($data as $k=>$val) {
				if (array_key_exists($val['carinfo2'], $reData)) {
					$data[$k]['cnt'] = $reData[$val['carinfo2']];
				} else {
					$data[$k]['cnt'] = 0;
				}
			}
		}

		return $data;
	}

	# 제조사, 모델 메뉴 : 등급, 연료
	public function menuModelDep4($str='', $param=[])
	{
		if (empty($str)) return;
		
		$arr = [$str];
		$strQue = "carinfo2 = ?";

		$query = "SELECT carinfo3, COUNT(*) AS cnt FROM ".$this->table." WHERE ".$strQue." GROUP BY carinfo3 ORDER BY cnt DESC, caryear2 DESC";
		$data = $this->BuildQuery($query, $arr, 'local_autosale_db');

		// 차종 검색이 있을때
		$carArr = empty($param['carTypeArr']) ? []: $param['carTypeArr'];
		$yearArr = empty($param['year']) ? []: $param['year'];
		$kmArr = empty($param['km']) ? []: $param['km']; // 주행거리
		$priceArr = empty($param['price']) ? []: $param['price']; // 가격
		$saleAreaArr = empty($param['saleArea']) ? []: $param['saleArea']; // 지역
		$fuelArr = empty($param['fuel']) ? []: $param['fuel']; // 연료
		$gearTypeArr = empty($param['gearType']) ? []: $param['gearType']; // 변속기
		$accident = empty($param['accident']) ? []: $param['accident']; // 사고유무
		$color = empty($param['color']) ? []: $param['color']; // 사고유무
		$modelQueArr = empty($param['modelQue']) ? []: $param['modelQue'];
		$option = empty($param['option']) ? []: $param['option']; // 옵션 - 바인드 사용하지 않음

		if (!empty($carArr) || !empty($yearArr) || !empty($kmArr) || !empty($priceArr) || !empty($saleAreaArr) || !empty($fuelArr) || !empty($gearTypeArr) || !empty($accident) || !empty($color) || !empty($modelQueArr) || !empty($option))
		{
			$wQueArr = [];

			if (!empty($modelQueArr)) 
			{
				$strQueArr = $this->whereQue($param); // 검색쿼리 배열
				$resetArr = []; // 차종 배열 다시 셋팅
				foreach ($modelQueArr as $value) {
					$strQueData = (count($strQueArr) > 0) ? $value." AND ".implode(" AND ", $strQueArr): $value;
					$wQueArr[] = "(".$strQueData.")"; // 검색쿼리 합체

					// 기존배열 갯수도 쿼리만큼 늘려야 함
					$addArr = $this->queValArr($param);
					$resetArr = array_merge($resetArr, $addArr); // 배열 합체
				}

				$wQue = implode(" OR ", $wQueArr); // 쿼리문 합체
				$mergeArr = $resetArr; // 배열 합체
			} 
			else 
			{
				$strQueArr = $this->whereQue($param); // 검색쿼리 배열
				$wQue = implode(" AND ", $strQueArr); // 쿼리문 합체
				$mergeArr = array_merge($arr, $carArr, $yearArr, $kmArr, $priceArr, $saleAreaArr, $fuelArr, $gearTypeArr, $accident, $color); // 배열 합체
			}

			$query = "SELECT carinfo3, COUNT(*) AS cnt FROM ".$this->table." WHERE ".$wQue." GROUP BY carinfo3";
			$aData = $this->BuildQuery($query, $mergeArr, 'local_autosale_db');

			// 가지고 온 데이터를 자료 배열로 변환
			$reData = [];
			foreach ($aData as $val) {
				$reData[$val['carinfo3']] = $val['cnt'];
			}

			// 수량 치환
			foreach ($data as $k=>$val) {
				if (array_key_exists($val['carinfo3'], $reData)) {
					$data[$k]['cnt'] = $reData[$val['carinfo3']];
				} else {
					$data[$k]['cnt'] = 0;
				}
			}
		}

		return $data;
	}

	# 제조사, 모델 메뉴 : 트림종류
	public function menuModelDep5($info2='', $info3='', $param=[])
	{
		if (empty($info2) && empty($info3)) return;
			
		$arr = [$info2, $info3];
		$strQue = "carinfo2 = ? AND carinfo3 = ?";

		$query = "SELECT carinfo4, COUNT(*) AS cnt FROM ".$this->table." WHERE ".$strQue." GROUP BY carinfo4 ORDER BY cnt DESC, caryear2 DESC";
		$data = $this->BuildQuery($query, $arr, 'local_autosale_db');
		
		// 차종 검색이 있을때
		$carArr = empty($param['carTypeArr']) ? []: $param['carTypeArr'];
		$yearArr = empty($param['year']) ? []: $param['year'];
		$kmArr = empty($param['km']) ? []: $param['km']; // 주행거리
		$priceArr = empty($param['price']) ? []: $param['price']; // 가격
		$saleAreaArr = empty($param['saleArea']) ? []: $param['saleArea']; // 지역
		$fuelArr = empty($param['fuel']) ? []: $param['fuel']; // 연료
		$gearTypeArr = empty($param['gearType']) ? []: $param['gearType']; // 변속기
		$accident = empty($param['accident']) ? []: $param['accident']; // 사고유무
		$color = empty($param['color']) ? []: $param['color']; // 사고유무
		$modelQueArr = empty($param['modelQue']) ? []: $param['modelQue'];
		$option = empty($param['option']) ? []: $param['option']; // 옵션 - 바인드 사용하지 않음

		if (!empty($carArr) || !empty($yearArr) || !empty($kmArr) || !empty($priceArr) || !empty($saleAreaArr) || !empty($fuelArr) || !empty($gearTypeArr) || !empty($accident) || !empty($color) || !empty($modelQueArr) || !empty($option))
		{
			$wQueArr = [];

			if (!empty($modelQueArr)) 
			{
				$strQueArr = $this->whereQue($param); // 검색쿼리 배열
				$resetArr = []; // 차종 배열 다시 셋팅
				foreach ($modelQueArr as $value) {
					$strQueData = (count($strQueArr) > 0) ? $value." AND ".implode(" AND ", $strQueArr): $value;
					$wQueArr[] = "(".$strQueData.")"; // 검색쿼리 합체

					// 기존배열 갯수도 쿼리만큼 늘려야 함
					$addArr = $this->queValArr($param);
					$resetArr = array_merge($resetArr, $addArr); // 배열 합체
				}

				$wQue = implode(" OR ", $wQueArr); // 쿼리문 합체
				$mergeArr = $resetArr; // 배열 합체
			} 
			else 
			{
				$strQueArr = $this->whereQue($param); // 검색쿼리 배열
				$wQue = implode(" AND ", $strQueArr); // 쿼리문 합체
				$mergeArr = array_merge($arr, $carArr, $yearArr, $kmArr, $priceArr, $saleAreaArr, $fuelArr, $gearTypeArr, $accident, $color); // 배열 합체
			}

			$query = "SELECT carinfo4, COUNT(*) AS cnt FROM ".$this->table." WHERE ".$wQue." GROUP BY carinfo4";
			$aData = $this->BuildQuery($query, $mergeArr, 'local_autosale_db');

			// 가지고 온 데이터를 자료 배열로 변환
			$reData = [];
			foreach ($aData as $val) {
				$reData[$val['carinfo4']] = $val['cnt'];
			}

			// 수량 치환
			foreach ($data as $k=>$val) {
				if (array_key_exists($val['carinfo4'], $reData)) {
					$data[$k]['cnt'] = $reData[$val['carinfo4']];
				} else {
					$data[$k]['cnt'] = 0;
				}
			}
		}

		return $data;
	}

	# 차종 메뉴
	public function menuKind($param=[])
	{
		$modelQueArr = empty($param['modelQue']) ? []: $param['modelQue'];
		$yearArr = empty($param['year']) ? []: $param['year']; // 연식
		$kmArr = empty($param['km']) ? []: $param['km']; // 주행거리
		$priceArr = empty($param['price']) ? []: $param['price']; // 가격
		$saleAreaArr = empty($param['saleArea']) ? []: $param['saleArea']; // 지역
		$fuelArr = empty($param['fuel']) ? []: $param['fuel']; // 연료
		$gearTypeArr = empty($param['gearType']) ? []: $param['gearType']; // 변속기
		$accident = empty($param['accident']) ? []: $param['accident']; // 사고유무
		$color = empty($param['color']) ? []: $param['color']; // 사고유무
		$option = empty($param['option']) ? []: $param['option']; // 옵션

		$arr = [1];
		$query =  "SELECT cartype, COUNT(*) AS cnt FROM ".$this->table." WHERE 1 = ? GROUP BY cartype ORDER BY cnt DESC";
		$data = $this->BuildQuery($query, $arr, 'local_autosale_db');

		if (!empty($yearArr) || !empty($kmArr) || !empty($priceArr) || !empty($saleAreaArr) || !empty($fuelArr) || !empty($gearTypeArr) || !empty($accident) || !empty($color) || !empty($option)) {
			$boolChk = true;
		} else {
			$boolChk = false;
		}

		if (!empty($modelQueArr) || $boolChk) 
		{
			$wQue = "";

			$strQueArr = $this->whereQue($param, 'N'); // 검색쿼리 배열
			$wQue = empty($strQueArr) ? "": implode(" AND ", $strQueArr); // 쿼리문 합체	

			// 차종
			$resetArr = []; // 차종 배열 다시 셋팅
			if (!empty($modelQueArr)) {
				// 연식 검색 추가
				if ($boolChk) {
					$addQue = " AND ".$wQue; // 연식
				}
				
				foreach ($modelQueArr as $value) {
					$wQueArr[] = $value.$addQue;

					// 기존배열 갯수도 쿼리만큼 늘려야 함
					$addArr = $this->queValArr($param, 'N');
					$resetArr = array_merge($resetArr, $addArr); // 배열 합체
				}

				$wQue = implode(" OR ", $wQueArr); // 쿼리문 합체 - 차종 겂색이 있으면 .. 치환
			} 
			else 
			{
				$strQueArr = $this->whereQue($param, 'N'); // 검색쿼리 배열
				$wQue = implode(" AND ", $strQueArr); // 쿼리문 합체	
				$resetArr = array_merge($yearArr, $kmArr, $priceArr, $saleAreaArr, $fuelArr, $gearTypeArr, $accident, $color); // 배열 합체
			}
			
			$mergeArr = $resetArr; // 배열 합체
			$query = "SELECT cartype, COUNT(*) AS cnt FROM ".$this->table." WHERE ".$wQue." GROUP BY cartype"; 
			//echo $query; print_r($mergeArr);
			$aData = $this->BuildQuery($query, $mergeArr, 'local_autosale_db');

			// 가지고 온 데이터를 자료 배열로 변환
			$reData = [];
			foreach ($aData as $val) {
				$reData[$val['cartype']] = $val['cnt'];
			}

			// 수량 치환
			foreach ($data as $k=>$val) {
				if (array_key_exists($val['cartype'], $reData)) {
					$data[$k]['cnt'] = $reData[$val['cartype']];
				} else {
					$data[$k]['cnt'] = 0;
				}
			}
		}

		return $data;
	}

	# 지역 메뉴
	public function menuArea()
	{
		$arr = [1];
		$query =  "SELECT carsalearea1 FROM ".$this->table." WHERE 1 = ? GROUP BY carsalearea1";
		$data = $this->BuildQuery($query, $arr, 'local_autosale_db');
		return $data;
	}

	# 연료 메뉴
	public function menuFuel()
	{
		$arr = [1];
		$query =  "SELECT caroil FROM ".$this->table." WHERE 1 = ? AND caroil != '' GROUP BY caroil";
		$data = $this->BuildQuery($query, $arr, 'local_autosale_db');
		return $data;
	}

	# 변속기 메뉴
	public function menuGearType()
	{
		$arr = [1];
		$query =  "SELECT carauto FROM ".$this->table." WHERE 1 = ? AND carauto != '' GROUP BY carauto";
		$data = $this->BuildQuery($query, $arr, 'local_autosale_db');
		return $data;
	}

	# 색상 메뉴
	public function menuColor()
	{
		$arr = [1];
		$query =  "SELECT carcolor, COUNT(*) AS cnt FROM ".$this->table." WHERE 1 = ? AND carcolor != '' GROUP BY carcolor ORDER BY cnt DESC LIMIT 12";
		$data = $this->BuildQuery($query, $arr, 'local_autosale_db');
		return $data;
	}
}
