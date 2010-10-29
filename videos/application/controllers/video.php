<?php

class Video extends Controller
{
	
	public function __construct()
	{
		$this->Video();
	}
	
	public function Video()
	{
		parent::Controller();
		$this->load->model('VideoModel');
	}
	
	public function index()
	{
		
	}
	
	public function watch()
	{
		
	}
}