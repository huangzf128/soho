<?php
require_once 'Zend/Http/Client.php';

class Keyword_KeywordController extends Zend_Controller_Action
{
	private $accessTimeLimit = 60;
	private $accessCountLimit = 4;
	
    public function init(){ }

    public function indexAction() { }
    
    /**
     * 検索ボタン押下イベント
     */
    public function getSuggestKeywordAction()
    {   
    	
        $keyword = mb_convert_encoding($this->getRequest()->keyword, "utf-8","auto");
        $model = new Keyword_Model_SuggestKeyword($keyword);
        
        if(!$this->accessLimitCheck()){
        	$this->_helper->layout->assign('keyword', $keyword);
        	$this->_helper->layout->assign('rstCnt', $model->getRstCnt());
        	$this->view->sk = "<font color='red'>アクセス制限のため、しばらく経ってから、検索してください。</font><br/><br/><br/>";
        	$this->view->indextab = $model->getIndexTab();
        	$this->_helper->layout->setLayout('layout');
        	return ;
        }
        

		// keyword check, block check
		$checkResult = $model->checkKeyword();
       	if(1 === $checkResult) {
            $this->_helper->layout->assign('keyword', $keyword);
	        $this->_helper->layout->assign('rstCnt', $model->getRstCnt());
        	$this->view->sk = "<font color='red'>入力するキーワードは、25文字以内にしてください。</font><br/><br/><br/>";
	        $this->view->indextab = $model->getIndexTab();
        	$this->_helper->layout->setLayout('layout');
        	return;
        } else if(0 === $checkResult) {
        	$this->_response->setRedirect('/index')->sendResponse();
        	return;
        }
		        
        if($model->isBlockKeyword()){

	        $this->_helper->layout->assign('keyword', $keyword);
	        $this->_helper->layout->assign('rstCnt', $model->getRstCnt());
	        $this->view->sk = "<font color='red'>入力されたキーワードは、当ツールでは対象外です。</font><br/><br/><br/>";
	        $this->view->indextab = $model->getIndexTab();
        	$this->_helper->layout->setLayout('layout');
	        
        	return;
		}

		
		// 検索
		$model->getSuggestKeyword();
                        
        $registFlg = $this->getRequest()->registflg;
        
        $this->_helper->layout->assign('keyword', $keyword);
        $this->_helper->layout->assign('rstCnt', $model->getRstCnt());
        $this->_helper->layout->assign('isValidIp', $model->isValidIp());
        $this->view->sk = $model->getStrSuggestKeywords();
        $this->view->indextab = $model->getIndexTab();

        //検索情報をDB登録
        if(isset($registFlg) && $registFlg == 1){
            
        	$model->setRegistdt(date('Y-m-d H:i:s'));
        	$model->registSearchHistoryResult();
        	
        	$helper = $this->_helper->getHelper('Layout');
        	$layout = $helper->getLayoutInstance();
        	
        	$this->_helper->layout->setLayout('historyd');
            $layout = $this->_helper->layout->getLayoutInstance();        
            $layout->assign('content', $this->view->render("keyword/get-suggest-keyword.phtml"));
            $layout->assign('keyword', $keyword);
            $layout->assign('date', $model->getRegistdt());
            $layout->assign('rstCnt', $model->getRstCnt());
            
            $html = $layout->render(); 
            $model->saveResult($html);
        }
              
        $this->_helper->layout->setLayout('layout');
        
    }
    
    
    /**
     * Ajax検索
     */
    public function getSuggestKeywordAjaxAction()
    {        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        //keywordを取得する
        $keyword = $this->getRequest()->keyword;
        
        $model = new Keyword_Model_SuggestKeyword($keyword);
        $model->getSuggestKeywordAjax();
                
        echo $model->getStrSuggestKeywords();
    }
    
    /**
     * CSVファイルダウンロード
     */
    public function getCsvFileAction()
    {        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $csvdata = $this->getRequest()->csvdata;
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="keyword.csv"');
        echo mb_convert_encoding ( $csvdata, "sjis", "utf-8");
    } 
    
    
    /**
     * 検索履歴リストを取得する
     */
    public function getSearchHistoryListAction()
    {
        $this->_helper->layout->setLayout('history');
        
        $currentNo = isset($this->getRequest()->currentNo) ? $this->getRequest()->currentNo : 1;        
        $model = new Keyword_Model_SuggestKeyword(null);
        $result = $model->getSearchHistoryList($currentNo);
//      self::errOutput("error", $e->getMessage());
        
        if (isset($result) && count($result) > 0){
            $pageNo = $model->getSearchHistoryListPageNo($currentNo);
            if ($currentNo > 1){
                $this->_helper->layout->assign('page', $currentNo."ページ目");
                $this->_helper->layout->assign('currentNo', $currentNo);
            } else {
                $this->_helper->layout->assign('page', "ページ");
                $this->_helper->layout->assign('currentNo', "");
            }
        }

        $this->view->pageno = $pageNo;
        $this->view->rlist = $result;
    }
    
    
    /**
     * キーワードの検索履歴を取得する(DB)
     */
    public function getSearchHistoryDetailAction()
    {
        $this->_helper->layout->setLayout('historyd');
        
        $id = $this->getRequest()->id;
        
        if(isset($id)) {
        
        	$model = new Keyword_Model_SuggestKeyword(null);
        	$result = $model->getSearchHistoryDetail($id);
    
        	$this->_helper->layout->assign('keyword', $result['kword']);
        	$this->_helper->layout->assign('date', $result['registdt']);
        	
        	$this->_helper->layout->assign('rstCnt', $result['rstcnt']);
        	$this->view->sk = $result['result'];
        	$this->view->indextab = $result['indextab'];
        }
        
        $this->render();
    }

    
    /**
     * キーワードの検索履歴を取得する(物理ファイル)
     */
    public function getSearchHistoryDetailFileAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $fileName = $this->getRequest()->fileName;
        $model = new Keyword_Model_SuggestKeyword(null);
        
        $file = $model->getResultFile($fileName);
        if($file === false) {
        	//throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        	
        	$helper = $this->_helper->getHelper('Layout');
        	$layout = $helper->getLayoutInstance();
        	 
        	$layout->assign('content', $this->view->render("error/error.phtml"));
        	$errHtml = $layout->render();
        	echo $errHtml;
        } else {
            
            $script = "";
            if($model->isValidIp()) {
                $script = "<script>$('.csvprime').show();</script>";
            }            
        	echo $file.$script;
        }        
    }    
    
    static function errOutput($errSql_file, $sql){
    
    	try{
    		$fp = fopen("log/".$errSql_file, 'a+');
    
    		if ($fp){
    			if (flock($fp, LOCK_EX)){
    				if (fwrite($fp,  $sql."\r\n") === FALSE){
    					new Exception('ファイル書き込みに失敗しました'); 
    				}
    				flock($fp, LOCK_UN);
    			}
    		}
    		fclose($fp);
    	}catch(Exception $e){
    		new Exception($e->getMessage());
    	}
    }
    
    private function accessLimitCheck(){
    	    	
    	if($this->accessTimeLimit == 0){
    		return true;
    	}
    	
    	$rst = false;
    	
	try{
		
    	$namespace = new Zend_Session_Namespace();    	
    	$current = strtotime(date("YmdHis")); 

	 } catch (Exception $e) {
			self::errOutput("error.log", $e->getMessage());
     }
    	

    	if(isset($namespace->lastAccess)){    		

    		if($current - $namespace->lastAccess > $this->accessTimeLimit){
    			$namespace->lastAccess = $current;
    			$namespace->accessCount = $this->accessCountLimit;
    			$rst = true;;
    		} else {
				if(--$namespace->accessCount > 0){
					$rst = true;
				} else {
					$rst = false;
				}    			
    		}
    	} else {
    		// はじめ
    		$namespace->lastAccess = $current;
    		$namespace->accessCount = $this->accessCountLimit; 
    		$rst = true;
    	}
    	
    	Zend_Session::writeClose();
    	return $rst;
    }
}
