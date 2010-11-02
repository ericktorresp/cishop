<?php

class Server extends Controller
{
	
	public function __construct()
	{
		$this->Server();
	}
	
	public function Server()
	{
		parent::Controller();
		$this->load->model('ServersModel');
	}
	
	public function index()
	{
		
	}
	
	public function add()
	{
		
	}
	
	public function edit($sid)
	{
		
	}
	
	public function delete($sid)
	{
		
	}
}