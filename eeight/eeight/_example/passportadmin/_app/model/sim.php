<?php

class model_sim extends basemodel
{
	public function simlist()
	{
		$sSql = 'SELECT * FROM sim WHERE 1=1';
		return $this->oDB->getALL($sSql);
	}
	public function add() 
	{
	
	}
	
	public function edit()
	{
	
	}
	
	public function disable()
	{
	
	}
	
	public function delete()
	{
	
	}

}

?>