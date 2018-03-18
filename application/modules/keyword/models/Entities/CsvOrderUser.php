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
		
		try {
		    $row->save();
		    return true;
		} catch (Exception $e) {
			return false;
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
	    if (!empty($result)) {
	        return $result;
	    }
	    return false;
	}
	
	public function getCsvOrderList($usreid) {
	    
	    $result = $this->fetchAll(
	            $this->select()->setIntegrityCheck(FALSE)
	            ->from(array("c"=>"csvorderuser"), array("*"))
	            ->joinLeft(array("h"=>"searchhistory"),
	                    "c.historyid = h.id",
	                    array("registdt"=>"h.registdt", "kword"=>"h.kword"))
	            ->joinLeft(array("e"=>"expandresult"),
	                    "c.historyid = e.historyid",
	                    array("status"=>"e.status"))
               ->where("userid = ?", $usreid)
               ->order('updatedt DESC'));
	    
	    if (!empty($result)) {
	        return $result;
	    }
	    return false;	
	}
	
	public function getExecutingCsv($userid) {
	    $result = $this->fetchAll(
    	            $this->select()->setIntegrityCheck(FALSE)
    	            ->from(array("c"=>"csvorderuser"), array("historyid"))
    	            ->joinLeft(array("e"=>"expandresult"),
    	                    "c.historyid = e.historyid",
    	                    array())
    	            ->where("e.status = 0  and c.userid = ? ", $userid)
	            );
	    
	    if (!empty($result)) {
	        return $result;
	    }
	    return false;
	}
	
	public function getNotExecuteCsv($usreid) {
	     
	    $result = $this->fetchAll(
	            $this->select()->setIntegrityCheck(FALSE)
	            ->from(array("c"=>"csvorderuser"), array("c.historyid"))
	            ->joinLeft(array("e"=>"expandresult"),
	                    "c.historyid = e.historyid",
	                    array())
	            ->where("e.historyid IS NULL AND c.userid = ?", $usreid)
	            ->order('c.updatedt DESC'));
	     
	    if (!empty($result)) {
	        return $result;
	    }
	    return false;
	}
}