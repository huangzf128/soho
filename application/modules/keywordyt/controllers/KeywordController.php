<?php
require_once 'Zend/Http/Client.php';

class Keywordyt_KeywordController extends Zend_Controller_Action
{
	private $accessTimeLimit = 60;
	private $accessCountLimit = 4;
	private $userid = null;
	
    public function init()
    { 
        $zend_session = new Zend_Session_Namespace("auth");
        if (isset($zend_session->userid)) {
            $this->_helper->layout->assign('userid', $zend_session->userid);
            $this->userid = $zend_session->userid;
            $this->_helper->layout->assign('usertype', $zend_session->type);
            $this->_helper->layout->assign('username', $zend_session->username);
        }
        
        $model = new Keywordyt_Model_SuggestKeyword(null);
        $this->_helper->layout->assign('isValidIp', $model->isValidIp());  // IPユーザー
    }

    public function indexAction() { }
    
    /**
     * 検索ボタン押下イベント
     */
    public function getSuggestKeywordAction()
    {   
    	
        $keyword = trim(mb_convert_encoding($this->getRequest()->keyword, "utf-8","UTF-8,eucjp-win,sjis-win"));
        
        $model = new Keywordyt_Model_SuggestKeyword($keyword);
        
        
        if(!$this->accessLimitCheck() || $model->isBlockIp()){
        	$this->_helper->layout->assign('keyword', $keyword);
        	$this->_helper->layout->assign('rstCnt', $model->getRstCnt());
        	$this->view->sk = "<font color='red'>アクセス制限のため、しばらく経ってから、検索してください。</font><br/><br/><br/>";
        	$this->view->indextab = "";
        	
        	$this->render("get-suggest-keyword-yt");
        	return ;
        }
        
        if($model->isBlockKeyword($keyword)){
        
            $this->_helper->layout->assign('keyword', $keyword);
            $this->_helper->layout->assign('rstCnt', $model->getRstCnt());
            $this->view->sk = "<font color='red'>入力されたキーワードは、当ツールでは対象外です。</font><br/><br/><br/>";
            $this->view->indextab = "";
             
            $this->render("get-suggest-keyword-yt");
            return;
        }
        
		// keyword check, block check
        $checkResult = $model->checkKeyword();
        if(1 === $checkResult) {
            $this->_helper->layout->assign('keyword', $keyword);
	        $this->_helper->layout->assign('rstCnt', $model->getRstCnt());
        	$this->view->sk = "<font color='red'>入力されたキーワードは、0文字以上、25文字以内にしてください。</font><br/><br/><br/>";
	        $this->view->indextab = "";
	        
	        $this->render("get-suggest-keyword-yt");
        	return;
        } else if(0 === $checkResult) {
        	$this->_response->setRedirect('/index')->sendResponse();
        	return;
        }
        			        
		// 検索
		$result = $model->getSuggestKeyword();
                        
        
        $this->_helper->layout->setLayout('historyd_yt');
        $layout = $this->_helper->layout->getLayoutInstance();
        
        $layout->assign('keyword', $keyword);
        $layout->assign('rstCnt', $model->getRstCnt());
        $this->view->sk = $model->getStrSuggestKeywords();
        $this->view->indextab = $model->getIndexTab();

        $registFlg = $this->getRequest()->registflg;
        //検索情報をDB登録
        if(isset($registFlg) && $registFlg == 1 && $result){
            
        	$model->setRegistdt(date('Y-m-d H:i:s'));
        	$this->view->historyid = $model->registSearchHistoryResult();
        }
              
        $this->render("get-suggest-keyword-yt");
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
        
        $model = new Keywordyt_Model_SuggestKeyword($keyword);
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
        $this->_helper->layout->setLayout('history_yt');
        
        $currentNo = isset($this->getRequest()->currentNo) ? $this->getRequest()->currentNo : 1;        
        $model = new Keywordyt_Model_SuggestKeyword(null);
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
        $this->view->usertype = $this->_helper->layout()->usertype;
        
//         $this->_helper->viewRenderer->setScriptAction("get-search-history-list_az");
		$this->render("get-search-history-list-yt");
    }
    
    
    /**
     * キーワードの検索履歴を取得する(物理ファイル)
     */
    public function getSearchHistoryDetailFileAction()
    {
        $fileName = $this->getRequest()->fileName;      
        $model = new Keywordyt_Model_SuggestKeyword(null);
        
        $registdt = substr($fileName, 0, strrpos($fileName, '_'));
        $keyword = substr($fileName, strrpos($fileName, '_') + 1);
        
        $result = false;
        if(isset($registdt)) {
            $result = $this->getSearchHistoryDetailAction($registdt, $keyword);
        }
         
        if (!$result) {
            // データ存在しない
            $this->_helper->layout->assign('content', $this->view->render("error/error.phtml"));
        } else {
            
            if ($result["showtype"] == 2) {
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender(true);
            
                echo $result["sk"];
                return;
            }
            
            // 存在する場合、file? DB?
            $file = $model->getResultFile($fileName);
            
            // 存在チェック
            if( $file === false ) {
            
                $this->_helper->layout->setLayout('historyd_yt');
                $layout = $this->_helper->layout->getLayoutInstance();
            
                $layout->assign('keyword', $result['kword']);
                $layout->assign('rstCnt', $result['rstcnt']);
                $this->view->sk = $result['sk'];
                $this->view->indextab = $result['indextab'];
                $this->view->historyid = $result['id'];
            
                // change phtml name
                $this->_helper->viewRenderer->setRender('get-suggest-keyword-yt');
                return true;
            
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
    }    
        
    /*------------------------------------------------------------------------
     *  private
     *------------------------------------------------------------------------*/
    
    /**
     * キーワードの検索履歴を取得する(DB)
     */
    private function getSearchHistoryDetailAction($registdt, $keyword)
    {
        //$id = $this->getRequest()->id;
        
        $model = new Keywordyt_Model_SuggestKeyword(null);
        return $model->getSearchHistoryDetail($registdt, $keyword);
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
