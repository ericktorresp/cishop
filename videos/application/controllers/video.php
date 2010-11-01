<?php
/**
 * 视频前台页面控制器
 * @author Floyd
 *
 */
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
		if($video = $this->VideoModel->video(0, $this->input->get('key')))
		{
			
		}
		else
		{
			show_404();
		}
	}
}