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
		$this->lang->load('video');
	}

	public function add()
	{
		$this->load->helper('form');
		$this->load->model(array('CategoriesModel', 'ServersModel', 'VideoModel'));
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
		$data = array(
			'cats'=>$this->CategoriesModel->categories_for_dropdown(),
			'servers'=>$this->ServersModel->servers_for_dropdown()
		);
		if($this->form_validation->run() == FALSE)
		{
			$this->load->view('admin/video_form',$data);
			return;
		}
		else
		{
			$this->load->helper('string');
			$this->load->library('upload');

			if (!$this->upload->do_upload())
			{
				$data['error'] = $this->upload->display_errors('<div class="error">','</div>');
				$this->load->view('admin/video_form', $data);
			}
			else
			{
				$image_data =$this->upload->data();
				$data = array(
					'cid'=>$this->input->post('cid'),
					'title'=>$this->input->post('title'),
					'key'=>array_shift(explode('.',$image_data['file_name'])),
					'description'=>$this->input->post['description'],
					'file_name'=>'',
					'width'=>$this->input->post('width'),
					'height'=>$this->input->post('height'),
					'ctime'=>time(),
					'views'=>$this->input->post('views') ? $this->input->post('views') : 0,
					'is_fetured'=>$this->input->post('is_fetured')?$this->input->post('is_fetured'):0,
					'rate'=>0,
					'server'=>$this->input->post('server'),
					'published'=>$this->input->post('published')
				);
				//insert into videos
				$this->VideoModel->add($data);
				redirect('/admin/video');
			}
		}
	}

	public function index()
	{
		echo 'video list.';
	}
}