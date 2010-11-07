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
	var $duration;
	var $ctime;
	var $views;
	var $is_fetured;
	var $rate;
	var $server;
	var $published;
	var $mime;

	public function __construct()
	{
		$this->VideoModel();
	}

	public function VideoModel()
	{
		parent::Model();
	}

	/**
	 * 视频列表
	 * @param int $offset
	 * @param int $perpage
	 *
	 * @return array
	 */
	public function videos($cid, $offset=0, $perpage=20)
	{
		if(!$cid)
		{
			return array(
				'total'=>$this->db->count_all_results($this->table),
				'data'=>$this->db->order_by('vid','DESC')->join('categories','videos.cid=categories.cid')->get($this->table, $perpage, $offset)->result()
			);
		}
		else
		{
			return array(
				'total'=>$this->db->where('cid', $cid)->count_all_results($this->table),
				'data'=>$this->db->order_by('vid','DESC')->join('categories','videos.cid=categories.cid')->get_where($this->table, array('videos.cid'=>$cid), $perpage, $offset)->result()
			);
		}
	}

	/**
	 * 添加视频
	 * @param	array	$data
	 *
	 * @return	boolean
	 */
	public function add($data)
	{
		$this->db->trans_start();
		$this->db->insert($this->table, $data);
		$this->db->query('UPDATE categories SET count=count+1 WHERE cid='.$data['cid']);
		return $this->db->trans_complete();
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

	/**
	 * 首页观看最多的视频列表
	 * 
	 */
	public function watched()
	{
		
	}
}