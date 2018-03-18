<?php
class Keyword_Model_Entities_Log extends Db_Abstract
{	
	protected $_name = 'log';
	protected $_use_adapter = 'front_db';
			
	/**
	 * Logを登録する
	 */
	public function regist($data)
	{
		$row = $this->createRow();
	
		$row->registdt = $data['registdt'];
		$row->type = $data['type'];
		$row->name = $data['name'];
		$row->message = $data['message'];
		$row->biko1 = $data['biko1'];
		$row->biko2 = $data['biko2'];
		$row->biko3 = $data['biko3'];
		
		try {
		    $row->save();
		    return true;
		} catch (Exception $e) {
			return false;
		}
	}
}