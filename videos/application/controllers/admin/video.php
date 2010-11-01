<?php
/**
 * 后台视频管理控制器
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
		if(!$this->session->userdata('uid'))
		{
			redirect('/login');
		}
	}

	public function add()
	{
		$this->load->helper('form');
		if(!$this->input->post('videosubmit'))
		{
			$this->load->view('admin/video_form');
		}
		else
		{
			$config['upload_path'] = './uploads/';
			$config['allowed_types'] = 'gif|jpg|png';
			$config['max_size'] = '1000';
			$config['max_width']  = '1024';
			$config['max_height']  = '768';
			$this->load->library('upload', $config);
			if ( ! $this->upload->do_upload())
			{
				$error = array('error' => $this->upload->display_errors('<div class="error">','</div>'));
				$this->load->view('admin/video_form', $error);
			}
			else
			{
				$data = array('upload_data' => $this->upload->data());
					
				$this->load->view('admin/video_success', $data);
			}
		}
	}
}