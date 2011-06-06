<?php
//生成一个验证码，并把验证码信息存到session里面
	$validate = new validatecode();
	$validate->setImage(array('width'=>115,'height'=>30,'type'=>'jpg'));
	$validate->setCode( array('characters'=>'2-9,A,B,C,D,E,F,G,H,J,K,M,N,P,Q,R,S,T,U,V,W,X,Y','length'=>4,'deflect'=>FALSE,'multicolor'=>FALSE) );
	$validate->setFont( array("space"=>10,"size"=>18,"left"=>10,"top"=>25,"file"=>'') ); 
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