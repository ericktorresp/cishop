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
	var $aid;
	var $pid;

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
	public function videos($cid=0, $offset=0, $perpage=20, $aid=0, $pid=0)
	{
		$condition_total = $condition_data = array();
		if($cid != 0)
		{
			$condition_total['cid'] = $cid;
			$condition_data['videos.cid'] = $cid;
		}
		if($aid != 0)
		{
			$condition_total['aid'] = $aid;
			$condition_data['videos.aid'] = $aid;
		}
		if($pid != 0)
		{
			$condition_total['pid'] = $pid;
			$condition_data['videos.pid'] = $pid;
		}
		$total = $this->db->where($condition_total)->count_all_results($this->table);
		$data = $this->db->order_by('vid', 'DESC')->join('categories', 'videos.cid=categories.cid')->get_where($this->table, $condition_data, $perpage, $offset)->result();
//		var_dump($data);die;
		return array('total'=>$total,'data'=>$data);
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
	public function watched($t='day',$n=6)
	{
		$date = $last_date = array();
		$sql = $last_sql = 'SELECT v.*,SUM(vv.views) AS views FROM video_views vv, videos v WHERE ';
		switch($t)
		{
			case "week":
				//本周
				$sql .= 'vv.vday BETWEEN date_sub(curdate(),INTERVAL WEEKDAY(curdate()) + 1 DAY) AND date_sub(curdate(),INTERVAL WEEKDAY(curdate()) - 5 DAY)';
				//上周
				$last_sql .= 'vv.vday BETWEEN date_sub(curdate(),INTERVAL WEEKDAY(curdate()) + 8 DAY) AND date_sub(curdate(),INTERVAL WEEKDAY(curdate()) + 2 DAY)';
				break;
			case "month":
				//本月
				$sql .= 'vv.vday BETWEEN concat(date_format(LAST_DAY(now()),"%Y-%m-"),"01") AND LAST_DAY(now())';
				//上月
				$last_sql .= 'vv.vday BETWEEN concat(date_format(LAST_DAY(now() - interval 1 month),"%Y-%m-"),"01") AND LAST_DAY(now() - interval 1 month)';
				break;
			case "day":
			default:
				//今天
				$sql .= 'vv.vday="'.date('Y-m-d').'"';
				//昨天
				$last_sql .= 'vv.vday=date_sub("'.date('Y-m-d').'", interval 1 day)';
				break;
		}
		$sql .= ' AND vv.vid=v.vid GROUP BY vv.vid ORDER BY views DESC, vv.vid DESC LIMIT '.$n;
		$last_sql .= ' AND vv.vid=v.vid GROUP BY vv.vid ORDER BY views DESC, vv.vid DESC LIMIT '.$n;
		$query = $this->db->query($sql);
		if($query->num_rows() > 0)
		{
			return $query->result();
		}
		else
		{
			$query = $this->db->query($last_sql);
			return $query->result();
		}
	}
}