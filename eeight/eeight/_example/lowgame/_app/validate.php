<?php
//生成一个验证码，并把验证码信息存到session里面
	$validate = new validatecode();
	$validate->setImage(array('width'=>80,'height'=>25,'type'=>'png'));
	$validate->setCode( array('characters'=>'0-9,A-Z','length'=>4,'deflect'=>FALSE,'multicolor'=>FALSE) );
	$validate->setFont( array("space"=>3,"size"=>14,"left"=>5,"top"=>20,"file"=>'') ); 
	$validate->setMolestation( array("type"=>FALSE,"density"=>'fewness') );
	$validate->setBgColor( array('r'=>39,'g'=>130,'b'=>150) );
	$validate->setFgColor( array('r'=>255,'g'=>255,'b'=>255) );
	
	/*
	 * 将验证码信息保存到session
	 */
	// 输出到浏览器 
	$validate->paint();
	$_SESSION['validateCode'] = $validate->getcode();
?>