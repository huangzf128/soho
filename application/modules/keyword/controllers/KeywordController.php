<?php
require_once 'Zend/Http/Client.php';

class Keyword_KeywordController extends Zend_Controller_Action
{
	private $accessTimeLimit = 60;
	private $accessCountLimit = 4;
	private $userid = null;
	private $service = null;
	
    public function init()
    { 
        $zend_session = new Zend_Session_Namespace("auth");
        if (isset($zend_session->userid)) {
            $this->userid = $zend_session->userid;
            $this->service = $zend_session->service;
            
            $this->_helper->layout->assign('userid', $zend_session->userid);
            $this->_helper->layout->assign('service', $zend_session->service);
            $this->_helper->layout->assign('usertype', $zend_session->type);
            $this->_helper->layout->assign('username', $zend_session->username);
            
            $zend_session->lastModified = time();
            $zend_session->setExpirationSeconds(Com_Const::SESSION_EXPIRE);
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
        set_time_limit(60);
        
        if(!$this->accessLimitCheck() || $model->isBlockIp()){
        	$this->_helper->layout->assign('keyword', $keyword);
        	$this->_helper->layout->assign('rstCnt', $model->getRstCnt());
        	$this->view->sk = "<font color='red'>アクセス制限のため、しばらく経ってから、検索してください。</font><br/><br/><br/>";
        	$this->view->indextab = "";
        	$this->_helper->layout->setLayout('layout');
        	return ;
        }

        // block check
        if($model->isBlockKeyword($keyword)){
        
            $this->_helper->layout->assign('keyword', $keyword);
            $this->_helper->layout->assign('rstCnt', $model->getRstCnt());
            $this->view->sk = "<font color='red'>入力されたキーワードは、当ツールでは対象外です。</font><br/><br/><br/>";
            $this->view->indextab = "";
            $this->_helper->layout->setLayout('layout');
             
            return;
        }
        
        // ================  check ========================
		// keyword check
		$checkResult = $model->checkKeyword();
       	if(1 === $checkResult) {
            $this->_helper->layout->assign('keyword', $keyword);
	        $this->_helper->layout->assign('rstCnt', $model->getRstCnt());
        	$this->view->sk = "<font color='red'>入力するキーワードは、25文字以内にしてください。</font><br/><br/><br/>";
	        $this->view->indextab = "";
        	$this->_helper->layout->setLayout('layout');
        	return;
        } else if(0 === $checkResult) {
        	$this->_response->setRedirect('/index')->sendResponse();
        	return;
        }

		// ==================================================
		
		$this->_helper->layout->setLayout('historyd');
		$layout = $this->_helper->layout->getLayoutInstance();
		
		// -------------------- recent keyword ---------------------
		$recentRst = $model->getRecentKeyword();
		if ($recentRst) {
		    $layout->assign('keyword', $recentRst["kword"]);
		    $layout->assign('rstCnt', $recentRst["rstcnt"]);
		    if ($recentRst["bflag"] == 0) {
		        $this->view->sk = $recentRst["sk"];  // 検索結果
		        $this->view->indextab = $recentRst["indextab"];      // 索引
		    } else {
		        $this->view->sk = gzuncompress($recentRst["skb"]);  // 検索結果
		        $this->view->indextab = gzuncompress($recentRst["indextabb"]);      // 索引
		    }
		    return;
		}
		// --------------------------------------------------------
		
		if (Com_Util::isZeroServiceEnabled(Com_Const::SERVICE_ZERO_G)) {
		    // 有料
		    $result = $model->getSuggestKeyword();
		    if ($result === Com_Const::FORBIDDEN) {
		        $result = $model->getSuggestKeywordOtherServer(Com_Const::SUGGEST_SERVER_GOOGLE_SECOND);
		    }
		} else {
		    // 無料
		    $result = $model->getSuggestKeywordOtherServer();
		}
	    
	    $layout->assign('keyword', $keyword);
	    $layout->assign('rstCnt', $model->getRstCnt());
	    $this->view->sk = $model->getStrSuggestKeywords();  // 検索結果
	    $this->view->indextab = $model->getIndexTab();      // 索引
	    
	    $registFlg = $this->getRequest()->registflg;
	    //検索情報をDB登録
	    if(isset($registFlg) && $registFlg == 1 && $result){
	    
	        $model->setRegistdt(date('Y-m-d H:i:s'));
	        $this->view->historyid = $model->registSearchHistoryResult();
	    }
		    
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
    }
    
    
    /**
     * キーワードの検索履歴を取得する(物理ファイル)
     */
    public function getSearchHistoryDetailFileAction()
    {

        $fileName = $this->getRequest()->filename;
        $model = new Keyword_Model_SuggestKeyword(null);
        
        $registdt = substr($fileName, 0, strrpos($fileName, '_'));
        $keyword = substr($fileName, strrpos($fileName, '_') + 1);
        
        $result = false;
        if(isset($registdt)) {
            $result = $this->getSearchHistoryDetailAction($registdt, $keyword);
        }
        
        if (!$result) {
            // データ存在しない
            $this->_helper->assign('content', $this->view->render("error/error.phtml"));
        } else {
            
            $file = $model->getResultFile($fileName);
            
            // 存在チェック
            if($file === false) {
            
                $this->_helper->layout->setLayout('historyd');
                $layout = $this->_helper->layout->getLayoutInstance();
            
                $layout->assign('keyword', $result['kword']);
                $layout->assign('rstCnt', $result['rstcnt']);
                $this->view->historyid = $result['id'];
                
                if ($result["bflag"] == 0) {
                    $this->view->sk = $result['sk'];
                    $this->view->indextab = $result['indextab'];
                } else {
                    $this->view->sk = gzuncompress($result['skb']);
                    $this->view->indextab = gzuncompress($result['indextabb']);
                }
            
                // change phtml name
                $this->_helper->viewRenderer->setRender('get-suggest-keyword');
                return true;
            
            } else {
            
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender(true);
            
                // 既存の履歴は、表示・非表示でIPCSVを制御する
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
    
        $model = new Keyword_Model_SuggestKeyword(null);
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
    	
    	//Zend_Session::writeClose();
    	return $rst;
    }
    
}
