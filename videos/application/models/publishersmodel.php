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
	public function publishers()
	{
		return $this->db->get($this->table)->result();
	}
	
	public function publishers_for_dropdown()
	{
		$publishers = array();
		foreach($this->publishers() AS $publisher)
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