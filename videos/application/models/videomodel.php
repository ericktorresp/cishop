<?php
class VideoModel extends Model
{
	var $table = 'videos';

	var $vid;
	var $cid;
	var $title;
	var $key;
	var $description;
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

	/**
	 * 添加视频
	 * @param	array	$data
	 *
	 * @return	boolean
	 */
	public function add($data)
	{
		return $this->db->insert($this->table, $data);
	}

	/**
	 * 读取视频
	 * @param	int		$vid
	 * @param	string	$key
	 *
	 * @return	mixed
	 */
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

	/**
	 * 更新视频
	 * @param	int		$vid
	 * @param	string	$key
	 * @param	array	$data
	 */
	public function update($vid=0, $key='', $data)
	{
		if(!$vid && !$key) return FALSE;
		if($vid)
		{
			return $this->db->update($this->table, $data, array('vid'=>$vid));
		}
		elseif($key)
		{
			return $this->db->update($this->table, $data, array('key'=>$key));
		}
	}

	/**
	 * 删除视频
	 * @param	int		$vid
	 * @param	string	$key
	 *
	 * @return boolean
	 */
	public function delete($vid=0, $key='')
	{
		if($vid)
		{
			return $this->db->delete($this->table, array('vid'=>$vid));
		}
		elseif($key)
		{
			return $this->db->delete($this->table, array('key'=>$key));
		}
		return FALSE;
	}
}