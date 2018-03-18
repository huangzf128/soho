<?php

Class Com_Const
{
	public function __construct(){}
	
	// システム用共通変数
	const CSV_EXPAND_LEVEL_MAX = 2;	           // CSV展開階層
	const CSV_EXPAND_PER_WAITTIME = 60;        // API呼びだし待ちタイム

	const MAX_RST_COUNT = 10;                  // 取得最大キーワード数
	
	// -------------------------------------------
	//     プログラム用 
	// -------------------------------------------
	
	const API_AMAZON = "http://completion.amazon.co.jp/search/complete?mkt=6&search-alias=aps&q=";
	const API_BING = "http://api.bing.com/qsonhs.aspx?mkt=ja-JP&q=";
	const API_YAHOO = "";
	const API_YOUTUBE = "";
	
	const KEY = 'gskwazkwyakwytkw2018.LONGLONGLONG';
	
	const FORBIDDEN = "Forbidden";
	
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
    
}