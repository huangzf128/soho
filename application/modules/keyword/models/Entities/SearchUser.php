<?php
class Keyword_Model_Entities_SearchUser extends Db_Abstract
{	
	protected $_name = 'user';
	protected $_use_adapter = 'front_db';
			
	/**
	 * 登録する
	 */
	public function regist($info)
	{
		$srchRst = $this->createRow();
	
		$srchRst->id = $info['id'];
		$srchRst->name = $info['name'];
		$srchRst->email = $info['email'];
		$srchRst->password = $info['password'];
		$srchRst->site = $info['site'];
		
		$srchRst->type = $info['type'];
		$srchRst->valid = $info['valid'];
		$srchRst->updatedt = $info['updatedt'];
		
		try	{
			$srchRst->save();
			return $srchRst->id;
		} catch (Exception $e) {
			return FALSE;
		}
	}
	
	public function getAllUserList()
	{	    
	    $result = $this->fetchAll($this->select()->order('updatedt DESC'));
	    if (!empty($result)) {
	    	return $result;
	    }	    
	    return FALSE;	     
	}
	
	public function getList($offset, $pnum)
	{	    
	    $result = $this->fetchAll($this->select()->order('updatetime DESC')->limit($pnum, $offset));
	    if (!empty($result)) {
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
	    $result = $this->fetchRow($this->select()->where('valid = 1 and id = ?', $id));
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
	    $where = $this->getAdapter()->quoteInto('id = ?', $id);
	    $this->delete($where);    
	}
	
	public function getCount()
	{
	    $sql = $this->select()->from($this, 'COUNT(*) as count');
	    $result = $this->fetchAll($sql);
	    
	    if (!empty($result))
	    {
	    	return $result[0]->count;
	    }
	    return FALSE;
	}

	public function updateUser($data, $where)
	{
	    try {
	        $updCnt = $this->update($data, $where);
	    } catch(Exception $e) {
	        return false;
	    }
	    return $updCnt;
	}
}