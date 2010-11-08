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

	/**
	 * 播放页
	 */
	public function watch()
	{
		if($video = $this->VideoModel->video(0, $this->input->get('v')))
		{
			var_dump($video);
		}
		else
		{
			show_404();
		}
	}

	/**
	 * 视频列表页
	 * @param int $offset
	 */
	public function index($offset=0)
	{
		$result = $this->VideoModel->videos(0, $offset, 20);
		$this->load->library('pagination');
		$config['base_url'] = "/video/";
		$config['total_rows'] = $result['total'];
		$config['per_page'] = '20';
		$config['page_query_string'] = FALSE;
		$config['uri_segment'] = 2;
		$this->pagination->initialize($config);
		$data = array('videos'=>$result['data'], 'pagination'=>$this->pagination->create_links());
		$this->load->view('video_list', $data);
	}

	/**
	 * 防止盗链, 返回视频rtmfp地址
	 * 还是 FMS 服务端验证用户有效性?
	 */
	public function address($key)
	{
		if($video = $this->VideoModel->video(0, $key))
		{
			$address = 'rtmfp://'.$video->server.'/vod/';
			if($server->mime == 'mp4' || $server->mime == 'f4v')
			{
				$address .= 'mp4:';
			}
			$address .= $server->file_name;
			echo $address;
			return;
		}
	}
}