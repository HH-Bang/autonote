<?
spl_autoload_register(function($class){
	//echo $class.'<br>';
	// 나자신을 호출 했을때 패스
	if(strpos($class, '\\') !== false){
		if( $class != 'lib\mysqli' ){
			$path = $_SERVER['DOCUMENT_ROOT'].'/'.$class;
			$classFile = str_replace('\\', '/', $path);
			require_once( $classFile.'.php' );
		}
	}
});
?>