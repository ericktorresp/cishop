<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {
	public function index()
	{
		$this->load->helper(array('captcha', 'form'));
		$vals = array(
			'img_path' => './captcha/',
		    'img_url' => '/captcha/'
	    );
	    $data['cap'] = create_captcha($vals);
	    $cdata = array(
	    'captcha_time' => $data['cap']['time'],
	    'ip_address' => $this->input->ip_address(),
	    'word' => $data['cap']['word']
	    );

	    $query = $this->db->insert_string('captcha', $cdata);
	    $this->db->query($query);
	    //		$this->load->model('user/User_Model', '', TRUE);
	    //		$data['users'] = $this->User_Model->getUserList();
	    $this->load->view('index', $data);
	}

	public function login()
	{
		// First, delete old captchas
		$expiration = time()-7200; // Two hour limit
		$this->db->query("DELETE FROM captcha WHERE captcha_time < ".$expiration);

		// Then see if a captcha exists:
		$sql = "SELECT COUNT(*) AS count FROM captcha WHERE word = ? AND ip_address = ? AND captcha_time > ?";
		$binds = array($this->input->post('captcha'), $this->input->ip_address(), $expiration);
		$query = $this->db->query($sql, $binds);
		$row = $query->row();

		if ($row->count == 0)
		{
			echo "You must submit the word that appears in the image";
		}
	}
}
