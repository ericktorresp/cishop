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
	
	public function add()
	{
		
	}

	public function server()
	{
		
	}
	
	public function update()
	{
		
	}
	
	public function delete()
	{
		
	}
}