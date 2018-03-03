<?php
class Keyword_Model_Entities_SearchHistory extends Db_Abstract
{	
	protected $_name = 'searchhistory';
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
		
		try
		{
			$srchRst->save();
			return $srchRst->id;
		}
		catch (Exception $e)
		{
			return FALSE;
		}
	}
	
	/**
	 * 検索履歴を取得する
	 * @return unknown|boolean
	 */
	public function getList($offset, $pnum)
	{	    
	    $result = $this->fetchAll($this->select()->order('registdt DESC')->limit($pnum, $offset));
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
	
	/**
	 * ゲットレコード
	 * @param unknown $id
	 * @return unknown|boolean
	 */
	public function deleteRowById($id)
	{
	    $result = $this->fetchRow($this->delete('id = '.$id ));
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

	
}