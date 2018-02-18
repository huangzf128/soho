<?php
class Keyword_Model_Entities_SearchUser extends Db_Abstract
{	
	protected $_name = 'user';
	protected $_use_adapter = 'front_db';
			
	/**
	 * 検索結果を登録する
	 */
	public function regist($info)
	{
		$srchRst = $this->createRow();
	
		$srchRst->id = $info['id'];
		$srchRst->name = $info['username'];
		$srchRst->email = $info['email'];
		$srchRst->password = $info['password'];
		
		$srchRst->type = $info['type'];
		$srchRst->updatetime = $info['updatetime'];
		$srchRst->deleteflag = $info['deleteflag'];
		
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
	    $result = $this->fetchAll($this->select()->order('updatetime DESC')->limit($pnum, $offset));
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
	    $result = $this->fetchRow($this->select()->where('deleteflag = 0 and valid = 0 and id = ?', $id));
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