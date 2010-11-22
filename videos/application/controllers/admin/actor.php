<?php
/**
 * 后台演员管理控制器
 * @author Floyd
 *
 */
class Actor extends Controller
{
	public function __construct()
	{
		$this->Actor();
	}

	public function Actor()
	{
		parent::Controller();
		if(!$this->session->userdata('uid') || !$this->session->userdata('is_admin'))
		{
			redirect('/login');
		}
		$this->load->model('ActorsModel');
	}

	public function index($offset=0)
	{
		$result = $this->ActorsModel->actors(0, $offset, 20);
		$this->load->library('pagination');

		$config['base_url'] = "/admin/actor/index/";
		$config['total_rows'] = $result['total'];
		$config['per_page'] = '20';
		$config['page_query_string'] = FALSE;
		$config['uri_segment'] = 4;

		$this->pagination->initialize($config);
		$data = array('actors'=>$result['data'], 'pagination'=>$this->pagination->create_links());
		$this->load->view('admin/actor_list', $data);
	}

	public function add()
	{
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
		if($this->form_validation->run() == FALSE)
		{
			$this->load->view('admin/actor_add_form',$data);
			return;
		}
		$this->load->helper('string');
		$config['upload_path'] = './uploads/actors/';
		$config['allowed_types'] = 'gif|jpg|png';
		$config['max_size'] = '1000';
		$config['overwrite'] = TRUE;
		$config['file_name'] = random_string('alnum',12).'.'.end(explode('.',$_FILES['photo']['name']));

		$this->load->library('upload',$config);
		if (!$this->upload->do_upload('photo'))
		{
			$data['error'] = $this->upload->display_errors('<div class="error">','</div>');
			$this->load->view('admin/actor_add_form', $data);
			return;
		}
		$image_data =$this->upload->data();
		//图片处理
		$config['image_library'] = 'gd2';
		$config['source_image'] = $image_data['full_path'];
		$config['create_thumb'] = FALSE;
		$config['maintain_ratio'] = TRUE;
		$config['width'] = 150;
		$config['height'] = 150;

		$this->load->library('image_lib', $config);
		$this->image_lib->resize();
		$data = array(
			'name'=>$this->input->post('name'),
			'gender'=>$this->input->post('gender'),
			'nationality'=>$this->input->post('nationality'),
			'photo'=>$image_data['file_name']
		);
		//insert into actors
		if($this->ActorsModel->add($data))
		{
			$this->session->set_flashdata('infomation', sprintf(lang('successed'),lang('add')));
		}
		else
		{
			$this->session->set_flashdata('error', sprintf(lang('failed'),lang('add')));
		}
		redirect('/admin/actor');
	}

	public function edit($id=0)
	{
		if(!$id && !$this->input->post('id'))	show_404();
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
		if($this->form_validation->run() == FALSE)
		{
			$data['actor'] = $this->ActorsModel->actor($id);
			$this->load->view('admin/actor_edit_form',$data);
			return;
		}
		$id = $this->input->post('id');

		if($_FILES['photo']['size'])
		{
			$config['upload_path'] = './uploads/actors/';
			$config['allowed_types'] = 'gif|jpg|png';
			$config['max_size'] = '1000';
			$config['overwrite'] = TRUE;
			$config['file_name'] = $id.'.'.end(explode('.',$_FILES['photo']['name']));
			$this->load->library('upload',$config);
			if (!$this->upload->do_upload('photo'))
			{
				$data['error'] = $this->upload->display_errors('<div class="error">','</div>');
				$this->load->view('admin/actor_edit_form', $data);
				return;
			}
			$image_data =$this->upload->data();

			//图片处理
			$config['image_library'] = 'gd2';
			$config['source_image'] = $image_data['full_path'];
			$config['create_thumb'] = FALSE;
			$config['maintain_ratio'] = TRUE;
			$config['width'] = 150;
			$config['height'] = 150;

			$this->load->library('image_lib', $config);
			$this->image_lib->resize();

			$data = array(
			'name'=>$this->input->post('name'),
			'gender'=>$this->input->post('gender'),
			'nationality'=>$this->input->post('nationality'),
			'photo'=>$image_data['file_name']
			);
		}
		else
		{
			$data = array(
			'name'=>$this->input->post('name'),
			'gender'=>$this->input->post('gender'),
			'nationality'=>$this->input->post('nationality'),
			);
		}
		//insert into videos
		if($this->ActorsModel->update($id, $data))
		{
			$this->session->set_flashdata('infomation', sprintf(lang('successed'),lang('edit')));
		}
		else
		{
			$this->session->set_flashdata('error', sprintf(lang('failed'),lang('edit')));
		}
		redirect('/admin/actor');
	}

	public function delete($id)
	{
		if($this->ActorsModel->delete($id))
		{
			$this->session->set_flashdata('infomation', sprintf(lang('successed'),lang('delete')));
		}
		else
		{
			$this->session->set_flashdata('error', sprintf(lang('failed'),lang('delete')));
		}
		redirect('admin/actor');
	}

	public function videos($aid=0, $offset=0)
	{
		if(!$aid)	redirect('admin/video');
		$this->load->model('VideoModel');
		$result = $this->VideoModel->videos(0, $offset, 20, $aid);
		$this->load->library('pagination');

		$config['base_url'] = "/admin/actor/videos/".$aid.'/';
		$config['total_rows'] = $result['total'];
		$config['per_page'] = '20';
		$config['page_query_string'] = FALSE;
		$config['uri_segment'] = 5;

		$this->pagination->initialize($config);
		$data = array('videos'=>$result['data'], 'pagination'=>$this->pagination->create_links());
		$this->load->view('admin/video_list', $data);
	}
}