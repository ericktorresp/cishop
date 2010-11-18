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
		if(!$this->session->userdata('uid') || !$this->session->userdata('is_admin'))
		{
			redirect('/login');
		}
		$this->load->model(array('CategoriesModel', 'ServersModel', 'VideoModel', 'ActorsModel', 'PublishersModel'));
	}

	/**
	 * 添加
	 */
	public function add()
	{
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
		$data = array(
			'cats'=>$this->CategoriesModel->categories_for_dropdown(),
			'servers'=>$this->ServersModel->servers_for_dropdown(),
			'actors'=>$this->ActorsModel->actors_for_dropdown(),
			'publishers'=>$this->PublishersModel->publishers_for_dropdown()
		);
		if($this->form_validation->run() == FALSE)
		{
			$this->load->view('admin/video_add_form',$data);
			return;
		}
		else
		{
			$this->load->helper('string');
			$this->load->library('upload');

			if (!$this->upload->do_upload())
			{
				$data['error'] = $this->upload->display_errors('<div class="error">','</div>');
				$this->load->view('admin/video_add_form', $data);
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
					'published'=>$this->input->post('published'),
					'mime'=>$this->input->post('mime'),
					'duration'=>$this->input->post('duration')
				);
				//insert into videos
				if($this->VideoModel->add($data))
				{
					$this->session->set_flashdata('infomation', sprintf($this->lang->line('successed'), $this->lang->line('add')));
				}
				else
				{
					$this->session->set_flashdata('error', sprintf($this->lang->line('failed'), $this->lang->line('add')));
				}
				redirect('admin/video');
			}
		}
	}

	/**
	 * 列表
	 */
	public function index($offset=0)
	{
		$result = $this->VideoModel->videos(0, $offset, 20);
		$this->load->library('pagination');

		$config['base_url'] = "/admin/video/index/";
		$config['total_rows'] = $result['total'];
		$config['per_page'] = '20';
		$config['page_query_string'] = FALSE;
		$config['uri_segment'] = 4;

		$this->pagination->initialize($config);
		$data = array('videos'=>$result['data'], 'pagination'=>$this->pagination->create_links());
		$this->load->view('admin/video_list', $data);
	}

	public function category($cid=0, $offset=0)
	{
		if(!$cid) show_404();
		$cids = $this->CategoriesModel->categories_for_dropdown();
		if(!array_key_exists($cid,$cids))
		{
			show_404();
			return;
		}
		$result = $this->VideoModel->videos($cid, $offset, 20);
		$this->load->library('pagination');

		$config['base_url'] = "/admin/video/category/".$cid."/";
		$config['total_rows'] = $result['total'];
		$config['per_page'] = '20';
		$config['page_query_string'] = FALSE;
		$config['uri_segment'] = 5;
		$this->pagination->initialize($config);
		$data = array('videos'=>$result['data'], 'pagination'=>$this->pagination->create_links());
		$this->load->view('admin/video_list', $data);
	}

	/**
	 * 编辑
	 * @param int $vid
	 */
	public function edit($vid=0)
	{
		if(!$vid && !$this->input->post('vid'))	show_404();
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
		if($this->form_validation->run() == FALSE)
		{
			$data = array(
			'cats'=>$this->CategoriesModel->categories_for_dropdown(),
			'servers'=>$this->ServersModel->servers_for_dropdown(),
			'actors'=>$this->ActorsModel->actors_for_dropdown(),
			'publishers'=>$this->PublishersModel->publishers_for_dropdown()
			);
			$data['video'] = $this->VideoModel->video($vid);
			$this->load->view('admin/video_edit_form',$data);
			return;
		}
		$vid = $this->input->post('vid');
		$data = array(
			'cid'=>$this->input->post('cid'),
			'title'=>$this->input->post('title'),
			'description'=>$this->input->post['description'],
			'width'=>$this->input->post('width'),
			'height'=>$this->input->post('height'),
			'is_fetured'=>$this->input->post('is_fetured')?$this->input->post('is_fetured'):0,
			'rate'=>$this->input->post('rate'),
			'server'=>$this->input->post('server'),
			'published'=>$this->input->post('published'),
			'mime'=>$this->input->post('mime'),
			'duration'=>$this->input->post('duration'),
			'aid'=>$this->input->post('aid'),
			'pid'=>$this->input->post('pid')
		);
		if($this->VideoModel->update($vid, '', $data))
		{
			$this->session->set_flashdata('infomation', sprintf($this->lang->line('successed'),$this->lang->line('edit')));
		}
		else
		{
			$this->session->set_flashdata('error', sprintf($this->lang->line('failed'),$this->lang->line('edit')));
		}
		redirect('/admin/video');
	}

	public function delete($vid)
	{
		if($this->VideoModel->delete($vid))
		{
			$this->session->set_flashdata('infomation', sprintf($this->lang->line('successed'),$this->lang->line('delete')));
		}
		else
		{
			$this->session->set_flashdata('error', sprintf($this->lang->line('failed'),$this->lang->line('delete')));
		}
		redirect('admin/video');
	}
}