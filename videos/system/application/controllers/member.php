<?php
class Member extends Controller
{
	function member()
	{
		parent::Controller();
		$this->load->model('user_model'); 
	}
	
	function login()
	{
		
	}
}