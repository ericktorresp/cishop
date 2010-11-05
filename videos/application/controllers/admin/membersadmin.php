<?php
/**
 * 用户管理控制器
 * @author Floyd
 *
 */
class MembersAdmin extends Controller
{
	public function __construct()
	{
		$this->MembersAdmin();
	}

	public function MembersAdmin()
	{
		parent::Controller();
		if(!$this->session->userdata('uid') || !$this->session->userdata('is_admin'))
		{
			redirect('/login');
		}
	}
}