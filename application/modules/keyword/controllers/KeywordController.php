<?php
require_once 'Zend/Http/Client.php';

class Keyword_KeywordController extends Zend_Controller_Action
{
	private $accessTimeLimit = 60;
	private $accessCountLimit = 4;
	
    public function init()
    { 
        $zend_session = new Zend_Session_Namespace("auth");
        if (isset($zend_session->userid)) {
            $this->_helper->layout->assign('usertype', $zend_session->type);
            $this->_helper->layout->assign('username', $zend_session->username);
        }
        
        $model = new Keyword_Model_SuggestKeyword(null);
        $this->_helper->layout->assign('isValidIp', $model->isValidIp());  // IPユーザー
    }

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
                        
        $this->_helper->layout->setLayout('historyd');
        $layout = $this->_helper->layout->getLayoutInstance();
        
        $layout->assign('keyword', $keyword);
        $layout->assign('rstCnt', $model->getRstCnt());
        $this->view->sk = $model->getStrSuggestKeywords();  // 検索結果
        $this->view->indextab = $model->getIndexTab();      // 索引
        
        $registFlg = $this->getRequest()->registflg;
        //検索情報をDB登録
        if(isset($registFlg) && $registFlg == 1){
            
        	$model->setRegistdt(date('Y-m-d H:i:s'));
        	
        	$this->view->historyid = $model->registSearchHistoryResult();
        	
            //$layout->assign('content', $this->view->render("keyword/get-suggest-keyword.phtml"));
            //$html = $layout->render(); 
            //$model->saveResult($html);
        }
              
        //$this->_helper->layout->setLayout('layout');
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
    
    public function getCsvFileOrderAction() 
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        // 予約に入れる
        $historyid = $this->getRequest()->historyid;
        $model = new Keyword_Model_SuggestKeyword();
        $model->csvOrder($historyid, $userid);
        
        echo "予約できました。";
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
     * キーワードの検索履歴を取得する(物理ファイル)
     */
    public function getSearchHistoryDetailFileAction()
    {

        $fileName_id = $this->getRequest()->fileName_id;
        $model = new Keyword_Model_SuggestKeyword(null);
        
        $fileName = substr($fileName_id, 0, strrpos($fileName_id, '_'));
        $id = substr($fileName_id, strrpos($fileName_id, '_') + 1);
        
        $file = $model->getResultFile($fileName);
        
        if($file === false) {
            
            $result = false;
            
            if(isset($id)) {
                $result = $this->getSearchHistoryDetailAction($id);
                $this->view->historyid = $id;
            }
            
            if (!$result) {
                // データ存在しない
                $helper = $this->_helper->getHelper('Layout');
                $layout = $helper->getLayoutInstance();
                
                $layout->assign('content', $this->view->render("error/error.phtml"));
                
                //$errHtml = $layout->render();
                //echo $errHtml;
            }
            
        } else {

            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            
            $script = "";
            if($model->isValidIp()) {
                $script = "<script>$('.csvprime').show();</script>";
            }            
        	echo $file.$script;
        }        
    }
    
    /**
     * キーワードの検索履歴を取得する(DB)
     */
    private function getSearchHistoryDetailAction($id)
    {
        //$id = $this->getRequest()->id;
    
        $model = new Keyword_Model_SuggestKeyword(null);
        $result = $model->getSearchHistoryDetail($id);

        if ($result) {
            
            $this->_helper->layout->setLayout('historyd');
            $layout = $this->_helper->layout->getLayoutInstance();
            
            $layout->assign('keyword', $result['kword']);
            $layout->assign('rstCnt', $result['rstcnt']);
            $this->view->sk = $result['sk'];
            $this->view->indextab = $result['indextab'];
            
            // change phtml name
            $this->_helper->viewRenderer->setRender('get-suggest-keyword');
            return true;
        }
        return false;
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
    	
    	//Zend_Session::writeClose();
    	return $rst;
    }
}
