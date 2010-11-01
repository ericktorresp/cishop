<?php

class Welcome extends Controller {

	function __construct()
	{
		$this->Welcome();
	}

	function Welcome()
	{
		parent::Controller();
		$this->load->model('VideoModel');
	}

	function index()
	{
		$data = array(
			'title' => $this->config->item('site_title') . ' : Welcome',
		);
		# 首页推荐(1)
		
		# 最新(6)
		
		#最多人观看(6)
		
		$this->load->view('header', $data);
		$this->load->view('welcome_message');
		$this->load->view('footer');
	}
}