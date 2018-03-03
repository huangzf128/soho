<?php
class Keyword_Model_Entities_CsvOrderUser extends Db_Abstract
{	
	protected $_name = 'csvorderuser';
	protected $_use_adapter = 'front_db';
			
	/**
	 * 予約を登録する
	 */
	public function regist($data)
	{
		$row = $this->createRow();
	
		$row->historyid = $data['historyid'];
		$row->userid = $data['userid'];
		$row->updatedt = $data['updatedt'];
		
		try
		{
		    $row->save();
		    return true;
		}
		catch (Exception $e)
		{
			return FALSE;
		}
	}
	
	/**
	 * ゲットレコード
	 * @param unknown $historyid
	 * @return unknown|boolean
	 */
	public function getRowById($historyid)
	{
	    $result = $this->fetchRow($this->select()->where('historyid = ?', $historyid));
	    if (!empty($result))
	    {
	        return $result;
	    }
	    return FALSE;
	}
	
	public function getCsvOrderList($usreid) {
	    
	    $result = $this->fetchAll($this->select()
	               ->where("userid = ?", $usreid)
	               ->order('updatedt DESC'));
	    if (!empty($result))
	    {
	        return $result;
	    }
	    return FALSE;	
	}
	
}