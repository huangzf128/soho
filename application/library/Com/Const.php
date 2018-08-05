<?php

Class Com_Const
{
	public function __construct(){}
	
	// システム用共通変数
	const CSV_EXPAND_LEVEL_MAX = 3;	           // CSV展開階層
	const CSV_EXPAND_PER_WAITTIME = 30;        // API呼びだし待ちタイム
	const CSV_EXPAND_PER_WAITTIME_G = 15;      // API呼びだし待ちタイム Google

	const MAX_RST_COUNT = 10;                  // 取得最大キーワード数
	const EAPPID_RETRY_COUNT = 5;              // Yahoo専用
	
	const FILE_LOCK_TIMEOUT = 15;
	const SESSION_EXPIRE = 1440;
	
	// -------------------------------------------
	//     プログラム用 
	// -------------------------------------------
	
	const API_GOOGLE = "http://www.google.co.jp/complete/search?hl=en&output=toolbar&q=";
	const API_AMAZON = "http://completion.amazon.co.jp/search/complete?mkt=6&search-alias=aps&q=";
	const API_BING = "http://api.bing.com/qsonhs.aspx?mkt=ja-JP&q=";
	const API_YAHOO = "http://assist.search.yahooapis.jp/AssistSearchService/V1/webassistSearch?output=iejson&callback=ytopAssist&";
	const API_YOUTUBE = "http://clients1.google.co.jp/complete/search?hl=ja&ds=yt&client=firefox&q=";
	
	// csv default server
	// const CSV_SERVER_GOOGLE = "http://ad8.coolblog.jp/kw/result?site={site}&keyword=";
	const SUGGEST_SERVER_GOOGLE = "http://line.b4.valueserver.jp/kw/keywordco/search/get-Suggest-Keyword?keyword={keyword}&p={p}";
	const SUGGEST_SERVER_GOOGLE_SECOND = "http://medical-blog.net/kw/keywordco/search/get-Suggest-Keyword?keyword={keyword}&p={p}";
	
	
	const KEY = 'gskwazkwyakwytkw2018.LONGLONGLONG';
	
	const FORBIDDEN = "Forbidden";
	const SERVICEUNAVAILABLE = "Service Unavailable";
	const EAPPIDERR = "eappiderr";
	const ERROR = "error";
	const INTERRUPTION = "interruption";
	
	// CSV取得状況
	const STATUS_ING = 0;
	const STATUS_FINISH = 1;
	const STATUS_FORBIDDEN = 8;
	const STATUS_ERROR = 9;
	
    // ユーザータイプ
    const USER_ADMIN = 9;
    const USER_COMMON = 1;
    
    // サイト名
    const GOOGLE = 1;
    const AMAZON = 2;
    const BING = 3;
    const YAHOO = 4;
    const YOUTUBE = 5;
    const GOOGLE_S = 6;
    
    // 実行タイム(秒)
    const EXECUTE_TIME_G = 120;
    const EXECUTE_TIME = 50;
    
    // service
    const SERVICE_ZERO_G = "zero_google";
    const SERVICE_ZERO_A = "zero_amazon";
    const SERVICE_ZERO_B = "zero_bing";
    const SERVICE_ZERO_YA = "zero_yahoo";
    const SERVICE_ZERO_YT = "zero_youtube";
    
    const SERVICE_CSV_G = "csv_google";
    const SERVICE_CSV_A = "csv_amazon";
    const SERVICE_CSV_B = "csv_bing";
    const SERVICE_CSV_YA = "csv_yahoo";
    const SERVICE_CSV_YT = "csv_youtube";
    
}