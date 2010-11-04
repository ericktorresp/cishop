<?php
class ServersModel extends Model
{
	var $table = 'servers';
	
	var $sid;
	var $domain;
	var $ip;
	var $actived;
	
	public function __construct()
	{
		$this->ServersModel();
	}
	
	public function ServersModel()
	{
		parent::Model();
	}

	public function servers()
	{
		return $this->db->get($this->table)->result();
	}
	
	public function servers_for_dropdown()
	{
		$servers = array();
		foreach($this->servers() AS $server)
		{
			if($server->actived)
			{
				$servers[$server->domain] = $server->domain;
			}
		}
		return $servers;
	}
	
	public function add($data)
	{
		return $this->db->insert($this->table, $data);
	}

	public function server($sid)
	{
		if(!$sid)	return FALSE;
		return $this->db->get_where($this->table, array('sid'=>$sid))->row();
	}
	
	public function edit($sid, $data)
	{
		if(!$sid)
		{
			return false;
		}
		$this->db->where('sid', $sid);
		return $this->db->update($this->table, $data);
	}
	
	public function delete($sid)
	{
		return $this->db->delete($this->table, array('sid'=>$sid));
	}
}