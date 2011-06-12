<?php

class model_sim extends basemodel
{
	public function simlist()
	{
		$sSql = 'SELECT * FROM sim WHERE 1=1';
		return $this->oDB->getALL($sSql);
	}
	
	public function read($id)
	{
		if(!$id) return FALSE;
		$sSql = 'SELECT * FROM sim WHERE id = ' . $id;
		return $this->oDB->getOne($sSql);
	}
	
	public function getByNumber($number)
	{
		if(!$number) return FALSE;
		if(strlen($number) != 11) return FALSE;
		return $this->oDB->getOne('select * from sim where number="'.$number.'"');
	}
	
	public function add($aData)
	{
		if(!is_array($aData))	return FALSE;
		//@todo verify every field
		return $this->oDB->insert('sim', $aData);
	}
	
	public function update($id, $aData)
	{
	
	}
	
	public function disable($id)
	{
		if(!$id)	return FALSE;
		$sSql = 'UPDATE sim SET enabled=0 WHERE id='.$id;
		return $this->oDB->query($sSql);
	}

	public function enable($id)
	{
		if(!$id)	return FALSE;
		$sSql = 'UPDATE sim SET enabled=1 WHERE id='.$id;
		return $this->oDB->query($sSql);
	}
	
	public function delete($id)
	{
	
	}

}

?>