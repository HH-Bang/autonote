<?
namespace lib;

class aligo_kakao_api
{
	function __construct()
	{
		//parent::__construct();

		# 타임존 설정
		date_default_timezone_set('Asia/Seoul');

		# 알리고 알림톡 api
		$this->aligo_kakao_id = 'modoo24';
		$this->aligo_key = 'jqcikv92tvfqk0obiof3skpuk6w1chp2';
		$this->aligo_send_key = 'b724997843be5e7009ec283489148df157c813c7';
	}

	# cUrl
	public function Curl( $_apiURL='', $_port=80, $_variables=array() )
	{
		$oCurl = curl_init();
		curl_setopt($oCurl, CURLOPT_PORT, $_port);
		curl_setopt($oCurl, CURLOPT_URL, $_apiURL);
		curl_setopt($oCurl, CURLOPT_POST, 1);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($oCurl, CURLOPT_POSTFIELDS, http_build_query($_variables));
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);

		$ret = curl_exec($oCurl);
		$error_msg = curl_error($oCurl);
		curl_close($oCurl);

		return $ret;
	}

	# 토큰 생성
	private function Token()
	{
		$_apiURL = 'https://kakaoapi.aligo.in/akv10/token/create/30/s/';
		$_hostInfo = parse_url($_apiURL);
		$_port = (strtolower($_hostInfo['scheme']) == 'https') ? 443 : 80;
		$_variables = array(
			'apikey' => $this->aligo_key
			, 'userid' => $this->aligo_kakao_id
		);

		$data = $this->Curl( $_apiURL, $_port, $_variables );
		return $data;
	}

	# 알림톡 발송
	public function Send_alimtok( $arr=array() )
	{
		// 토큰가져오기
		$retArr = json_decode( $this->Token(), true );

		$_apiURL = 'https://kakaoapi.aligo.in/akv10/alimtalk/send/';
		$_hostInfo = parse_url($_apiURL);
		$_port = (strtolower($_hostInfo['scheme']) == 'https') ? 443 : 80;
		$_variables = array(
			'apikey' => $this->aligo_key
			, 'userid' => $this->aligo_kakao_id
			, 'token' => $retArr['token']
			, 'senderkey' => $this->aligo_send_key
			, 'tpl_code' => $arr['tp_code']
			, 'sender' => $arr['sender']
			, 'senddate' => date("YmdHis", strtotime("+10 minutes"))
		);

		// 메세지
		if( count($arr['data'])>0 ){
			$k = 1;
			foreach( $arr['data'] as $val ){
				$_variables['receiver_'.$k] = $val['hp'];
				$_variables['recvname_'.$k] = $val['name'];
				$_variables['subject_'.$k] = $val['subject'];
				$_variables['message_'.$k] = $val['msg'];
				if( !empty($val['button']) ){
					$_variables['button_'.$k] = $val['button'];
				}
				$k++;
			}
		}

		$data = $this->Curl( $_apiURL, $_port, $_variables );
		return $data;

		/*
		-----------------------------------------------------------------
		치환자 변수에 대한 처리
		-----------------------------------------------------------------

		등록된 템플릿이 "#{이름}님 안녕하세요?" 일경우
		실제 전송할 메세지 (message_x) 에 들어갈 메세지는
		"홍길동님 안녕하세요?" 입니다.

		카카오톡에서는 전문과 템플릿을 비교하여 치환자이외의 부분이 일치할 경우
		정상적인 메세지로 판단하여 발송처리 하는 관계로
		반드시 개행문자도 템플릿과 동일하게 작성하셔야 합니다.

		예제 : message_1 = "홍길동님 안녕하세요?"

		-----------------------------------------------------------------
		버튼타입이 WL일 경우 (웹링크)
		-----------------------------------------------------------------
		링크정보는 다음과 같으며 버튼도 치환변수를 사용할 수 있습니다.
		{"button":[{"name":"버튼명","linkType":"WL","linkP":"https://www.링크주소.com/?example=12345", "linkM": "https://www.링크주소.com/?example=12345"}]}

		-----------------------------------------------------------------
		버튼타입이 AL 일 경우 (앱링크)
		-----------------------------------------------------------------
		{"button":[{"name":"버튼명","linkType":"AL","linkI":"https://www.링크주소.com/?example=12345", "linkA": "https://www.링크주소.com/?example=12345"}]}

		-----------------------------------------------------------------
		버튼타입이 DS 일 경우 (배송조회)
		-----------------------------------------------------------------
		{"button":[{"name":"버튼명","linkType":"DS"}]}

		-----------------------------------------------------------------
		버튼타입이 BK 일 경우 (봇키워드)
		-----------------------------------------------------------------
		{"button":[{"name":"버튼명","linkType":"BK"}]}

		-----------------------------------------------------------------
		버튼타입이 MD 일 경우 (메세지 전달)
		-----------------------------------------------------------------
		{"button":[{"name":"버튼명","linkType":"MD"}]}

		-----------------------------------------------------------------
		버튼이 여러개 인경우 (WL + DS)
		-----------------------------------------------------------------
		{"button":[{"name":"버튼명","linkType":"WL","linkP":"https://www.링크주소.com/?example=12345", "linkM": "https://www.링크주소.com/?example=12345"}, {"name":"버튼명","linkType":"DS"}]}
		*/
	}

	# 알림톡 발송 내역
	public function Send_list( $page=0, $limit=0, $sDate='', $eDate='' )
	{
		if( empty($page) || empty($limit) || empty($sDate) || empty($eDate) ){
			return;
		}

		// 토큰가져오기
		$retArr = json_decode( $this->Token(), true );		

		$_apiURL = 'https://kakaoapi.aligo.in/akv10/history/list/';
		$_hostInfo = parse_url($_apiURL);
		$_port = (strtolower($_hostInfo['scheme']) == 'https') ? 443 : 80;
		$_variables = array(
			'apikey' => $this->aligo_key
			, 'userid' => $this->aligo_kakao_id
			, 'token' => $retArr['token']			
			, 'page' => $page
			, 'limit' => $limit
			, 'startdate' => $sDate
			, 'enddate' => $eDate
		);

		$data = $this->Curl( $_apiURL, $_port, $_variables );
		return $data;
	}

	# 알림톡 상세내용
	public function View( $page=0, $limit=0, $mid='' )
	{
		if( empty($page) || empty($limit) || empty($mid) ){
			return;
		}

		// 토큰가져오기
		$retArr = json_decode( $this->Token(), true );		

		$_apiURL = 'https://kakaoapi.aligo.in/akv10/history/detail/';
		$_hostInfo = parse_url($_apiURL);
		$_port = (strtolower($_hostInfo['scheme']) == 'https') ? 443 : 80;
		$_variables = array(
			'apikey' => $this->aligo_key
			, 'userid' => $this->aligo_kakao_id
			, 'token' => $retArr['token']
			, 'page' => $page
			, 'limit' => $limit
			, 'mid' => $mid
		);

		$data = $this->Curl( $_apiURL, $_port, $_variables );
		return $data;
	}

	# 템플릿 리스트
	public function Template_list( $tpl_code='' )
	{
		// 토큰가져오기
		$retArr = json_decode( $this->Token(), true );		

		$_apiURL = 'https://kakaoapi.aligo.in/akv10/template/list/';
		$_hostInfo = parse_url($_apiURL);
		$_port = (strtolower($_hostInfo['scheme']) == 'https') ? 443 : 80;
		$_variables = array(
			'apikey' => $this->aligo_key
			, 'userid' => $this->aligo_kakao_id
			, 'token' => $retArr['token']
			, 'senderkey' => $this->aligo_send_key
		);
		if( !empty($tpl_code) ){
			$_variables['tpl_code'] = $tpl_code;
		}

		$data = $this->Curl( $_apiURL, $_port, $_variables );
		return $data;
	}
}
/*
$kakao_api = new Aligo_kakao_api;



// 토큰
//$retArr = json_decode( $kakao_api->Token(), true );
//print_r($retArr);



// 발송
$data = array(
	'tp_code' => 'TC_3875' // 템플릿 코드
	, 'sender' => '1600-7728' // 발산자번호
);
$data['data'][0] = array(
	'hp' => '01046137425' // 받는사람 번호
	, 'name' => '방한혁' // 이름
	, 'subject' => '알림톡 테스트2 입니다.' // 제목
	, 'msg' => '안녕하세요 방한혁님 모두플랫폼에서 보낸 테스트 입니다. 잘 갈까요? ㅎㅎ' // 내용
	//, 'button' => '{"button":[{"name":"버튼명","linkType":"WL","linkP":"https://www.링크주소.com/?example=12345", "linkM": "https://www.링크주소.com/?example=12345"}]}' // 템플릿에 버튼이 없는경우 제거
);
$send_alimtok = $kakao_api->Send_alimtok( $data );
$retArr = json_decode($send_alimtok, true);
print_r($retArr);



// 리스트
$page = 1;
$limit = 10;
$sDate = '20201014';
$eDate = '20201014';
$list_alimtok = $kakao_api->Send_list( $page, $limit, $sDate, $eDate );
$retArr = json_decode($list_alimtok, true);
print_r($retArr);



// 상세내용
$page = 1;
$limit = 10;
$mid = '146922466';
$view_alimtok = $kakao_api->View( $page, $limit, $mid );
$retArr = json_decode($view_alimtok, true);
print_r($retArr);
*/
?>