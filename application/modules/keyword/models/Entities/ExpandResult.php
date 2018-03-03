<?php
class Keyword_Model_Entities_ExpandResult extends Db_Abstract
{	
	protected $_name = 'expandresult';
	protected $_use_adapter = 'front_db';
			
	/**
	 * 検索結果を登録する
	 */
	public function regist($info)
	{
		$expand = $this->createRow();
	
		$expand->historyid = $info['historyid'];
		$expand->result = $info['result'];
		$expand->updatedt = $info['updatedt'];
		
		try
		{
		    $expand->save();
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
	
	public function updateExpand($data, $where)
	{
	    try{
	        $updCnt = $this->update($data, $where);
	    }catch(Exception $e){
	        return false;
	    }
	    return $updCnt;
	}


}