<?php

class Welcome extends Controller {

	function __construct()
	{
		$this->Welcome();
	}

	function Welcome()
	{
		parent::Controller();
	}

	function index()
	{
		$data = array(
			'title' => $this->config->item('site_title') . ' : Welcome',
		);
		$this->load->view('header', $data);
		$this->load->view('welcome_message');
		$this->load->view('footer');
	}
}