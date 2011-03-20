<?php
class Country_Model extends CI_Model {
	private $iso = '';
	private $name = '';
	private $printable_name = '';
	private $iso3 = '';
	private $numcode = '';
	
	public function getAll() {
		return $this->db->get('country')->result();
	}
	
	public function getByIso($iso) {
		return $this->db->get_where('country', array('iso'=>$iso))->row();
	}
}