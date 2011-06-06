<?php
//生成一个验证码，并把验证码信息存到session里面
	$validate = new validatecode();
	$validate->setImage( array('width'=>95, 'height'=>32, 'type'=>'jpg') );
	$validate->setCode( array('characters'=>'0-9','length'=>4,'deflect'=>FALSE,'multicolor'=>FALSE) );
	$validate->setFont( array("space"=>3,"size"=>16,"left"=>9,"top"=>24,"file"=>'') ); 
	$validate->setMolestation( array("type"=>FALSE,"density"=>'fewness') );
	$validate->setBgColor( array('r'=>197,'g'=>197,'b'=>190) );
	$validate->setFgColor( array('r'=>26,'g'=>26,'b'=>26) );
	
	/*
	 * 将验证码信息保存到session
	 */
	// 输出到浏览器 
	$validate->paint();
	$_SESSION['validateCode'] = $validate->getcode();
?>