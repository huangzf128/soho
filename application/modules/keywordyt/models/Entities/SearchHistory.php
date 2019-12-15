<?php
class Keywordyt_Model_Entities_SearchHistory extends Db_Abstract
{	
	protected $_name = 'searchhistoryyt';
	protected $_use_adapter = 'front_db';
			
	/**
	 * 検索結果を登録する
	 */
	public function regist($info)
	{
		$srchRst = $this->createRow();
	
		$srchRst->registdt = $info['registdt'];
		$srchRst->kword = $info['kword'];
		$srchRst->rstcnt = $info['rstcnt'];
		$srchRst->clientip = $info['clientip'];
		$srchRst->indextab = $info['indextab'];
		$srchRst->sk = $info['sk'];
		
		try	{
			$srchRst->save();
			return $srchRst->id;
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	/**
	 * 検索履歴を取得する
	 * @return unknown|boolean
	 */
	public function getList($offset, $pnum)
	{	    
// 	    $result = $this->fetchAll($this->select()->order('registdt DESC')->limit($pnum, $offset));
	    $db =Zend_Db_Table_Abstract::getDefaultAdapter();
	     
	    $sql = "SELECT  t.*
                FROM    (
                        SELECT id
                        FROM  ".$this->_name."
                        ORDER BY id desc
                        LIMIT ".$offset.", ".$pnum.
	                            ") q
                INNER JOIN    ".$this->_name." t
                ON      t.id = q.id";
	     
	    $result = $db->query($sql);
	    
	    if (!empty($result))
	    {
	    	return $result;
	    }	    
	    return FALSE;	     
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
	
	/**
	* 検索履歴を取得する
	* @return unknown|boolean
	*/
	public function getKeywordById($id)
	{
		$result = $this->fetchAll($this->select()->where("id = ? ", $id));
		if (!empty($result))
		{
			return $result;
		}
		return FALSE;
	}
	
	// 2014/05/18 ADD
	public function getListReverse($offset, $pnum)
	{
		$result = $this->fetchAll($this->select()->order('registdt ASC')->limit($pnum, $offset));
		if (!empty($result))
		{
			return $result;
		}
		return FALSE;
	}
	// 2014/05/18 ADD
	
	public function getRowByRegDt($registdt)
	{
// 	    $result = $this->fetchAll($this->select()->where("DATE_FORMAT(registdt, '%Y%m%d%H%i%s') = ? ", $registdt));
	    $registdt = DateTime::createFromFormat('YmdHis', $registdt)->format('Y/m/d H:i:s');
	    $result = $this->fetchAll($this->select()->where("registdt = ? ", $registdt));
	     
	    if (!empty($result))
	    {
	        return $result;
	    }
	    return FALSE;
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