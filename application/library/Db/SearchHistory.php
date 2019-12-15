<?php
class Db_SearchHistory extends Db_Abstract
{	
	protected $_name = '';
	protected $_use_adapter = 'front_db';
			
	function __construct($site) {
	   parent::__construct();
	   $this->_name = Com_Util::getHistoryTableName($site);
	}
	
	public function getRowByRangeDt($fromDt, $toDt)
	{
	    $fromDt = DateTime::createFromFormat('YmdHis', $fromDt)->format('Y/m/d H:i:s');
	    $toDt = DateTime::createFromFormat('YmdHis', $toDt)->format('Y/m/d H:i:s');
	    
	    $result = $this->fetchAll(
	               $this->select()
	                   ->where("registdt <= ? ", $toDt)
	                   ->where("registdt >= ? ", $fromDt)
	                   ->where("showtype = 0"));

	    if (empty($result)) return FALSE;
	    return $result;
	}
	
	public function updateHistory($data, $where)
	{
		try{
			$updCnt = $this->update($data, $where);
		}catch(Exception $e){
			return false;
		}
		return $updCnt;
	}
	
	
	
	/**
	* 検索履歴を取得する
	* @return unknown|boolean
	*/
	public function getListByKeyword($keyword)
	{
		$result = $this->fetchAll($this->select()->where("kword like ?", "%".$keyword."%")->order('registdt DESC'));
		if (!empty($result))
		{
			return $result;
		}
		return FALSE;
	}
	
	public function getRowByRegDt($registdt)
	{
	    $result = $this->fetchAll($this->select()->where("DATE_FORMAT(registdt, '%Y%m%d%H%i%s') = ? ", $registdt));
	    if (!empty($result))
	    {
	        return $result;
	    }
	    return FALSE;
	}
	
	/**
	 * ゲットレコード
	 * @param unknown $id
	 * @return unknown|boolean
	 */
	public function getRowById($id)
	{
	    $result = $this->fetchRow($this->select()->where('id = ?', $id));
	    if (!empty($result))
	    {
	    	return $result;
	    }
	    return FALSE;	     
	}
	
	public function getCount()
	{
 	    //$result = $this->fetchAll($this->select());
	    $sql = $this->select()->from($this, 'COUNT(*) as count');
	    $result = $this->fetchAll($sql);
	    
	    if (!empty($result))
	    {
	    	return $result[0]->count;
	    }
	    return FALSE;
	}
	
	/*---------------------------------------------------
	* Delete
	---------------------------------------------------*/
	
	public function getDeleteList($keyword)
	{
		$result = $this->fetchAll($this->select()->where("kword like ? ", "%".$keyword."%")
								->order('registdt DESC'));
		if (!empty($result))
		{
			return $result;
		}
		return FALSE;
	}

	public function getDelete($keyword)
	{
		if(!empty($keyword)){
			$result = $this->delete(array("kword like ? " => "%".$keyword."%"));
		} else {
			return FALSE;
		}
		return TRUE;
	}
}