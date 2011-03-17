<?php
class Captcha_Model extends CI_Model
{
	var $img_path = './captcha/';
	var $img_url = '/captcha/';
	
	public function varifyCaptcha()
	{
		$expiration = time()-$this->config['sess_expiration'];
	}
}