<?php
class Db_ExpandResult extends Db_Abstract
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
		$expand->status = $info['status'];
		$expand->site = $info['site'];
		$expand->interruptinfo = $info['interruptinfo'];
		
		try	{
		    $expand->save();
		    return true;
		} catch (Exception $e) {
			return false;
		}
	}

	public function registb($info)
	{
	    $expand = $this->createRow();
	
	    $expand->historyid = $info['historyid'];
	    $expand->resultb = $info['resultb'];
	    $expand->updatedt = $info['updatedt'];
	    $expand->status = $info['status'];
	    $expand->site = $info['site'];
	    $expand->interruptinfo = $info['interruptinfo'];
	    $expand->bflag = 1;
	     
	    try	{
	        $expand->save();
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
	public function getRowById($historyid, $site)
	{
	    $result = $this->fetchRow($this->select()
	               ->where('historyid = ?', $historyid)
	               ->where('site = ?', $site));
	    
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
	
	public function deleteOne($historyid, $site) {
	    $this->delete(array("historyid = ? " => $historyid,
	            "site = ? " => $site));
	    return true;
	}
	
	// ------------------ maintenance --------------------
	public function getRowsByIdRange($idfrom, $idto, $site) {
	    
	    $result = $this->fetchAll($this->select()
	            ->where('historyid >= ?', $idfrom)
	            ->where('historyid <= ?', $idto)
	            ->where('bflag = 0 and site = ?', $site));
	     
	    if (!empty($result))
	    {
	        return $result;
	    }
	    return FALSE;
	}

}