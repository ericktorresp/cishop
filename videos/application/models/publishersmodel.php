<?php
/**
 * AV Publisher Model
 *
 * @author Floyd
 * @since 2010-11-16
 */
class PublishersModel extends Model
{
	var $table = 'publishers';

	var $id;
	var $name;
	var $nationality;

	public function __construct()
	{
		$this->PublishersModel();
	}

	public function PublishersModel()
	{
		parent::Model();
	}

	/**
	 * Add new publisher
	 *
	 * @param array $data
	 *
	 * @return boolean
	 */
	public function add($data)
	{
		return $this->db->insert($this->table, $data);
	}

	/**
	 * List All AV Publishers
	 */
	public function publishers($nationality='', $offset=0, $perpage=20)
	{
		if(empty($nationality))
		{
			return array(
			'total'=>$this->db->count_all_results($this->table),
			'data'=>$this->db->get($this->table, $perpage, $offset)->result()
			);
		}
		else
		{
			return array(
				'total'=>$this->db->where('nationality', $nationality)->count_all_results($this->table),
				'data'=>$this->db->get_where($this->table, array('nationality'=>$nationality), $perpage, $offset)->result()
			);
		}
	}

	public function publishers_for_dropdown()
	{
		$publishers = array();
		$result = $this->publishers();
		foreach($result['data'] AS $publisher)
		{
			$publishers[$publisher->id] = $publisher->name;
		}
		return $publishers;
	}

	/**
	 * Read a AV Publisher
	 *
	 * @param int $id
	 *
	 * @return array publisher
	 */
	public function publisher($id)
	{
		if(!$id)
		return FALSE;
		return $this->db->get_where($this->table, array('id'=>$id))->row();
	}

	/**
	 * Update a AV Publisher
	 *
	 * @param int $id
	 * @param array $data
	 *
	 * @return boolean
	 */
	public function update($id, $data)
	{
		if(!$id)
		{
			return false;
		}
		$this->db->where('id', $id);
		return $this->db->update($this->table, $data);
	}

	/**
	 * Delete a AV Publisher
	 *
	 * @param int $id
	 *
	 * @return boolean
	 */
	public function delete($id)
	{
		return $this->db->delete($this->table, array('id'=>$id));
	}
}