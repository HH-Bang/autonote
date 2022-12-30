<?
// 컨트롤러에서는 namespace를 사용하지 않는다.

use lib\func_class as func; // 공용함수
use app\models\autonote_db_model as autonoteModel; // model

class autonote_db extends func
{
	private $autonoteModel;
	private $priceStartArr;
	private $priceEndArr;
	private $accident;
	private $opTitle;
	private $opArr;

	function __construct()
	{
		parent::__construct();
		$this->autonoteModel = new autonoteModel; // 오더 model 로드

		$this->priceStartArr = [
			0, 100, 200, 300, 400, 500, 600, 700, 800, 900
			, 1000, 1100, 1200, 1300, 1400, 1500
			, 2000, 2500, 3000, 3500, 4000, 4500
			, 5000, 6000, 7000, 8000, 9000
		];
		$this->priceEndArr = [
			0, 100, 200, 300, 400, 500, 600, 700, 800, 900
			, 1000, 1100, 1200, 1300, 1400, 1500
			, 2000, 2500, 3000, 3500, 4000, 4500
			, 5000, 6000, 7000, 8000, 9000
			, 10000, 10001
		];
		$this->accident = ['무사고', '사고'];
		$this->opTitle = [
			'out' => '외관'
			, 'in' => '내장'
			, 'safe' => '안전'
			, 'conv' => '편의'
		];
		$this->opArr = [
			'out' => ['HID 램프', 'LED 램프', '어댑티브 램프', '하이빔', '전동 접이 사이드미러', '열선 사이드미러', '후진 각도조절 사이드미러', '썬루프', '듀얼 썬루프', '파노라마 썬루프', '와이퍼 결빙방지 윈드실드', '자외선 차단 윈드실드', '알루미늄휠', '크롬휠','광폭타이어']
			, 'in' => ['가죽스티어링휠', '우드스티어링휠', '열선내장 스티어링 휠', '직물시트', '가죽시트', '전동시트(운전석)', '전동시트(동승석)', '전동시트(뒷좌석)', '열선시트(앞)', '열선시트(뒤)', '메모리시트(운전석)', '메모리시트(동승석)', '통풍시트(운전석)', '통풍시트(동승석)', '안마시트', 'ECM 룸미러', '하이패스내장 룸미러', '후방룸미러', '풋파킹 브레이크', '전자식파킹 브레이크']
			, 'safe' => ['운전석에어백', '동승석에어백', '사이드에어백', '커튼에어백', '무릎보호에어백', '전방감지센서', '후방감지센서', '전방카메라', '후방카메라', '차선이탈방지(LDWS)', '어라운드뷰(AVM)', '후측방경보시스템(BSD/BSW)', 'ABS 브레이크 잠김방지', 'TCS 미끄럼방지', 'VDC(ESP) 차체자세 제어', 'ECS 전자제어서스펜션', 'ESS 급제동경보시스템', 'HAS 경사로', 'TPMS 타이어공기압', '유아시트고정장치', '세이프티윈도우', '액티브헤드레스트', '전동식파워스티어링', 'AGCS 주행안정성 제어 시스템']
			, 'conv' => ['에어컨', '풀오토 에어컨', '듀얼 풀오토 에어컨', 'CD', 'CD 체인저', 'DVD', 'AUX단자', 'MP3', 'USB', 'iPod', '네비게이션', '스마트키', '버튼시동', '크루즈컨트롤', '핸즈프리', '전동식 파워 트렁크', '자동주차시스템', '레인센서와이퍼', '속도 감응식 스티어링휠', '스티어링휠 리모컨', '트립컴퓨터']
		];

		$cmd = empty($this->params['cmd']) ? '': $this->params['cmd'];
		switch( $cmd ){
			case "menuModel": $this->menuModel(); break;
			case "menuKind": $this->menuKind(); break;
			case "products": $this->products(); break;
			default: $this->Init(); break;
		}
	}

	# 금액 셋팅
	public function moneySet($price=0)
	{
		$return = '';
		switch ($price) {
			case "0": $return = '0원'; break;
			case "10000": $return = '1억원'; break;
			case "10001": $return = '1억원 이상'; break;
			default: $return = number_format($price).'만원'; break;
		}
		return $return;
	}

	# main
	public function Init()
	{
		$startTime = $this->GetTimeChk();

		// 연식
		$nowYear = date("Y", strtotime("+1 Year"));
		$y1 = '';
		for ($yy=1990; $yy<=$nowYear; $yy++) {
			$y1 .= '<option value="'.$yy.'">'.$yy.'년</option>';
		}
		$y2 = '';
		for ($yy=1990; $yy<=$nowYear; $yy++) {
			$y2 .= '<option value="'.$yy.'">'.$yy.'년</option>';
		}

		// 주행거리
		$km1 = '';
		for ($km=0; $km<=200000; $km+=10000) {
			$km1 .= '<option value="'.$km.'">'.number_format($km).'Km</option>';
		}
		$km2 = '';
		for ($km=0; $km<=200000; $km+=10000) {
			$km2 .= '<option value="'.$km.'">'.number_format($km).'Km</option>';
		}

		// 가격
		$price1 = '';
		foreach ($this->priceStartArr as $val) {
			$strPrice = $this->moneySet($val);
			$price1 .= '<option value="'.$val.'">'.$strPrice.'</option>';
		}
		$price2 = '';
		foreach ($this->priceEndArr as $val) {
			$strPrice = $this->moneySet($val);
			$price2 .= '<option value="'.$val.'">'.$strPrice.'</option>';
		}

		// 지역
		$saleareaData = $this->autonoteModel->menuArea();
		$salearea = '';
		foreach ($saleareaData as $val) {
			if (!empty($val['carsalearea1'])) {
				$salearea .= '<li data-area="'.$val['carsalearea1'].'"><b>'.$val['carsalearea1'].'</b></li>';
			}
		}
		$salearea .= '<div style="clear:both;"></div>';

		// 연료
		$fuelData = $this->autonoteModel->menuFuel();
		$fuel = '';
		foreach ($fuelData as $val) {
			if (!empty($val['caroil'])) {
				$menuStr = str_replace("/", "<br>", $val['caroil']);
				$fuel .= '<li data-fuel="'.$val['caroil'].'"><b>'.$menuStr.'</b></li>';
			}
		}
		$fuel .= '<div style="clear:both;"></div>';

		// 변속기
		$GearTypeData = $this->autonoteModel->menuGearType();
		$GearType = '';
		foreach ($GearTypeData as $val) {
			if (!empty($val['carauto'])) {
				$GearType .= '<li data-gear-type="'.$val['carauto'].'"><b>'.$val['carauto'].'</b></li>';
			}
		}
		$GearType .= '<div style="clear:both;"></div>';

		// 사고유무
		$accident = '';
		foreach ($this->accident as $val) {
			$accident .= '<li data-accident="'.$val.'"><b>'.$val.'</b></li>';
		}
		$accident .= '<div style="clear:both;"></div>';

		// 색상
		$colorData = $this->autonoteModel->menuColor();
		$color = '';
		foreach ($colorData as $val) {
			if (!empty($val['carcolor'])) {
				$color .= '<li data-color="'.$val['carcolor'].'"><b>'.$val['carcolor'].'</b></li>';
			}
		}
		$color .= '<div style="clear:both;"></div>';

		// 옵션
		$option = '';
		foreach ($this->opTitle as $val) {
			$option .= '<li data-option="'.$val.'"><b>'.$val.'</b></li>';
		}
		$option .= '<div style="clear:both;"></div>';

		// 내용 data
		$dataArr = [
			'y1' => $y1
			, 'y2' => $y2
			, 'km1' => $km1
			, 'km2' => $km2
			, 'price1' => $price1
			, 'price2' => $price2
			, 'salearea' => $salearea
			, 'fuel' => $fuel
			, 'GearType' => $GearType
			, 'accident' => $accident
			, 'color' => $color
			, 'option' => $option
		];
		$this->Load_view('/sale.html', $dataArr);
		
		$endTime = $this->GetTimeChk();
		echo '<p>'.($endTime - $startTime).'</p>'; 
	}

	# 제조사/모델 메뉴
	public function menuModel()
	{
		$searchArr = $this->searchArr(); // 검색 배열값
		$modelQue = $this->autonoteModel->modelWhereQue($searchArr['queArr']);
		$searchArr['modelQue'] = $modelQue;
		//print_r($modelQue);
		
		$menuData1 = $this->autonoteModel->menuModelDep1($searchArr);
		$menuModelHtml = '';

		// depth1 - 회사
		foreach ($menuData1 as $val1)
		{
			$setVal1 = $val1['carinfo1'];
			$dep1Boolen = (in_array($setVal1, $searchArr['modelDepth1'])) ? true: false;
			$chk = ($dep1Boolen) ? 'checked': ''; // depth1 체크값

			if (!empty($setVal1)) 
			{
				$menuModelHtml .= '<li><label><input type="checkbox" name="modelDepth1[]" value="'.$setVal1.'" class="modelDepth1" '.$chk.' onclick="depthCheck1(this);" /> '.$setVal1.'</label></li>';
				$menuModelHtml .= '<li class="cntArea">'.number_format($val1['cnt']).'</li>';

				// depth2 - 차종
				if ($dep1Boolen) 
				{
					$menuData2 = $this->autonoteModel->menuModelDep2($setVal1, $searchArr);
					foreach ($menuData2 as $val2) 
					{
						$setVal2 = $val2['carseries'];
						$setInfo1 = $setVal1.'_'.$setVal2;
						$dep2Boolen = (in_array($setVal2, $searchArr['modelDepth2'])) ? true: false;
						$chk = ($dep2Boolen) ? 'checked': ''; // depth2 체크값

						if (!empty($setVal2)) 
						{
							$menuModelHtml .= '<li>';
								$menuModelHtml .= '<span class="menu_model_inwrite1">⨽</span>';
								$menuModelHtml .= '<label><input type="checkbox" name="modelDepth2[]" value="'.$setInfo1.'" class="modelDepth2" '.$chk.' onclick="depthCheck2(this);" /> '.$setVal2.'</label>';
							$menuModelHtml .= '</li>';
							$menuModelHtml .= '<li class="cntArea">'.number_format($val2['cnt']).'</li>';

							// depth3 - 차량이름
							if ($dep2Boolen)
							{
								$menuData3 = $this->autonoteModel->menuModelDep3($setVal2, $searchArr);
								foreach ($menuData3 as $val3) 
								{
									$setVal3 = $val3['carinfo2'];
									$setInfo2 = $setVal1.'_'.$setVal2.'_'.$setVal3;
									$dep3Boolen = (in_array($setVal3, $searchArr['modelDepth3'])) ? true: false;
									$chk = ($dep3Boolen) ? 'checked': ''; // depth3 체크값

									if (!empty($setVal3)) 
									{
										$menuModelHtml .= '<li>';
											$menuModelHtml .= '<span class="menu_model_inwrite2">⨽</span>';
											$menuModelHtml .= '<label><input type="checkbox" name="modelDepth3[]" value="'.$setInfo2.'" class="modelDepth3" '.$chk.' onclick="depthCheck3(this);" /> '.$setVal3.'</label>';
										$menuModelHtml .= '</li>';
										$menuModelHtml .= '<li class="cntArea">'.number_format($val3['cnt']).'</li>';

										// depth4 - 등급, 연료
										if ($dep3Boolen)
										{
											$menuData4 = $this->autonoteModel->menuModelDep4($setVal3, $searchArr);
											foreach ($menuData4 as $val4) 
											{
												$setVal4 = $val4['carinfo3'];
												$setInfo3 = $setVal1.'_'.$setVal2.'_'.$setVal3.'_'.$setVal4;
												$dep4Boolen = (in_array($setVal4, $searchArr['modelDepth4'])) ? true: false;
												$chk = ($dep4Boolen) ? 'checked': ''; // depth3 체크값

												if (!empty($setVal4)) 
												{
													$menuModelHtml .= '<li>';
														$menuModelHtml .= '<span class="menu_model_inwrite3">⨽</span>';
														$menuModelHtml .= '<label><input type="checkbox" name="modelDepth4[]" value="'.$setInfo3.'" class="modelDepth4" '.$chk.' onclick="depthCheck4(this);" /> '.$setVal4.'</label>';
													$menuModelHtml .= '</li>';
													$menuModelHtml .= '<li class="cntArea">'.number_format($val4['cnt']).'</li>';

													// depth5 - 트림종류
													if ($dep4Boolen)
													{
														$menuData5 = $this->autonoteModel->menuModelDep5($setVal3, $setVal4, $searchArr);
														foreach ($menuData5 as $val5) 
														{
															$setVal5 = $val5['carinfo4'];
															$setInfo4 = $setVal1.'_'.$setVal2.'_'.$setVal3.'_'.$setVal4.'_'.$setVal5;
															if (!empty($val5['carinfo4'])) 
															{
																$dep5Boolen = (in_array($setVal5, $searchArr['modelDepth5'])) ? true: false;
																$chk = ($dep5Boolen) ? 'checked': ''; // depth3 체크값

																$menuModelHtml .= '<li>';
																	$menuModelHtml .= '<span class="menu_model_inwrite4">⨽</span>';
																	$menuModelHtml .= '<label><input type="checkbox" name="modelDepth5[]" value="'.$setInfo4.'" class="modelDepth5" '.$chk.' /> '.$setVal5.'</label>';
																$menuModelHtml .= '</li>';
																$menuModelHtml .= '<li class="cntArea">'.number_format($val5['cnt']).'</li>';
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		$menuModelHtml .= '<div style="clear:both;"></div>';
		$menuModelHtml .= "
			<script>
				$(function(){
					var count = $('.cntArea').length;
					for (var i=0; i<count; i++) {
						var element = $('.cntArea').eq(i);
						if (element.text() == '0') {
							element.css('color', '#ddd');
							element.prev().css('color', '#ddd');
							element.prev().find('input').attr('disabled', true); // 체크박스 비활성화
						}
					}
				});
			</script>
		";
		echo $menuModelHtml;
	}

	# 차종 메뉴
	public function menuKind()
	{
		$searchArr = $this->searchArr(); // 검색 배열값
		$modelQue = $this->autonoteModel->modelWhereQue($searchArr['queArr']);
		$searchArr['modelQue'] = $modelQue;

		$menuKindData = $this->autonoteModel->menuKind($searchArr);
		$menuKindHtml = '';
		
		foreach ($menuKindData as $val) {
			if (!empty($val['cartype'])) {
				$style = (in_array($val['cartype'], $searchArr['carTypeArr'])) ? 'class="car_type_on"': '';
				$menuKindHtml .= '<li '.$style.' onclick="carTypeAdd(\''.$val['cartype'].'\');"><b>'.$val['cartype'].'</b><br>'.number_format($val['cnt']).'</li>';
			}
		}
		$menuKindHtml .= '<div style="clear:both;"></div>';

		echo $menuKindHtml;
	}

	# 컨텐츠
	public function products()
	{
		#--------------- list ---------------#
		$record = empty($this->params['view_cnt']) ? 15: $this->params['view_cnt'];
		$no = empty($this->params['no'])? 0: $this->params['no'];
		$page_view = 10;
		$listParam = [
			'record' => $record
			, 'no' => $no
		];


		$searchArr = $this->searchArr(); // 검색 배열값
		$modelQue = $this->autonoteModel->modelWhereQue($searchArr['queArr']);
		$searchArr['modelQue'] = $modelQue;
		$searchArr['listParam'] = $listParam;

		$data = $this->autonoteModel->getList($searchArr);
		$dataArr = $data['data'];
		$total_row = $data['data_row'];
		//print_r($data);

		$html = '';
		$k = 0;
		if ($dataArr) {
			foreach ($dataArr as $val) {
				$num = $total_row - $k - $no; // 게시물 번호

				// 기타설명
				if (empty($val['carsubject'])) {
					$subjectArr = explode(" ", $val['kw']);
					$subject = $subjectArr[2].' '.$subjectArr[3].' '.$subjectArr[4];
				} else {
					$subject = $this->Cut_str($val['carsubject'], 50, "…");
				}

				// 차량이름
				$titleArr = []; 
				if (!empty($val['carinfo1'])) $titleArr[] = $val['carinfo1'];
				if (!empty($val['carseries'])) $titleArr[] = $val['carseries'];
				if (!empty($val['carinfo3'])) $titleArr[] = $val['carinfo3'];
				if (!empty($val['carinfo4'])) $titleArr[] = $val['carinfo4'];
				$title = implode(" / ", $titleArr);

				// 금액
				$price = number_format($val['carmoney']);

				// 설명
				$infoArr = []; 
				if (!empty($val['caryear2'])) $infoArr[] = $val['caryear2'];
				if (!empty($val['carkm'])) $infoArr[] = number_format($val['carkm']).'km';
				if (!empty($val['caroil'])) $infoArr[] = $val['caroil'];
				if (!empty($val['carsalearea1'])) $infoArr[] = $val['carsalearea1'];
				$info = implode(" | ", $infoArr);

				$html .= '<div class="car_box">';
					$html .= '<div class="car_pic"></div>';
					$html .= '<div class="car_info">';
						$html .= '<div class="car_company_area"><span>'.$val['carsalearea4'].'</span></div>';
						$html .= '<div class="car_info_box">';
							$html .= '<p class="car_info_1">'.$subject.'</p>';
							$html .= '<p class="car_info_2">'.$title.'</p>';
							$html .= '<p class="car_info_3">'.$info.'</p>';
						$html .= '</div>';
						$html .= '<p class="car_info_4"><span>'.$price.'</span> 만원</p>';
					$html .= '</div>';
				$html .= '</div>';

				$k++;
			}
		}

		#--------------- paging 처리 기본값 ---------------#
		//$pgArr = ['record'=>$record, 'page_view'=>$page_view, 'no'=>$no, 'total_row'=>$total_row];
		//$html_paging = $this->PageMake($pgArr, $this->params);

		echo $html;
	}

	# 검색 옵션
	public function searchArr()
	{
		$mDepth1 =  empty($this->params['modelDepth1']) ? []: $this->params['modelDepth1'];
		$mDepth2 =  empty($this->params['modelDepth2']) ? []: $this->params['modelDepth2'];
		$mDepth3 =  empty($this->params['modelDepth3']) ? []: $this->params['modelDepth3'];
		$mDepth4 =  empty($this->params['modelDepth4']) ? []: $this->params['modelDepth4'];
		$mDepth5 =  empty($this->params['modelDepth5']) ? []: $this->params['modelDepth5'];


		// 상위 값이 없으면 하위 배열은 초기화
		if (empty($mDepth1)) {
			$mDepth2 = [];
			$mDepth3 = [];
			$mDepth4 = [];
			$mDepth5 = [];
		}
		if (empty($mDepth2)) {
			$mDepth3 = [];
			$mDepth4 = [];
			$mDepth5 = [];
		}
		if (empty($mDepth3)) {
			$mDepth4 = [];
			$mDepth5 = [];
		}
		if (empty($mDepth4)) {
			$mDepth5 = [];
		}

		
		$arr = []; // 리턴 배열 초기화


		// 쿼리용 배열
		if (!empty($mDepth1)) {
			foreach ($mDepth1 as $val) {
				$arr['queArr'][$val] = '';
			}
		}
		if (!empty($mDepth2)) {
			foreach ($mDepth2 as $val) {
				$dArr = explode("_", $val);
				$arr['queArr'][$dArr[0]][$dArr[1]] = '';
			}
		}
		if (!empty($mDepth3)) {
			foreach ($mDepth3 as $val) {
				$dArr = explode("_", $val);
				$arr['queArr'][$dArr[0]][$dArr[1]][$dArr[2]] = '';
			}
		}
		if (!empty($mDepth4)) {
			foreach ($mDepth4 as $val) {
				$dArr = explode("_", $val);
				$arr['queArr'][$dArr[0]][$dArr[1]][$dArr[2]][$dArr[3]] = '';
			}
		}
		if (!empty($mDepth5)) {
			foreach ($mDepth5 as $val) {
				$dArr = explode("_", $val);
				$arr['queArr'][$dArr[0]][$dArr[1]][$dArr[2]][$dArr[3]][$dArr[4]] = '';
			}
		}


		// 제조사/모델 배열
		if (!empty($mDepth1)) {
			$arr['modelDepth1'] = $mDepth1;

			// 모델
			if (!empty($mDepth2)) {
				$arr['modelDepth2'] = $this->firstArr($mDepth2);

				// 차량 이름
				if (!empty($mDepth3)) {
					$arr['modelDepth3'] = $this->firstArr($mDepth3);

					// 등급 or 연료
					if (!empty($mDepth4)) {
						$arr['modelDepth4'] = $this->firstArr($mDepth4);

						// 트림
						if (!empty($mDepth5)) {
							$arr['modelDepth5'] = $this->firstArr($mDepth5);
						}
					}
				}
			}
		}


		// 차종 검색값
		if (!empty($this->params['car_type'])) $arr['carTypeArr'] = explode(",", $this->params['car_type']);

		// 연식
		$year0 = empty($this->params['menuY'][0]) ? '': $this->params['menuY'][0];
		$year1 = empty($this->params['menuY'][1]) ? '': $this->params['menuY'][1];
		if (!empty($year0) && !empty($year1)) $arr['year'] = [$year0, $year1];

		// 주행거리
		$km0 = empty($this->params['menuKm'][0]) ? 0: $this->params['menuKm'][0];
		$km1 = empty($this->params['menuKm'][1]) ? 0: $this->params['menuKm'][1];
		if (!empty($km1)) $arr['km'] = [$km0, $km1];

		// 금액
		$price0 = empty($this->params['menuPrice'][0]) ? 0: $this->params['menuPrice'][0];
		$price1 = empty($this->params['menuPrice'][1]) ? 0: $this->params['menuPrice'][1];
		if (!empty($price1)) $arr['price'] = [$price0, $price1];

		// 지역
		if (!empty($this->params['sale_area'])) $arr['saleArea'] = explode(",", $this->params['sale_area']);

		// 연료
		if (!empty($this->params['fuel'])) $arr['fuel'] = explode(",", $this->params['fuel']);

		// 변속기
		if (!empty($this->params['gear_type'])) $arr['gearType'] = explode(",", $this->params['gear_type']);

		// 사고유무
		if (!empty($this->params['accident'])) $arr['accident'] = explode(",", $this->params['accident']);

		// 색상
		if (!empty($this->params['color'])) $arr['color'] = explode(",", $this->params['color']);

		// 옵션
		if (!empty($this->params['option'])) $arr['option'] = explode(",", $this->params['option']);


		return $arr;
	}

	# 배열 첫번째 값 저장
	public function firstArr($arr=[])
	{
		if (empty($arr)) return [];

		$retArr = [];
		foreach ($arr as $val) {
			$eArr = explode("_", $val);
			$n = count($eArr) - 1;
			$retArr[] = $eArr[$n];
		}

		return $retArr;
	}
}
