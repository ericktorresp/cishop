<?php
/**
 * 站点首页控制器
 * @author Floyd
 *
 */
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
		
		# 最新(20)
		$data['recents'] = $this->VideoModel->videos(0);
		# 最多人观看(6)
		$data['watched'] = $this->VideoModel->watched('month');
//		$this->load->view('index', $data);
	}
}