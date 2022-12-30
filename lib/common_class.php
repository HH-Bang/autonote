<?
# 프론트 및 관리자에서 공용으로 사용될 클래스
namespace lib;
use lib\func_class as func; // 공용함수

class common_class extends func
{
	function __construct()
	{
		//parent::__construct();
		//$this->db = DB_SEl_NAME; // blockking		
	}

	/*
	# 1depth/ 2depth 메뉴
	public function menuArr()
	{
		// 메뉴
		$mArr = array(
			'm1' => array(
				'1' => 'CEO 인사말'
				, '2' => '연혁'
				, '3' => '조직도'
				, '4' => '약도'
			)
			, 'm2' => array(
				'13' => '인터로킹(보도블럭)'
				, '14' => '경계석'
				, '15' => '화강석'
				, '21' => '잔디블록'
				, '19' => '보강토옹벽블록'
				, '20' => '식생축조블록'
				, '9' => '가로등기초'
				, '5' => '사각수로관'
				, '6' => '원심력사각수로관'
				, '8' => '맨홀'
				, '10' => '주철뚜껑'
				, '4' => '측구수로관'
				, '11' => '스틸그레이팅'
				, '12' => '특수그레이팅'
				, '2' => '플룸관/수로관'
				, '3' => '플룸관/수로관뚜껑'
				, '16' => 'PE제품'
				, '7' => '흄관(VR관)'
				, '22' => '레진관'
				, '1' => 'PC암거'
				, '17' => '파형강관'
				, '23' => 'PHC파일'	
				, '18' => 'PC방호벽(중앙분리)'
			)
			, 'm4' => array(
				'build' => '특허/납품실적'
				, 'data_room' => '자료실'
				, 'supply' => '공급승인원'	
			)
		);

		return $mArr;
	}

	# 3depth 메뉴
	public function menuArrSub()
	{
		// 메뉴
		$mArr = array(
			//'인터로킹(보도블럭)' => array(
			//	'1' => '인터로킹(U형/I2형)'
			//	, '2' => '장애인사각블럭'
			//	, '3' => '점토블럭'
			//	, '4' => '투수블럭'		
			//)
			'인터로킹(보도블럭)' => array(
				'1' => '보도블록(규사)'
				, '2' => '인터로킹U형블럭'
				, '3' => '인터로킹i2형블럭'
				, '4' => '인조화강블록(투수)'
				, '5' => '인조화강블록(불투수)'
				, '6' => '장애인사각블럭(점자블럭)'
				, '7' => '규사'
			)
			, '경계석' => array(
				'1' => '콘크리트경계석'
				, '2' => '인조화강경계석'		
			)
			//, '화강석' => array(
			//	'1' => '화강경계석'
			//	, '2' => '볼라드'
			//	, '3' => '사구석'		
			//)
			, '화강석' => array(
				'1' => '화강경계석'
				, '2' => '험프석'
				, '3' => '볼라드'
				, '4' => '계단석'
				, '5' => '판석'
				, '6' => '사구석/소포석'
				, '7' => '화산석'
			)
			, '잔디블록' => array()
			, '보강토옹벽블록' => array()
			, '식생축조블록' => array()
			, '가로등기초' => array()
			, '사각수로관' => array(
				'1' => '원형사각수로관(RDG형)'
				, '2' => '돌무늬원형사각수로관(NS02,03)'
				, '3' => '사각수로관(AN,BN,CN형)'		
			)
			, '원심력사각수로관' => array(
				'1' => 'R,RG,RDG형'
				, '2' => '유개/무개형'		
			)
			, '맨홀' => array(
				'1' => '원형맨홀'
				, '2' => '사각맨홀'
				, '3' => '집수정(빗물받이)'
				, '4' => '전기,통신맨홀'
				, '5' => 'PC(인버터)맨홀'		
			)
			, '주철뚜껑' => array(
				'1' => '원형주철'
				, '2' => '사각주철'
				, '3' => '트렌치(닥타일)'
				, '4' => '조화(디자인)맨홀'
				, '5' => '각종주철'		
			)
			, '측구수로관' => array(
				'1' => '앵글미부착형(A형)'
				, '2' => '앵글부착형(B형)'	
			)
			, '스틸그레이팅' => array(
				'1' => '그레이팅 단가표'
				, '2' => '그레이팅 소개'
				, '3' => '측구수로관용'
				, '4' => '집수정(맨홀)용'
				, '5' => '플룸관(U-TYPE)용'
				, '6' => '원형그레이팅'
				, '7' => '스페셜 TYPE'		
			)
			, '특수그레이팅' => array(
				'1' => '계단용그레이팅'
				, '2' => '중하중그레이팅'
				, '3' => '디자인그레이팅'
				, '4' => '스텐레스(SUS)그레이팅/SUS타공'		
			)
			, '플룸관/수로관' => array(
				'1' => 'U형플룸관'
				, '2' => '벤치플룸관(2종)'
				, '3' => '벤치플룸관(3종)'
			)
			, '플룸관/수로관뚜껑' => array()
			, 'PE제품' => array(
				'1' => 'PE배수로'
				, '2' => 'PE빗물받이/오수받이/홈통받이'
				, '3' => 'PE이중벽관(1,2,3종)'
				, '4' => 'THP관/THP유공관'
				, '5' => 'PVC관(VG1/VG2)'
				, '6' => '고강성PVC이중벽관'
				, '7' => 'HDPE이중벽관/유공관'		
			)
			, '흄관(VR관)' => array(
				'1' => '흄관(B형)'
				, '2' => '유공흄관/접속흄관'
				, '3' => 'VR관'		
			)
			, '레진관' => array()
			, 'PC암거' => array(
				'1' => '1련암거'
				, '2' => '2련암거'
				, '3' => '상하분리형'
				, '4' => '덮개형(개거수로)'
			)
			, '파형강관' => array()
			, 'PHC파일' => array()
			, 'PC방호벽(중앙분리)' => array()
		);

		return $mArr;
	}
	*/
}
?>
