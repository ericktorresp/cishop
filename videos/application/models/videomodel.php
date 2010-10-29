<?php
class VideoModel extends Model
{
	var $table = 'videos';
	
	var $vid;
	var $title;
	var $key;
	var $descript;
	var $file_name;
	var $width;
	var $height;
	var $publish_time;
	var $views;
	var $is_fetured;
	var $rate;
	
	public function __construct()
	{
		$this->VideoModel();
	}
	
	public function VideoModel()
	{
		parent::Model();
	}
	
	public function video($vid=0, $key='')
	{
		if(!$vid && !$key)	return FALSE;
		if($vid)
		{
			return $this->db->get_where($this->table, array('vid'=>$vid))->row();
		}
		elseif($key)
		{
			return $this->db->get_where($this->table, array('key'=>$key))->row();
		}
	}

	public function update_views($vid=0, $key='')
	{
		if(!$vid && !$key) return FALSE;
		if($vid)
		{
			return $this->db->update($this->table,array('views'=>'views+1'), array('vid'=>$vid));
		}
		elseif($key)
		{
			return $this->db->update($this->table, array('views'=>'views+1'), array('key'=>$key));
		}
	}

}