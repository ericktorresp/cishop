<?php
/**
 * AV Actors Model
 *
 * @author Floyd
 * @since 2010-11-16
 */
class ActorsModel extends Model
{
	var $table = 'actors';

	var $id;
	var $name;
	var $gender;
	var $photo;
	var $nationality;

	public function __construct()
	{
		$this->ActorsModel();
	}

	public function ActorsModel()
	{
		parent::Model();
	}

	/**
	 * Add new Actor
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
	 * List Av Actors
	 *
	 * @return array dataset
	 */
	public function actors($gender='', $offset=0, $perpage=20)
	{
		if(!in_array($gender,array('female','male','unknown')))
		{
			return array(
			'total'=>$this->db->count_all_results($this->table),
			'data'=>$this->db->get($this->table, $perpage, $offset)->result()
			);
		}
		else
		{
			return array(
				'total'=>$this->db->where('gender', $gender)->count_all_results($this->table),
				'data'=>$this->db->get_where($this->table, array('gender'=>$gender), $perpage, $offset)->result()
			);
		}
	}

	/**
	 * Read a Actor
	 *
	 * @param int $id
	 *
	 * @return Actor Object datarow
	 */
	public function actor($id)
	{
		if(!$id)
		return FALSE;
		return $this->db->get_where($this->table, array('id'=>$id))->row();
	}

	/**
	 * Actor List for HTML dropdown
	 *
	 * @return boolean
	 */
	public function actors_for_dropdown()
	{
		$as = $this->actors();
		$actors = array();
		foreach($as['data'] AS $actor)
		{
			$actors[$actor->id] = $actor->name;
		}
		return $actors;
	}

	/**
	 * Update a Actor
	 *
	 * @param int	$id
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
	 * Delete a Actor
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