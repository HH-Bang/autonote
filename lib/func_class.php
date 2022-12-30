<?php

namespace lib;

use lib\dbconn as dbObject; // 네임스페이스\파일명=class명

class func_class
{
	public $db;
	public $params;
	public $dbConn;

	function __construct()
	{
		$this->db = DB_SEl_NAME; // adoo
		$this->params = $this->Params_trim( $_REQUEST );
		$this->dbObj = new dbObject;
	}

	/*
	public function BindQuery($q='', $wArr=[], $db_name='')
	{
		if(empty($q)) return array();

		// 바인드 쿼리 실행 시 타입설정
		$bindTypeArr = [
			'integer' => 'i' // 숫사형
			, 'string' => 's' // 문자형
			, 'double' => 'd' // 소숫점
			, 'blob' => 'b' // 이미지?
		];

		$dbName = (!empty($db_name))? $db_name: $this->db;
		$db = $this->dbObj->Db_connect($dbName);
		
		// 쿼리문 준비
		$stmt = mysqli_prepare($db, $q); 
		if($stmt === false){
			echo('Statement 생성 실패 : ' . mysqli_error($db));
			exit;
		}

		// ? 몇개인지
		$wCnt = substr_count($q, "?"); 
		if($wCnt != count($wArr)){
			echo('파라미터와 ?의 갯수가 다릅니다.');
			exit;
		}

		// 타입설정
		$typeArr = array();
		for($i=0; $i<$wCnt; $i++){
			$t = gettype($wArr[$i]);
			$typeArr[] = $bindTypeArr[$t];
		}
		$setType = (($typeArr))? implode("", $typeArr): '';

		// 치환 될 내용 배열저장
		$paramArr = array();
		$paramArr[] = $stmt; // obj
		if($setType) $paramArr[] = $setType; // type
		for($i=0; $i<$wCnt; $i++){
			$paramArr[] = $wArr[$i]; // ?에 치환될 내용
		}

		// mysqli_stmt_bind_param(); // 쿼리바인딩 - 준비된 쿼리문과 배열을 치환한다.
		//$no="1"; $name="admin";
		//$bind = mysqli_stmt_bind_param($stmt, "ss", $no, $name);
		$bind = call_user_func_array("mysqli_stmt_bind_param", $this->refValues($paramArr));
		if($bind === false){
			echo('파라미터 바인드 실패 : '.mysqli_error($db));
			exit;
		}

		// 쿼리 실행
		$exec = mysqli_stmt_execute($stmt); 
		if($exec === false){
			echo('쿼리 실행 실패 : '.mysqli_error($db));
			exit;
		}


		//---------- 쿼리 타입별 return s ----------//
		$queType = '';
		$chkArr = array('update', 'insert', 'delete');
		foreach( $chkArr as $val ){
			if( strpos(strtolower($q), $val) !== false ){
				$queType = $val;
				break;
			}
		}

		switch($queType)
		{
			case "insert":
				$newNo = mysqli_insert_id($db);
				mysqli_stmt_close($stmt); // 준비된 문장 닫기

				return $newNo;
				break;

			case "update": case "delete":
				mysqli_stmt_close($stmt); // 준비된 문장 닫기

				return true;
				break;

			default:
				$result = mysqli_stmt_get_result($stmt);
				mysqli_stmt_close($stmt); // 준비된 문장 닫기

				$array = array();
				if( $result ){
					$k = 0;
					// key & value 가져오기
					while( $row = mysqli_fetch_assoc($result) ){ 
						foreach($row as $key=>$value){
							$array[$k][$key] = stripslashes(htmlspecialchars_decode($value));
						}
						$k++;
					}
				}
				return $array;
				break;
		}
	}

	#------------------------------------#
	# BindSelect 1개만 가져올때.
	#------------------------------------#
	public function BindSelectOne($q='', $wArr=[], $db_name='')
	{
		$chk = true;
		$pos = strpos(strtolower($q), 'select');
		if( $pos !== false ) $chk = false;

		// select 아니면 리턴
		if( $chk ) return false;

		$result = $this->BindQuery($q, $wArr, $db_name);
		$return = $result[0]; // ?? array();
		return $return;
	}

	#------------------------------------#
	# 쿼리바인딩 -> ::값이 있으면 ..like 같은 문장 처리
	#------------------------------------#
	public function BindQue($key='')
	{
		if(strpos($key, '::') !== false)
		{
			list($strCmd, $colum) = explode('::', $key);

			$strCmd = strtoupper($strCmd); // 대문자
			$setStr = $colum." ".$strCmd." ?"; // colum LIKE ?
		}
		else $setStr = $key."=?";

		return $setStr;
	}

	#------------------------------------#
	# 바인딩 배열, 쿼리문 배열을 만든다.
	#------------------------------------#
	public function SetBind( $arr=[] )
	{
		if(!$arr) return [];

		$bindArr = [];
		$queArr = [];
		foreach( $arr as $k=>$val ){
			$bindArr[] = $val; // 바인딩 될 배열 생성
			$queArr[] = $this->BindQue($k); // 쿼리
		}
		
		return [
			'bindArr' => $bindArr
			, 'queArr' => $queArr
		];
	}

	#------------------------------------#
	# 바인딩 update 할 때 배열 셋팅
	#------------------------------------#
	public function BindUpArr($uArr=[], $wArr=[])
	{
		$bindArr = []; // 바인딩 값 배열 생성

		if(empty($uArr) && empty($wArr)) return $bindArr;

		$uBind = $this->SetBind( $uArr );
		foreach( $uBind['bindArr'] as $val ){
			$bindArr[] = $val;
		}

		$wBind = $this->SetBind( $wArr );
		foreach( $wBind['bindArr'] as $val ){
			$bindArr[] = $val;
		}

		return $bindArr;
	}

	#------------------------------------#
	# 바인딩 update 할 때 쿼리문 셋팅
	#------------------------------------#
	public function BindUpQue($uArr=[], $wArr=[])
	{
		if(empty($uArr) && empty($wArr)) return [];

		// update setting
		$upValArr = [];
		foreach( $uArr as $k=>$val ){
			$upValArr[] = $this->BindQue($k); // 쿼리바인딩
		}
		$upValQue = implode(", ", $upValArr);

		// where setting
		$wQueArr = [];
		foreach( $wArr as $k=>$val ){
			$wQueArr[] = $this->BindQue($k); // 쿼리바인딩
		}
		$wQue = implode(" AND ", $wQueArr);

		return [
			'upValQue' => $upValQue
			, 'wQue' => $wQue
		];
	}
	*/

	#------------------------------------#
	# BuildQuery(쿼리문, 바인딩배열, 연결db)
	# mysqli_stmt_execute() - 준비된 명령문을 실행합니다.
	# mysqli_stmt_fetch () -준비된 명령문의 결과를 바인딩 된 변수로 가져 오기
	# mysqli_stmt_bind_param () -변수를 준비된 명령문에 매개 변수로 바인드
	# mysqli_stmt_bind_result () -변수를 결과 저장을 위해 준비된 명령문에 바인드
	# mysqli_stmt_get_result() - 준비된 명령문에서 결과 집합을 mysqli_result 객체로 가져옵니다.
	# mysqli_stmt_close () -준비된 문장을 닫는다
	#------------------------------------#
	public function BuildQuery($q='', $wArr=[], $db_name='')
	{
		if (empty($q)) return array();
		
		// 바인드 쿼리 실행 시 타입설정
		$bindTypeArr = [
			'integer' => 'i' // 숫사형
			, 'string' => 's' // 문자형
			, 'double' => 'd' // 소숫점
			, 'blob' => 'b' // 이미지?
		];

		$dbName = (!empty($db_name))? $db_name: $this->db;
		$db = $this->dbObj->Db_connect($dbName);
		
		// 쿼리문 준비
		$stmt = mysqli_prepare($db, $q); 
		if ($stmt === false) {
			echo('Statement 생성 실패 : ' . mysqli_error($db));
			exit;
		}

		// ? 몇개인지
		$wCnt = substr_count($q, "?"); 
		if ($wCnt != count($wArr)) {
			echo('파라미터와 ?의 갯수가 다릅니다.');
			exit;
		}

		// 타입설정
		$typeArr = [];
		for ($i=0; $i<$wCnt; $i++) {
			$t = gettype($wArr[$i]);
			$typeArr[] = $bindTypeArr[$t];
		}
		$setType = empty($typeArr) ? '': implode("", $typeArr);

		// 치환 될 내용 배열저장
		$paramArr = [];
		$paramArr[] = $stmt; // obj
		if ($setType) $paramArr[] = $setType; // type
		for ($i=0; $i<$wCnt; $i++) {
			$paramArr[] = $wArr[$i]; // ?에 치환될 내용
		}
		//print_r($paramArr);echo $q;

		// mysqli_stmt_bind_param(); // 쿼리바인딩 - 준비된 쿼리문과 배열을 치환한다.
		//$no="1"; $name="admin";
		//$bind = mysqli_stmt_bind_param($stmt, "ss", $no, $name);
		$bind = call_user_func_array("mysqli_stmt_bind_param", $this->refValues($paramArr));
		if ($bind === false) {
			echo('파라미터 바인드 실패 : '.mysqli_error($db));
			exit;
		}

		// 쿼리 실행
		$exec = mysqli_stmt_execute($stmt); 
		if ($exec === false) {
			echo('쿼리 실행 실패 : '.mysqli_error($db));
			exit;
		}

		//---------- 쿼리 타입별 return s ----------//
		$queType = '';
		$chkArr = ['update', 'insert', 'delete'];
		foreach ($chkArr as $val) {			
			if (strpos(strtolower($q), $val) === 0) { // update, insert, delete 문자열이 0번째면 쿼리타입 결정
				$queType = $val;
				break;
			}
		}

		switch ($queType)
		{
			case "insert":
				$newNo = mysqli_insert_id($db);
				mysqli_stmt_close($stmt); // 준비된 문장 닫기

				return $newNo;
				break;

			case "update": case "delete":
				mysqli_stmt_close($stmt); // 준비된 문장 닫기

				return true;
				break;

			default:
				$result = mysqli_stmt_get_result($stmt);
				mysqli_stmt_close($stmt); // 준비된 문장 닫기

				$array = array();
				if( $result ){
					$k = 0;
					// key & value 가져오기
					while( $row = mysqli_fetch_assoc($result) ){ 
						foreach($row as $key=>$value){
							$array[$k][$key] = stripslashes(htmlspecialchars_decode($value));
						}
						$k++;
					}
				}
				return $array;
				break;
		}
	}

	#------------------------------------#
	# BuildQuery 1개만 가져올때.
	#------------------------------------#
	public function BuildQueryOne($q='', $wArr=[], $db_name='')
	{
		$chk = true;
		$pos = strpos(strtolower($q), 'select');
		if( $pos !== false ) $chk = false;

		// select 아니면 리턴
		if( $chk ) return false;

		$result = $this->BuildQuery($q, $wArr, $db_name);
		$return = $result[0]; // ?? array();
		return $return;
	}

	#------------------------------------#
	# mysqli_stmt_bind_param 함수를 사용하기 위해서
	#------------------------------------#
	private function refValues($arr=[])
	{
		if(empty($arr)) return array();

		// Reference is required for PHP 5.3+
		if ( strnatcmp(phpversion(),'5.3') >= 0 ){
			$refs = array();
			foreach($arr as $key => $value){
				$refs[$key] = &$arr[$key]; // 원래 배열값을 참조 해야함
			}
			return $refs;
		}
		return $arr;
	}










	#------------------------------------#
	# query
	#------------------------------------#
	public function Query($q='', $db_name)
	{
		$db = $this->dbObj->Db_connect($db_name);
		$result = mysqli_query($db, $q);

		return $result;
	}

	#------------------------------------#
	# db select
	#------------------------------------#
	public function Select($q='', $db_name)
	{
		$result = $this->Query($q, $db_name);

		$array = array();
		if($result)
		{
			$k = 0;
			while($arr = mysqli_fetch_assoc($result))
			{
				foreach($arr as $key=>$value){
					//$array[$k][$key] = htmlspecialchars_decode($value);
					//$array[$k][$key] = stripslashes($value);
					$array[$k][$key] = stripslashes(htmlspecialchars_decode($value));
				}
				$k++;
			}
		}

		return $array;
	}

	#------------------------------------#
	# db select 1개만 가져올때.
	#------------------------------------#
	public function SelectOne($data='', $db_name)
	{
		$result = $this->Select($data, $db_name);
		return $result[0];
	}

	#------------------------------------#
	# db update
	#------------------------------------#
	public function Update($tab='', $params=[], $where='', $db_name)
	{
		$db = $this->dbObj->Db_connect($db_name);

		foreach($params as $k=>$v){
			$pArr[] = $k."='".addslashes($v)."'";
			//$pArr[] = $k."='".htmlspecialchars($v)."'";
			//$pArr[] = $k."='".$v."'";
		}
		$param = implode(",",$pArr);
		$result = mysqli_query($db, "UPDATE ".$tab." SET ".$param." WHERE 1 AND ".$where);

		if($result) return true;
		else return false;
	}

	#------------------------------------#
	# db insert
	#------------------------------------#
	public function Insert($tab='', $params=[], $db_name)
	{
		$db = $this->dbObj->Db_connect($db_name);

		foreach($params as $k=>$v){
			$pArr[] = $k."='".addslashes($v)."'";
			//$pArr[] = $k."='".htmlspecialchars($v)."'";
			//$pArr[] = $k."='".$v."'";
		}
		$param = implode(",", $pArr);
		$result = mysqli_query($db, "INSERT ".$tab." SET ".$param);

		if($result) return true;
		else return false;
	}

	#------------------------------------#
	# db delete
	#------------------------------------#
	public function Del($tab='', $where='', $db_name)
	{
		$db = $this->dbObj->Db_connect($db_name);
		$result = mysqli_query($db, "DELETE FROM ".$tab." WHERE ".$where);

		if($result) return true;
		else return false;
	}










	#------------------------------------#
	# paging 처리 - $lastPageView 0 : 보임, 1 : 않보임
	#------------------------------------#
	public function PageMake($pgArr=[], $paramsArr=[], $lastPageView=0)
	{
		// 아래쪽에서 다시 no 생성되니까 제거하자
		unset($paramsArr['no']);

		$url = str_replace(".php", "", $_SERVER['PHP_SELF']); // 확장자 삭제
		$url = str_replace("/index", "", $url); // /index 삭제

		// 배열로 들어온 파라미터 처리
		if($paramsArr)
		{
			foreach($paramsArr as $k=>$val)
			{
				if($val) $aArr[] = $k.'='.$val;
			}

			if($aArr){
				$params = $url.'?'.implode("&", $aArr);
			}else{
				$params = $url.'?';
			}
		}
		else $params = $url.'?';

		$total_page = ($pgArr['total_row']==0)? 0: floor(($pgArr['total_row']-1) / $pgArr['record']); // 전체 페이지 구하기
		$pm_now_page = ($pgArr['no']==0)? 0: floor($pgArr['no']/$pgArr['record']); // 현재 페이지 구하기
		$start_page = ($pgArr['page_view']==0)? 0: (int)($pm_now_page/$pgArr['page_view'])*$pgArr['page_view']; // 페이지처리 앞부분

		// 페이지 처리 뒷부분
		$end_page = $start_page+$pgArr['page_view']-1;
		if($total_page<=$end_page) $end_page = $total_page;

		// 이전 버튼
		if($pgArr['page_view']<=$start_page) {
			$prev = ($start_page-1)*$pgArr['record'];
			$pageSource[] = '<a href="'.$params.'&no='.$prev.'" class="bbsPageArr">◀</a>&nbsp;';
		}

		// 페이지 버튼
		for($i=$start_page; $i<=$end_page; $i++)
		{
			$page = $i*$pgArr['record'];
			$page_i = $i+1;
			$sel_num_class = ($pgArr['no']!=$page)? 'bbsPage': 'bbsPageSel';

			$pageSource[] = '<a href="'.$params.'&no='.$page.'" class="'.$sel_num_class.'">'.$page_i.'</a> ';
		}

		// 다음 페이지 버튼
		if($end_page < $total_page)
		{
			$next = ($end_page+1)*$pgArr['record'];
			$pageSource[] = '&nbsp;<a href="'.$params.'&no='.$next.'" class="bbsPageArr">▶</a>';
		}

		// 마지막 페이지 버튼
		if($lastPageView == 0){
			$total_page_view =$total_page+1;
			$total_page_a = $total_page*$pgArr['record'];
			$pageSource[] = ' ... <a href="'.$params.'&no='.$total_page_a.'" class="bbsPage">'.$total_page_view.'</a>';
		}

		$result = implode(" ",$pageSource);
		return $result;
	}

	#------------------------------------#
	# 파일 업로드
	#------------------------------------#
	public function FileUp($path, $file)
	{
		$path_set = $_SERVER['DOCUMENT_ROOT'].$path;
		$upFile = $file['tmp_name'];
		$file_name = $file['name'];
		$file_size = $file['size'];

		for($i=0; $i<count($file_name); $i++)
		{
			/*
			if($file_name[$i])
			{
				If(!eregi(".jpg$|.JPG$|.bmp$|.BMP$|.gif$|.GIF$",$file_name[$i]))
				{
					$this->Location_msg( array('msg'=>'*.jpg,*.bmp,*.gif 파일만 업로드할 수 있습니다') );
					return;
				}
			}
			*/

			$fSize = 204800000; // 200MB -> 1MB = 1,000,000
			if($file_size[$i] >= $fSize)
			{
				$this->Location_msg( array('msg'=>'200MB를 초과!') );
				return;
			}


			if($file_name[$i])
			{
				$set_file_name = str_replace(" ", "_", $file_name[$i]);

				if($upFile[$i])
				{
					/***************
					원본 파일 작업
					***************/
					$fileN2 = strrchr($set_file_name, ".");
					$fileN1 = str_replace($fileN2, '', $set_file_name);
					$fileN1 = $fileN1.'_'.date("YmdHis");
					//$file_V_name = $fileN1."_".$i.$fileN2;
					$file_V_name = $fileN1.$fileN2;
					
					$file_V_name_path = $path_set."/".$file_V_name;

					move_uploaded_file($upFile[$i], $file_V_name_path);

					// 썸네일
					//$thumb = $this->Thumbnail_create(188, 124, $path_set, $file_V_name);

					// 파일명 디비저장
					$fileSet[$i]['file_name'] = $set_file_name;
					$fileSet[$i]['file_name_real'] = $file_V_name;
					$fileSet[$i]['file_size'] = $file_size[$i];
					$fileSet[$i]['path'] = $path;
					//if($thumb) $fileSet[$i]['thumb'] = $thumb;
				}
			}
		}

		return json_encode($fileSet, JSON_UNESCAPED_UNICODE);
	}

	#------------------------------------#
	# thumbnail 생성
	#------------------------------------#
	public function Thumbnail_create($w, $h, $path, $fileName)
	{
		$savePath = '';
		$thumbnail_w = $w;
		$thumbnail_h = $h;
		//$thumbnail_w = 170;
		//$thumbnail_h = 76;

		if($path || $fileName)
		{
			$fileVPath = $path.'/'.$fileName;
			$arr = array( '.jpg', '.jpeg', '.gif', '.png', '.bmp' );
			$exp = strrchr($fileName, '.');
			$exp = strtolower($exp);

			if(in_array($exp, $arr))
			{				
				$image_size = @getimagesize($fileVPath);
				$image_w = $image_size[0];
				$image_h = $image_size[1];
				
				if($exp==".jpg" || $exp==".JPG") $originImg = imagecreatefromjpeg($fileVPath);
				else if($exp==".gif" || $exp==".GIF") $originImg = imagecreatefromgif($fileVPath);
				else if($exp==".bmp" || $exp==".BMP") $originImg = imagecreatefrombmp($fileVPath);
				else if($exp==".png" || $exp==".PNG") $originImg = imagecreatefrompng($fileVPath);

				// 새 이미지틀 : 가로 180px, 세로 135px
				$newImg = imagecreatetruecolor($thumbnail_w, $thumbnail_h);

				// 이미지 틀에 원본이미지를 축소해서 넣는다
				imagecopyresampled($newImg, $originImg, 0, 0, 0, 0, $thumbnail_w, $thumbnail_h, $image_w, $image_h);

				// 저장
				$savePath = $path.'/'."thumb_".$fileName;
				if($exp==".jpg" || $exp==".JPG") imagejpeg($newImg, $savePath);
				else if($exp==".gif" || $exp==".GIF") imagegif($newImg, $savePath);
				else if($exp==".bmp" || $exp==".BMP") imagebmp($newImg, $savePath);
				else if($exp==".png" || $exp==".PNG") imagepng($newImg, $savePath);
			}
		}

		return $savePath;
	}

	#------------------------------------#
	# controller 로드
	#------------------------------------#
	public function Load_controllers()
	{
		$reDirect = str_replace("/index.php", "", $_SERVER["PHP_SELF"]);
		$reDirect = (empty($reDirect))? INDEX_PAGE_PATH: $reDirect;
		$dir = $_SERVER['DOCUMENT_ROOT'].CONTROLLERS_PATH.$reDirect;

		if( strpos($dir, ".php") === false ){
			$dir = $dir.".php";
		}

		// 파일 있는지 체크 : 없을때 내용 view
		if( !file_exists($dir) ){
			echo "<script>alert('해당 경로에 페이지가 없습니다.'); history.back();</script>";
			exit;
		}

		// class명 만들기
		$pathArr = explode("/", $reDirect);
		$pathCnt = count($pathArr) - 1;
		$setClassName = $pathArr[$pathCnt];
		//$setClassName = ucfirst( $setClassName ); // 첫글자 대문자 변경

		// 정상이면 페이지 로드
		include( $dir );
		new $setClassName; // 인스턴스 생성
	}

	#------------------------------------#
	# view 로드
	#------------------------------------#
	public function Load_view( $file='', $dataArr=[] )
	{
		list($fPath, $fExp) = explode(".", $file);
		$expArr = array( 'html', 'php' );
		if(!in_array( $fExp, $expArr )){
			echo '확장자를 입력해주세요.';
			return;
		}

		// 경로
		$path = $_SERVER['DOCUMENT_ROOT'].VIEW_PATH.$file;

		// 파일 있는지 체크
		if( !file_exists( $path ) ){
			echo 'Not File!';
			return;
		}

		// 배열 변수화
		extract( $dataArr );

		// 정상이면 페이지 로드
		include( $path );
	}

	#------------------------------------#
	# login 체크
	#------------------------------------#
	public function Login_chk()
	{
		$url = urlencode(base64_encode($_SERVER["REQUEST_URI"]));

		// 토큰 들어왔을때 세션 생성
		if( !empty($_GET['token']) ){
			$_SESSION['token'] = $_GET['token'];
		}

		if($_SESSION['user_id']){
			session_save_path($_SESSION['id'].".txt");
		}else{
			echo "
				<script>
					if(confirm('로그인이 필요한 서비스 입니다.\\n로그인 하시겠습니까?')){
						location.href='/adm/login?url=".$url."';
					}else{
						history.back();
					}					
				</script>
			";
			exit;
		}
	}

	#------------------------------------#
	# 지점 login 체크
	#------------------------------------#
	public function Login_staff_chk()
	{
		$url = urlencode(base64_encode($_SERVER["REQUEST_URI"]));

		if($_SESSION['user_id']){
			session_save_path($_SESSION['id'].".txt");
		}else{
			//$dataArr = array(
			//	"msg" => "로그인 해주세요!"
			//	, "url" => "/login.php?url=".$url
			//);
			//$this->Location_msg($dataArr);
			echo "
				<script>
					//if(confirm('로그인이 필요한 서비스 입니다.\\n로그인 하시겠습니까?')){
						location.href='/partner/login?url=".$url."';
					//}else{
					//	history.back();
					//}					
				</script>
			";
			exit;
		}
	}

	#------------------------------------#
	# msg location
	#------------------------------------#
	public function Location_msg($dataArr)
	{
		if(!$dataArr) exit;

		$alert = ($dataArr['msg'])? "alert('".$dataArr['msg']."');": "";
		$location = ($dataArr['url'])? "location.href = '".$dataArr['url']."';": "";

		echo "
			<script type='text/javascript'>
				".$alert."
				".$location."
			</script>
		";
	}

	#------------------------------------#
	# params 여백제거, 태그 변환
	#------------------------------------#
	public function Params_trim($arr=[])
	{
		$returnArr = array();
		foreach($arr as $k=>$val)
		{
			if( gettype($val) == 'array' ){
				$array = array();
				foreach($val as $v){
					$array[] = htmlspecialchars( $v ); // xss 방지용
				}
				$value = $array;
			}else{
				$value = htmlspecialchars( $val ); // xss 방지용
			}


			if( gettype($value) != 'array' ){
				$returnArr[$k] = trim($value);
			}else{
				$returnArr[$k] = $value;
			}
		}
		return $returnArr;
	}

	#------------------------------------#
	# SQL 인젝션 필터
	#------------------------------------#
	public function SQLFilter($str='')
	{
		$str = preg_replace("/\s{1,}1\=(.*)+/", "", $str); // 공백이후 1=1이 있을 경우 제거
		$str = preg_replace("/\s{1,}(or|and|null|where|limit)/i", " ", $str); // 공백이후 or, and 등이 있을 경우 제거
		$str = preg_replace("/[\s\t\'\;\=]+/", "", $str); // 공백이나 탭 제거, 특수문자 제거
		//$str = mysql_real_escape_string($str); // 백슬래시를 붙인다: \x00, \n, \r, \, ', ", \x1a.
		return $str;
	}

	#------------------------------------#
	# print_r
	#------------------------------------#
	public function print_r2($var)
	{
		ob_start();
		print_r($var);
		$str = ob_get_contents();
		ob_end_clean();
		$str = str_replace(" ", "&nbsp;", $str);
		echo nl2br("<span style='font-family:Tahoma, 굴림; font-size:9pt;'>$str</span>");
	}

	#------------------------------------#
	# 홑따옴표, 괄호 역슬레쉬 처리
	#------------------------------------#
	public function Replace_str($str)
	{
		$patterns[0] = '/\'/';
		$patterns[1] = '/\(/';
		$patterns[2] = '/\)/';
		$replacements[0] = '\\\'';
		$replacements[1] = '\\\(';
		$replacements[2] = '\\\)';
		$return = preg_replace($patterns, $replacements, $str);

		return $return;
	}

	#------------------------------------#
	# 암호화
	#------------------------------------#
	public function Encrypt($value)
	{
		$result = "";
		for($i=0; $i<strlen($value); $i++){
			$val1 = chr(ord(substr($value, $i, 1))+strlen($value));
			$key1 = substr(CRYPT_KEY, ($i % strlen(CRYPT_KEY)), 1);
			$val1 = $val1 ^ $key1;
			$result .= $val1;
		}
		return base64_encode($result);
	}

	#------------------------------------#
	# 복호화
	#------------------------------------#
	public function Decrypt($value)
	{
		$result = "";
		$value = base64_decode($value);
		for($i=strlen($value)-1; $i>=0; $i--){
			$val2 = substr($value, $i, 1);
			$key2 = substr(CRYPT_KEY, ($i % strlen(CRYPT_KEY)), 1);

			$val2 = $val2 ^ $key2;
			$val2 = chr(ord($val2)-strlen($value));
			$result = $val2.$result;
		}
		return $result;
	}

	#------------------------------------#
	# 단방향 sha512 암호화
	#------------------------------------#
	public function Hash512($str)
	{
		$hashed = base64_encode(hash("sha512", $str, true));
		return $hashed;
	}

	#------------------------------------#
	# 1일동안 New아이콘
	#------------------------------------#
	public function NewIconView($wirteDay)
	{
		$Rdate = explode(" ", $wirteDay);
		list($y,$m,$d) = explode("-", $Rdate[0]);
		list($h,$i,$s) = explode(":", $Rdate[1]);
		$oneDay = 60*60*24;
		$WriteTime =  mktime($h, $i, $s, $m, $d, $y) + $oneDay; //mktime(시간, 분, 초, 월, 달, 년)
		$nowTime = time();

		if($WriteTime >= $nowTime) {
			return '<img src="/images/icon/icon_new.gif" align="absmiddle" alt="new" />';
		}
	}

	# 글자수 제한하기 함수
	public function Cut_str($str, $len, $suffix="…")
	{
		$s = substr($str, 0, $len);
		$cnt = 0;

		for($i=0; $i<strlen($s); $i++){
			if(ord($s[$i]) > 127) $cnt++;
		}
		$s = substr($s, 0, $len - ($cnt % 3));
		if (strlen($s) >= strlen($str)){
			$suffix = "";
		}
		return $s.$suffix;
	}

	#------------------------------------#
	# 관리자 레벨 체크
	#------------------------------------#
	public function Admin_level_chk()
	{
		if($_SESSION['level'] != 10){
			$dataArr = array(
				"msg" => "관리자 ID로 로그인 해주세요!"
				, "url" => "/"
			);
			$this->Location_msg($dataArr);
		}
	}

	#------------------------------------#
	# 다음 오픈 에디터 툴 html source
	#------------------------------------#
	public function Tool()
	{
		$HTML = '<div id="tx_trex_container" class="tx-editor-container" style="z-index:0; width:100%; display:inline-block;">';
			$HTML .= '<!--';
			$HTML .= '@decsription';
			$HTML .= '툴바 버튼의 그룹핑의 변경이 필요할 때는 위치(왼쪽, 가운데, 오른쪽) 에 따라 <li> 아래의 <div>의 클래스명을 변경하면 된다.';
			$HTML .= 'tx-btn-lbg: 왼쪽, tx-btn-bg: 가운데, tx-btn-rbg: 오른쪽, tx-btn-lrbg: 독립적인 그룹';

			$HTML .= '드롭다운 버튼의 크기를 변경하고자 할 경우에는 넓이에 따라 <li> 아래의 <div>의 클래스명을 변경하면 된다.';
			$HTML .= 'tx-slt-70bg, tx-slt-59bg, tx-slt-42bg, tx-btn-43lrbg, tx-btn-52lrbg, tx-btn-57lrbg, tx-btn-71lrbg';
			$HTML .= 'tx-btn-48lbg, tx-btn-48rbg, tx-btn-30lrbg, tx-btn-46lrbg, tx-btn-67lrbg, tx-btn-49lbg, tx-btn-58bg, tx-btn-46bg, tx-btn-49rbg';
			$HTML .= '-->';
			$HTML .= '<div id="tx_toolbar_basic" class="tx-toolbar tx-toolbar-basic">';
				$HTML .= '<div class="tx-toolbar-boundary">';
					$HTML .= '<ul class="tx-bar tx-bar-left">';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div id="tx_fontfamily" unselectable="on" class="tx-slt-70bg tx-fontfamily">';
								$HTML .= '<a href="javascript:;" title="글꼴">굴림</a>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_fontfamily_menu" class="tx-fontfamily-menu tx-menu" unselectable="on"></div>';
						$HTML .= '</li>';
					$HTML .= '</ul>';
					$HTML .= '<ul class="tx-bar tx-bar-left">';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-slt-42bg tx-fontsize" id="tx_fontsize">';
								$HTML .= '<a href="javascript:;" title="글자크기">9pt</a>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_fontsize_menu" class="tx-fontsize-menu tx-menu" unselectable="on"></div>';
						$HTML .= '</li>';
					$HTML .= '</ul>';
					$HTML .= '<ul class="tx-bar tx-bar-left tx-group-font">';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-btn-lbg 	tx-bold" id="tx_bold">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="굵게 (Ctrl+B)">굵게</a>';
							$HTML .= '</div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-btn-bg 	tx-underline" id="tx_underline">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="밑줄 (Ctrl+U)">밑줄</a>';
							$HTML .= '</div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-btn-bg 	tx-italic" id="tx_italic">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="기울임 (Ctrl+I)">기울임</a>';
							$HTML .= '</div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-btn-bg 	tx-strike" id="tx_strike">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="취소선 (Ctrl+D)">취소선</a>';
							$HTML .= '</div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-slt-tbg 	tx-forecolor" id="tx_forecolor">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="글자색">글자색</a>';
								$HTML .= '<a href="javascript:;" class="tx-arrow" title="글자색 선택">글자색 선택</a>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_forecolor_menu" class="tx-menu tx-forecolor-menu tx-colorpallete" unselectable="on"></div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-slt-brbg tx-backcolor" id="tx_backcolor">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="글자 배경색">글자 배경색</a>';
								$HTML .= '<a href="javascript:;" class="tx-arrow" title="글자 배경색 선택">글자 배경색 선택</a>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_backcolor_menu" class="tx-menu tx-backcolor-menu tx-colorpallete" unselectable="on"></div>';
						$HTML .= '</li>';
					$HTML .= '</ul>';
					$HTML .= '<ul class="tx-bar tx-bar-left tx-group-align">';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-btn-lbg tx-alignleft" id="tx_alignleft">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="왼쪽정렬 (Ctrl+,)">왼쪽정렬</a>';
							$HTML .= '</div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-btn-bg tx-aligncenter" id="tx_aligncenter">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="가운데정렬 (Ctrl+.)">가운데정렬</a>';
							$HTML .= '</div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-btn-bg tx-alignright" id="tx_alignright">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="오른쪽정렬 (Ctrl+/)">오른쪽정렬</a>';
							$HTML .= '</div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-btn-rbg tx-alignfull" id="tx_alignfull">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="양쪽정렬">양쪽정렬</a>';
							$HTML .= '</div>';
						$HTML .= '</li>';
					$HTML .= '</ul>';
					$HTML .= '<ul class="tx-bar tx-bar-left tx-group-tab">';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-btn-lbg tx-indent" id="tx_indent">';
								$HTML .= '<a href="javascript:;" title="들여쓰기 (Tab)" class="tx-icon">들여쓰기</a>';
							$HTML .= '</div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-btn-rbg tx-outdent" id="tx_outdent">';
								$HTML .= '<a href="javascript:;" title="내어쓰기 (Shift+Tab)" class="tx-icon">내어쓰기</a>';
							$HTML .= '</div>';
						$HTML .= '</li>';
					$HTML .= '</ul>';
					$HTML .= '<ul class="tx-bar tx-bar-left tx-group-list">';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-slt-31lbg tx-lineheight" id="tx_lineheight">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="줄간격">줄간격</a>';
								$HTML .= '<a href="javascript:;" class="tx-arrow" title="줄간격">줄간격 선택</a>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_lineheight_menu" class="tx-lineheight-menu tx-menu" unselectable="on"></div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-slt-31rbg tx-styledlist" id="tx_styledlist">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="리스트">리스트</a>';
								$HTML .= '<a href="javascript:;" class="tx-arrow" title="리스트">리스트 선택</a>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_styledlist_menu" class="tx-styledlist-menu tx-menu" unselectable="on"></div>';
						$HTML .= '</li>';
					$HTML .= '</ul>';
					$HTML .= '<ul class="tx-bar tx-bar-left tx-group-etc">';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class=" tx-btn-lbg tx-emoticon" id="tx_emoticon">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="이모티콘">이모티콘</a>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_emoticon_menu" class="tx-emoticon-menu tx-menu" unselectable="on"></div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class=" tx-btn-lbg tx-link" id="tx_link">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="링크 (Ctrl+K)">링크</a>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_link_menu" class="tx-link-menu tx-menu"></div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-btn-bg tx-specialchar" id="tx_specialchar">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="특수문자">특수문자</a>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_specialchar_menu" class="tx-specialchar-menu tx-menu"></div>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-btn-lbg tx-table" id="tx_table">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="표만들기">표만들기</a>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_table_menu" class="tx-table-menu tx-menu" unselectable="on">';
								$HTML .= '<div class="tx-menu-inner">';
									$HTML .= '<div class="tx-menu-preview"></div>';
									$HTML .= '<div class="tx-menu-rowcol"></div>';
									$HTML .= '<div class="tx-menu-deco"></div>';
									$HTML .= '<div class="tx-menu-enter"></div>';
								$HTML .= '</div>';
							$HTML .= '</div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-btn-rbg tx-horizontalrule" id="tx_horizontalrule">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="구분선">구분선</a>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_horizontalrule_menu" class="tx-horizontalrule-menu tx-menu" unselectable="on"></div>';
						$HTML .= '</li>';
					$HTML .= '</ul>';

					$HTML .= '<ul class="tx-bar tx-bar-left">';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-image tx-btn-trans">';
								$HTML .= '<a href="#" title="사진" class="tx-text" onclick="window.open(\'/resource/web_editor/image.php\',\'images_up\',\'width=400, height=200\'); return false;">사진</a>';
							$HTML .= '</div>';
						$HTML .= '</li>';
					$HTML .= '</ul>';

					$HTML .= '<ul class="tx-bar tx-bar-right">';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-switchtoggle" id="tx_switchertoggle">';
								$HTML .= '<a href="javascript:;" title="에디터 타입">에디터</a>';
							$HTML .= '</div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-btn-nlrbg tx-advanced" id="tx_advanced">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="툴바 더보기">툴바 더보기</a>';
							$HTML .= '</div>';
						$HTML .= '</li>';
					$HTML .= '</ul>';
				$HTML .= '</div>';
			$HTML .= '</div>';

			$HTML .= '<!-- 툴바 - 더보기 시작 -->';
			$HTML .= '<div id="tx_toolbar_advanced" class="tx-toolbar tx-toolbar-advanced">';
				$HTML .= '<div class="tx-toolbar-boundary">';
					$HTML .= '<ul class="tx-bar tx-bar-left">';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div class="tx-tableedit-title"></div>';
						$HTML .= '</li>';
					$HTML .= '</ul>';

					$HTML .= '<ul class="tx-bar tx-bar-left tx-group-align">';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-btn-lbg tx-mergecells" id="tx_mergecells">';
								$HTML .= '<a href="javascript:;" class="tx-icon2" title="병합">병합</a>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_mergecells_menu" class="tx-mergecells-menu tx-menu" unselectable="on"></div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-btn-bg tx-insertcells" id="tx_insertcells">';
								$HTML .= '<a href="javascript:;" class="tx-icon2" title="삽입">삽입</a>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_insertcells_menu" class="tx-insertcells-menu tx-menu" unselectable="on"></div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class="tx-btn-rbg tx-deletecells" id="tx_deletecells">';
								$HTML .= '<a href="javascript:;" class="tx-icon2" title="삭제">삭제</a>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_deletecells_menu" class="tx-deletecells-menu tx-menu" unselectable="on"></div>';
						$HTML .= '</li>';
					$HTML .= '</ul>';

					$HTML .= '<ul class="tx-bar tx-bar-left tx-group-align">';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div id="tx_cellslinepreview" unselectable="on" class="tx-slt-70lbg tx-cellslinepreview">';
								$HTML .= '<a href="javascript:;" title="선 미리보기"></a>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_cellslinepreview_menu" class="tx-cellslinepreview-menu tx-menu"  unselectable="on"></div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div id="tx_cellslinecolor" unselectable="on" class="tx-slt-tbg tx-cellslinecolor">';
								$HTML .= '<a href="javascript:;" class="tx-icon2" title="선색">선색</a>';

								$HTML .= '<div class="tx-colorpallete" unselectable="on"></div>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_cellslinecolor_menu" class="tx-cellslinecolor-menu tx-menu tx-colorpallete" unselectable="on"></div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div id="tx_cellslineheight" unselectable="on" class="tx-btn-bg tx-cellslineheight">';
								$HTML .= '<a href="javascript:;" class="tx-icon2" title="두께">두께</a>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_cellslineheight_menu" class="tx-cellslineheight-menu tx-menu" unselectable="on"></div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div id="tx_cellslinestyle" unselectable="on" class="tx-btn-bg tx-cellslinestyle">';
								$HTML .= '<a href="javascript:;" class="tx-icon2" title="스타일">스타일</a>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_cellslinestyle_menu" class="tx-cellslinestyle-menu tx-menu" unselectable="on"></div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div id="tx_cellsoutline" unselectable="on" class="tx-btn-rbg tx-cellsoutline">';
								$HTML .= '<a href="javascript:;" class="tx-icon2" title="테두리">테두리</a>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_cellsoutline_menu" class="tx-cellsoutline-menu tx-menu" unselectable="on"></div>';
						$HTML .= '</li>';
					$HTML .= '</ul>';

					$HTML .= '<ul class="tx-bar tx-bar-left">';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div id="tx_tablebackcolor" unselectable="on" class="tx-btn-lrbg tx-tablebackcolor" style="background-color:#9aa5ea;">';
								$HTML .= '<a href="javascript:;" class="tx-icon2" title="테이블 배경색">테이블 배경색</a>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_tablebackcolor_menu" class="tx-tablebackcolor-menu tx-menu tx-colorpallete" unselectable="on"></div>';
						$HTML .= '</li>';
					$HTML .= '</ul>';

					$HTML .= '<ul class="tx-bar tx-bar-left">';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div id="tx_tabletemplate" unselectable="on" class="tx-btn-lrbg tx-tabletemplate">';
								$HTML .= '<a href="javascript:;" class="tx-icon2" title="테이블 서식">테이블 서식</a>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_tabletemplate_menu" class="tx-tabletemplate-menu tx-menu tx-colorpallete" unselectable="on"></div>';
						$HTML .= '</li>';
					$HTML .= '</ul>';

					$HTML .= '<ul class="tx-bar tx-bar-left">';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class=" tx-btn-lbg tx-richtextbox" id="tx_richtextbox">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="글상자">글상자</a>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_richtextbox_menu" class="tx-richtextbox-menu tx-menu">';
								$HTML .= '<div class="tx-menu-header">';
									$HTML .= '<div class="tx-menu-preview-area">';
										$HTML .= '<div class="tx-menu-preview"></div>';
									$HTML .= '</div>';
									$HTML .= '<div class="tx-menu-switch">';
										$HTML .= '<div class="tx-menu-simple tx-selected"><a><span>간단 선택</span></a></div>';
										$HTML .= '<div class="tx-menu-advanced"><a><span>직접 선택</span></a></div>';
									$HTML .= '</div>';
								$HTML .= '</div>';
								$HTML .= '<div class="tx-menu-inner">';
								$HTML .= '</div>';
								$HTML .= '<div class="tx-menu-footer">';
									$HTML .= '<img class="tx-menu-confirm" src="/images/web_editor/icon/editor/btn_confirm.gif?rv=1.0.1" alt=""/>';
									$HTML .= '<img class="tx-menu-cancel" hspace="3" src="/images/web_editor/icon/editor/btn_cancel.gif?rv=1.0.1" alt=""/>';
								$HTML .= '</div>';
							$HTML .= '</div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class=" tx-btn-bg tx-quote" id="tx_quote">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="인용구 (Ctrl+Q)">인용구</a>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_quote_menu" class="tx-quote-menu tx-menu" unselectable="on"></div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class=" tx-btn-bg tx-background" id="tx_background">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="배경색">배경색</a>';
							$HTML .= '</div>';
							$HTML .= '<div id="tx_background_menu" class="tx-menu tx-background-menu tx-colorpallete" unselectable="on"></div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class=" tx-btn-rbg tx-dictionary" id="tx_dictionary">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="사전">사전</a>';
							$HTML .= '</div>';
						$HTML .= '</li>';
					$HTML .= '</ul>';
					$HTML .= '<ul class="tx-bar tx-bar-left tx-group-undo">';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class=" tx-btn-lbg tx-undo" id="tx_undo">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="실행취소 (Ctrl+Z)">실행취소</a>';
							$HTML .= '</div>';
						$HTML .= '</li>';
						$HTML .= '<li class="tx-list">';
							$HTML .= '<div unselectable="on" class=" tx-btn-rbg tx-redo" id="tx_redo">';
								$HTML .= '<a href="javascript:;" class="tx-icon" title="다시실행 (Ctrl+Y)">다시실행</a>';
							$HTML .= '</div>';
						$HTML .= '</li>';
					$HTML .= '</ul>';
				$HTML .= '</div>';
			$HTML .= '</div>';
			$HTML .= '<!-- 툴바 - 더보기 끝 -->';

			$HTML .= '<!-- 편집영역 시작 -->';
			$HTML .= '<!-- 에디터 Start -->';
			$HTML .= '<div id="tx_canvas" class="tx-canvas">';
				$HTML .= '<div id="tx_loading" class="tx-loading"><div><img src="/resource/web_editor/images/icon/editor/loading2.png" width="113" height="21" align="absmiddle"/></div></div>';
				$HTML .= '<div id="tx_canvas_wysiwyg_holder" class="tx-holder" style="display:block;">';
					$HTML .= '<iframe id="tx_canvas_wysiwyg" name="tx_canvas_wysiwyg" allowtransparency="true" frameborder="0"></iframe>';
				$HTML .= '</div>';
				$HTML .= '<div class="tx-source-deco">';
					$HTML .= '<div id="tx_canvas_source_holder" class="tx-holder">';
						$HTML .= '<textarea id="tx_canvas_source" rows="30" cols="30"></textarea>';
					$HTML .= '</div>';
				$HTML .= '</div>';
				$HTML .= '<div id="tx_canvas_text_holder" class="tx-holder">';
					$HTML .= '<textarea id="tx_canvas_text" rows="30" cols="30"></textarea>';
				$HTML .= '</div>';
			$HTML .= '</div>';

			$HTML .= '<!-- 높이조절 Start -->';
			$HTML .= '<div id="tx_resizer" class="tx-resize-bar">';
				$HTML .= '<div class="tx-resize-bar-bg"></div>';
				$HTML .= '<img id="tx_resize_holder" src="/images/web_editor/icon/editor/skin/01/btn_drag01.gif" width="58" height="12" unselectable="on" alt="" />';
			$HTML .= '</div>';
			$HTML .= '<!--요건 에디터 로고 주석처리 한거임';
			$HTML .= '<div class="tx-side-bi" id="tx_side_bi">';
				$HTML .= '<div style="text-align: right;">';
					$HTML .= '<img hspace="4" height="14" width="78" align="absmiddle" src="/images/web_editor/icon/editor/editor_bi.png" />';
				$HTML .= '</div>';
			$HTML .= '</div>';
			$HTML .= '-->';
			$HTML .= '<!-- 편집영역 끝 -->';

			$HTML .= '<!-- 첨부박스 시작 -->';
			$HTML .= '<div id="tx_attach_div" class="tx-attach-div">';
				$HTML .= '<div style="display:none;"><!--요고 첨부내용 않보이게 해주려고 넣었지롱-->';
				$HTML .= '<div id="tx_attach_txt" class="tx-attach-txt">파일 첨부</div>';
					$HTML .= '<div id="tx_attach_box" class="tx-attach-box">';
						$HTML .= '<div class="tx-attach-box-inner">';
							$HTML .= '<div id="tx_attach_preview" class="tx-attach-preview"><p></p><img src="/images/web_editor/icon/editor/pn_preview.gif" width="147" height="108" unselectable="on"/></div>';
							$HTML .= '<div class="tx-attach-main">';
								$HTML .= '<div id="tx_upload_progress" class="tx-upload-progress"><div>0%</div><p>파일을 업로드하는 중입니다.</p></div>';
								$HTML .= '<ul class="tx-attach-top">';
									$HTML .= '<li id="tx_attach_delete" class="tx-attach-delete"><a>전체삭제</a></li>';
									$HTML .= '<li id="tx_attach_size" class="tx-attach-size">파일: <span id="tx_attach_up_size" class="tx-attach-size-up"></span>/<span id="tx_attach_max_size"></span></li>';
									$HTML .= '<li id="tx_attach_tools" class="tx-attach-tools"></li>';
								$HTML .= '</ul>';
								$HTML .= '<ul id="tx_attach_list" class="tx-attach-list"></ul>';
							$HTML .= '</div>';
						$HTML .= '</div>';
					$HTML .= '</div>';
				$HTML .= '</div>';
			$HTML .= '</div>';
			$HTML .= '<!-- 첨부박스 끝 -->';
		$HTML .= '</div>';

		$HTML .= '<link rel="stylesheet" href="/resource/web_editor/editor.css" type="text/css" charset="euc-kr" />';
		$HTML .= '<script src="/resource/web_editor/editor_loader.js" type="text/javascript" charset="euc-kr"></script>';
		$HTML .= '<script src="/resource/web_editor/customize.js" type="text/javascript" charset="euc-kr"></script>';

		return $HTML;
	}

	#------------------------------------#
	# 바이트 변환 함수
	#------------------------------------#
	public function Unit_size($size)
	{
		if(!$size) return "0 Byte";

		if($size < 1024) {
			return "$size Byte";
		} elseif($size >= 1024 && $size < 1024 * 1024) {
			return sprintf("%0.1f",$size / 1024)." KB";
		} elseif($size >= 1024 * 1024 && $size < 1024 * 1024 * 1024) {
			return sprintf("%0.1f",$size / 1024 / 1024)." MB";
		} else {
			return sprintf("%0.1f",$size / 1024 / 1024 / 1024)." GB";
		}
	}

	#------------------------------------#
	# 10개의 랜덤 숫자+영어 생성 후 5개의 문자를 추출
	# 게시판 댓글달기에 적용
	# 1234567890/12345 -> explode() 사용
	#------------------------------------#
	public function randCharsCreate()
	{
		$chars = "0123456789abcdefghijklmnopqrstuvwxyz";
		$len = strlen($chars)-1;

		for($i=0; $i<10; $i++)
		{
			$startStr = rand(0, $len);
			$str .= substr($chars, $startStr, 1) . ",";
		}
		$strArrSet = substr($str, 0, 19);
		$strArr = explode(",", $strArrSet);

		// 중복되지 않는 랜덤 숫자 추출
		$rand_array = array();
		for($j=0; $j<5; $j++)
		{
			while(in_array($rand=rand(0,9), $rand_array) == true); //중복되면 다시 돌림
			$rand_array[$j] = $rand; //중복되지 않으면 배열에 저장
		}

		$strChars = '';
		for($i=0; $i<10; $i++)
		{
			if($rand_array[0]==$i || $rand_array[1]==$i || $rand_array[2]==$i || $rand_array[3]==$i || $rand_array[4]==$i)
			{
				$strChars .= '<span class="randChars0">'.$strArr[$i].'</span>';
				$quizChars .= $strArr[$i];
			}
			else $strChars .= '<span class="randChars1">'.$strArr[$i].'</span>';
		}

		$return = array(
			'str_chars' => $strChars
			, 'quiz_chars' => $quizChars
		);

		return $return;
	}

	# 메일 전송이 base64 인코딩
	public function EncodeSet($str)
	{
		//$setChar = 'euc-kr';
		$setChar = 'utf-8';
		$res = "=?".$setChar."?B?".base64_encode($str)."?=";
		return $res;
	}

	#------------------------------------#
	# 파일 첨부 할때 $file = 파일 경로 및 실제 이름
	#------------------------------------#
	public function Attach_file($file)
	{
		$size = filesize($file);
		$fp = fopen($file, "r");
		$tmpfile = fread($fp, $size);
		fclose($fp);
		return $tmpfile;
	}

	#------------------------------------#
	# 메일 바운드리
	#------------------------------------#
	public function Get_boundary()
	{
		$uniqchr = uniqid(time());
		$one = strtoupper($uniqchr[0]);
		$two = strtoupper(substr($uniqchr,0,8));
		$three = strtoupper(substr(strrev($uniqchr),0,8));
		return "----=_NextPart_000_000${one}_${two}.${three}";
	}

	#------------------------------------#
	# 메일 전송
	#------------------------------------#
	public function SendMailer($from, $toName, $to, $subject, $body, $upFile)
	{
		//$setChar = 'euc-kr';
		$setChar = 'utf-8';
		$RN = "\r\n";
		$RNRN = "\r\n\r\n";

		$damname = $this->EncodeSet($toName);
		$subject	= $this->EncodeSet($subject);

		$header .= 'Reply-To: '.$to.$RN; 
		$header .= 'From: '.$damname.' <'.$from.'>'.$RN;
		$header .= 'X-Sender: <'.$from.'>'.$RN;
		$header .= 'X-Mailer: PHP '.phpversion().$RN; 
		$header .= 'X-Priority: 3'.$RN;

		if($upFile['tmp_name'][0]) // 첨부파일 있을때
		{
			$boundary = $this->Get_boundary();

			$header   .= 'MIME-Version: 1.0'.$RN;    // MIME 버전 표시 
			$header   .= 'Content-Type: multipart/mixed; boundary="'.$boundary.'"'.$RNRN;
			$header   .= 'This is a multi-part message in MIME format'.$RNRN;

			$mailbody .= '--'.$boundary.$RN.
			$mailbody .= 'Content-Type: text/html; charset="'.$setChar.'"'.$RN;
			$mailbody .= 'Content-Transfer-Encoding: base64'.$RNRN;
			$mailbody .= chunk_split(base64_encode($body)).$RNRN;

			for($i=0;$i<count($upFile['tmp_name']);$i++) 
			{
				if($upFile['tmp_name'][$i]) 
				{	
					$limit = 20;
					if($upFile['size'][$i] > ($limit * 1024 * 1024)) 
					{
						//$this->back(($i+1).'번째 첨부파일이 제한용량('.$limit.'MB)을 초과하였습니다.');
						exit;
					}
					
					$file = $this->Attach_file($upFile['tmp_name'][$i]); // 파일을 읽어오자
					$filename = basename($upFile['name'][$i]);

					$mailbody .= '--'.$boundary.$RN;
					$mailbody .= 'Content-Type: application/octet-stream; name="'.$filename.'"'.$RN;
					$mailbody .= 'Content-Transfer-Encoding: base64'.$RN;
					$mailbody .= 'Content-Disposition: attachment; filename="'.$this->EncodeSet($filename).'"'.$RNRN;
					$mailbody .= chunk_split(base64_encode($file)).$RN;
				}
			}
			$mailbody .= '--'.$boundary.'--'.$RN;
		}
		else
		{
			$header .= 'Content-Type:text/html; charset='.$setChar.$RN; 
			$mailbody = nl2br(stripslashes($body)).$RNRN;
		}
		
		if(mail($to, $subject, $mailbody, $header, '-f'.$from)) $res = true;
		else $res = false;

		return $res;
	}

	#------------------------------------#
	# 이메일 괄호 처리
	#------------------------------------#
	public function Mail_replace( $str='' )
	{
		$str = trim($str);
		if(strpos($str, '(') !== false){
			$strMailArr = explode("(", $str);
			$strMail = str_replace(")", "", $strMailArr[1]);
		}else{
			$strMail = $str;
		}

		return $strMail;
	}

	#------------------------------------#
	# 메일 유효성 검사
	#------------------------------------#
	public function Mail_chk( $email='' )
	{
		$check_email = preg_match( "/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $email );
		return $check_email;
	}

	#------------------------------------#
	# 문자발송 api
	#------------------------------------#
	public function SmsSender($number='', $title='', $message='', $image='', $sender='')
	{
		$sender_num = ($sender)? $sender: '1600-7728';

		/**************** 문자전송하기 예제 ******************/
		# "result_code":결과코드,"message":결과문구,
		# "msg_id":메세지ID,"error_cnt":에러갯수,"success_cnt":성공갯수
		# 동일내용 > 전송용 입니다.  
		/******************** 인증정보 ********************/
		$sms_url = "https://apis.aligo.in/send/"; // 전송요청 URL
		$sms['user_id'] = "modoo24"; // SMS 아이디
		$sms['key'] = "jqcikv92tvfqk0obiof3skpuk6w1chp2"; // 인증키
		/******************** 인증정보 ********************/		

		$sms['msg'] = stripslashes($message);
		$sms['receiver'] =  $number; // '01111111111,01111111112'; // 수신번호
		//$sms['destination'] = $_POST['destination']; // '01111111111|담당자,01111111112|홍길동'; // 수신인 %고객명% 치환
		$sms['sender'] = $sender_num; // 발신번호
		//$sms['rdate'] = date("Ymd"); // 예약일자 - 20161004 : 2016-10-04일기준
		//$sms['rtime'] = date("Hi"); // 예약시간 - 1930 : 오후 7시30분
		//$sms['testmode_yn'] = 'Y'; // Y 인경우 실제문자 전송X , 자동취소(환불) 처리
		if($title) $sms['title'] = $title;

		// 이미지 전송시
		if(!empty($image)){ // '/tmp/pic_57f358af08cf7_sms_.jpg'; // MMS 이미지 파일 위치
			if(file_exists($image)){
				$tmpFile = explode('/', $image);
				$str_filename = $tmpFile[sizeof($tmpFile)-1];
				$tmp_filetype = 'image/jpeg';
				$sms['image'] = '@'.$image.';filename='.$str_filename. ';type='.$tmp_filetype;
			}
		}


		$host_info = explode("/", $sms_url);
		$port = $host_info[0] == 'https:' ? 443 : 80;

		$oCurl = curl_init();
		curl_setopt($oCurl, CURLOPT_PORT, $port);
		curl_setopt($oCurl, CURLOPT_URL, $sms_url);
		curl_setopt($oCurl, CURLOPT_POST, 1);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($oCurl, CURLOPT_POSTFIELDS, $sms);
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
		$ret = curl_exec($oCurl);
		curl_close($oCurl);

		//echo $ret;
		$retArr = json_decode($ret, true); // 결과배열
		return $retArr;
	}	

	#------------------------------------#
	# 문자열 별표처리
	#------------------------------------#
	public function Str_star($n, $str)
	{
		$return = preg_replace( '/(?<=.{'.$n.'})./u', '*', $str );
		//$return = substr($str, 0, $n).str_repeat("*", strlen($str)-$n);
		return $return;
	}

	#------------------------------------#
	# curl 파라미터 전송 함수
	# $insertArr = []; // 파라미터값
	# $url = 'http://dbdbdeep.com/site19/gate/modoo24/join.php';
	# $returnArr = $this->Request_curl( $url, 1, $insertArr ); // 1 = post, 0 = get
	#------------------------------------#
	public function Request_curl( $url, $is_post=0, $data=[], $custom_header=null ) 
	{
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL,$url );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $ch, CURLOPT_SSLVERSION,1 );
		curl_setopt( $ch, CURLOPT_POST, $is_post );
		if( $is_post ){
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
		}
	 
		curl_setopt( $ch, CURLOPT_TIMEOUT, 300 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
		//curl_setopt( $ch, CURLOPT_HEADER, true );
	 
		if($custom_header){
			curl_setopt( $ch, CURLOPT_HTTPHEADER, $custom_header );
		}
		$result[0] = curl_exec( $ch );
		$result[1] = curl_errno( $ch );
		$result[2] = curl_error( $ch );
		$result[3] = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );

		return $result;
	}

	#------------------------------------#
	# 브라우저 체크
	#------------------------------------#
	public function Get_browser_info()
	{
		$userAgent = $_SERVER["HTTP_USER_AGENT"]; 

		if(preg_match('/MSIE/i',$userAgent) && !preg_match('/Opera/i',$u_agent)){
			$browser = 'explorer';
		}else if(preg_match('/Firefox/i',$userAgent)){
			$browser = 'firefox';
		}else if (preg_match('/Chrome/i',$userAgent)){
			$browser = 'chrome';
		}else if(preg_match('/Safari/i',$userAgent)){
			$browser = 'safari';
		}else if(preg_match('/Opera/i',$userAgent)){
			$browser = 'opera';
		}else if(preg_match('/Netscape/i',$userAgent)){
			$browser = 'netscape';
		}else{
			$browser = "other";
		}

		return $browser;
	}	

	#------------------------------------#
	# 문자열 전화번호 제거
	#------------------------------------#
	public function Del_str_hp( $str='' )
	{
		$re = '~"[^"]*"|\'[^\']*\'|(\+?[\d-\(\)\s]{8,25}[0-9]?\d)~';
		preg_match_all( $re, $str, $m, PREG_PATTERN_ORDER );
		$result = array_filter($m[1]);
		//print_r($result );
		$return = str_replace( $result, "", $str );
		return $return;
	}

	#------------------------------------#
	# php 수행시간 측정을 위한 함수
	#------------------------------------#
	public function GetTimeChk() 
	{ 
		$t = explode(' ', microtime()); 
		return (float)$t[0] + (float)$t[1]; 
	}

	#------------------------------------#
	# 지나간 시간 계산
	#------------------------------------#
	public function PassingTime($datetime) 
	{
		$time_lag = time() - strtotime($datetime);
		
		if($time_lag < 60) { // 1분 미만
			$posting_time = "방금";
		} elseif($time_lag >= 60 and $time_lag < 3600) { // 1시간 미만
			$posting_time = floor($time_lag/60)."분 전";
		} elseif($time_lag >= 3600 and $time_lag < 43200) { // 12시간 미만
			$posting_time = floor($time_lag/3600)."시간 전";
		//} elseif($time_lag >= 86400 and $time_lag < 2419200) {
		//	$posting_time = floor($time_lag/86400)."일 전";
		} else {
			$posting_time = date("Y-m-d", strtotime($datetime));
		} 
		
		return $posting_time;
	}

	#------------------------------------#
	# 인클루드 된 모든 파일 보여주기
	#------------------------------------#
	public function ViewIncludeFile()
	{
		$included_files = get_included_files();

		$arr = [];
		$arr[] = "<h3>현재페이지 인클루드된 파일리스트</h3>";    
		foreach ($included_files as $filename) {
			$arr[] =  $filename;    
		}
		$return = implode('<br>', $arr);

		return $return;
	}
}
