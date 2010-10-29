<?php
class Welcome extends Controller
{
	public function __constuct()
	{
		$this->Welcome();
	}
	
	public function Welcome()
	{
		parent::Controller();
	}
	
	public function index()
	{
		$this->load->view('admin/welcome');
	}
}