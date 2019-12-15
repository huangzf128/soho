<?php
class Keyword_Model_Entities_SearchHistory extends Db_Abstract
{	
	protected $_name = 'searchhistory';
	protected $_use_adapter = 'front_db';
			
	
	/**
	 * 検索結果を登録する
	 */
	public function registbWithId($info)
	{
	    $srchRst = $this->createRow();
	
	    $srchRst->id = $info[0];
	    $srchRst->registdt = $info[1];
	    $srchRst->kword = $info[2];
	    $srchRst->rstcnt = $info[3];
 	    $srchRst->indextabb = $info[4];
 	    $srchRst->skb = $info[5];
	    $srchRst->clientip = $info[6];
	    $srchRst->bflag = 1;
	     
	    try	{
	        $srchRst->save();
	    } catch (Exception $e) {
	        echo $e->getMessage();
	    }
	}
	
	/**
	 * 検索結果を登録する
	 */
	public function registb($info)
	{
	    $srchRst = $this->createRow();
	
	    $srchRst->registdt = $info['registdt'];
	    $srchRst->kword = $info['kword'];
	    $srchRst->rstcnt = $info['rstcnt'];
	    $srchRst->clientip = $info['clientip'];
	    $srchRst->indextabb = $info['indextabb'];
	    $srchRst->skb = $info['skb'];
	    $srchRst->bflag = 1;
	
	    try	{
	        $srchRst->save();
	        return $srchRst->id;
	    } catch (Exception $e) {
	        throw $e;
	    }
	}
	
	/**
	 * 検索結果を登録する
	 */
	public function updateb($info, $where)
	{
	    try	{
	        $this->update($info, $where);
	    } catch (Exception $e) {
	        throw $e;
	    }
	}
	
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
	
	public function getRowByRegDt($registdt)
	{
	    $result = $this->fetchAll($this->select()->where("DATE_FORMAT(registdt, '%Y%m%d%H%i%s') = ? ", $registdt));
	    if (!empty($result))
	    {
	        return $result;
	    }
	    return FALSE;
	}

	public function getRecentKeyword($keyword) {
	    $result = $this->fetchAll($this->select()->where("registdt > date_sub(now(), INTERVAL 30 minute) and kword = ?", $keyword));
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

	// ====================================== mentance
	
	public function getDuplicateCount($keyword, $id) {

	    $sql = $this->select()->from($this, 'COUNT(*) as count')
	                               ->where("id > ?", $id)
	                               ->where("id < ?", $id + 50)
	                               ->where("kword = ?", $keyword);
	    
	    $result = $this->fetchAll($sql);
	     
	    if (!empty($result))
	    {
	        return $result[0]->count;
	    }
	    return FALSE;
	}
	
	public function getRowByIdRange($idFrom, $idTo) {
	    $sql = $this->select()->where("id >= ?", $idFrom)->where("id <= ?", $idTo)->order("id");
	    $result = $this->fetchAll($sql);
	    if (!empty($result))
	    {
	        return $result;
	    }
	    return FALSE;
	}
	
	public function getZeroRowByRegDtRange($registdt)
	{
	    $result = $this->fetchAll($this->select()->where("DATE_FORMAT(registdt, '%Y%m%d%H%i%s') < ? and rstcnt = 0", $registdt));
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
	public function getListForBflag()
	{
	    $result = $this->fetchAll($this->select()->where("id = 1815001")->where("bflag = 0")->order('id asc'));
	    if (!empty($result))
	    {
	        return $result;
	    }
	    return FALSE;
	}
}