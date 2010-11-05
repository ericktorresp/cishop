<?php
/**
 * 后台管理首页控制器
 * @author Floyd
 *
 */
class Welcome extends Controller
{
	public function __constuct()
	{
		$this->Welcome();
	}

	public function Welcome()
	{
		parent::Controller();
		if(!$this->session->userdata('uid') || !$this->session->userdata('is_admin'))
		{
			$this->session->set_flashdata('url','/admin/');
			redirect('/login');
		}
	}

	public function index()
	{
		redirect('admin/video');
	}
}