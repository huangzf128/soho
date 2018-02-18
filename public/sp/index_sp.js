
var jq = document.createElement("script");
jq.src = "//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js";

var jqloaded = false;
var jQuery = null;

document.head.appendChild(jq);

jqcnt = 0;

onLoad_sub = function(){

	setTimeout(function(){
		onLoad()
	},1000);

}

if ( isSP ) {

	jqloadtimer = setInterval(function(){

		if ( window.$ ){
			clearInterval(jqloadtimer);
			jqload();
		}

	},100);


	//<link href="http://www.gesyuku.net/_css/style.css" rel="stylesheet" type="text/css">

	var spcss = document.createElement("link");
	spcss.rel = "stylesheet";
	spcss.type = "text/css";
	spcss.href = "/sp/index_sp.css";

	document.head.appendChild(spcss);

} else {
	try{
		document.body.style.display="block";
	}catch(ex){
	}
}

jqload = function(){
}

window.onload = function(){
	jqload2();
}
jqload2 = function(){

if ( isSP == false ) { return; }

if ( jqcnt >= 1 ) {
	document.body.style.display="block";
}
jqcnt = 1;

tgw = "100%";
base = "/sp/adscript/";
adname = "result_ad_";

$("#list,#wrapper,#header,#header p,#header #fav,#footer-outer-block,#main,#content,#readtex,#form,#keyword,#form #f01").css({"width":"auto"});
$("#f01").css({"width":"100%"});
$("header,h1").remove()


$("#fav").before('<div id="logo"><div style="text-align:center;margin-bottom: 0.3rem; font-size: 2.8rem !important;font-weight: bold;">グーグルサジェスト キーワード一括ＤＬツール</div><a href="/"><img src="http://www.gskw.net/_img/gskpdt.png" width="100%" style="border:0px;" /></a></div>');

setTimeout(function(){
addHatena();
},500);

$("#header p").css({"background":"#333333","color":"#ffffff","text-align":"center","padding":"5px", "margin-bottom":"0.5rem"});

$(".fb-like").css({"margin-left":30});
$("#lefticon2").css({"margin-left":-7});
$("#lefticon3").css({"padding-left":5});
$("#fav").css({"margin-left":15});
$("#sp_sns").css({"cssText":"  height: 60px;  background: #333;  padding: 0.5rem 0px;"});

//$("#wrapper").before('<br clear="all" />');

$("#keywordFrm label").wrap("<div id='form_input'></div>")
$("#form_input").prepend($("#f_txt"));
$("#but").before('<br clear="all" style="line-height: 1px;" />');
$("#wrapper").before('<br clear="all" style="line-height: 1px;" />');

$("#resultbar,#footbar").remove();

if ( $("#list").length > 0 ) {

	$("a[href='#0']").before('<span>キーワード：</span>');
	$("a[href='#1']").text("▼一覧へ").css({"padding-left":"1rem"});
	$("#indextab").after('<table id="indextab" ><tbody></tbody></table>').attr("id","indextab_bk");
	
	tr = 0;
	$("#indextab tbody").append("<tr></tr>");
	for ( c = 2; c < 107; c++){
		if ( c % 15 == 2 ) {
			tr += 1;
			$("#indextab tbody").append("<tr></tr>");
		}
		$("#indextab tr").eq(tr).append($("#indextab_bk td").eq(2));
	}
}

return false;

/////////////////

$("body").append('<div id="pc_content"></div>');
$("#pc_content").append($("body").children().not("#pc_content"));
$("#pc_content").css({"display":"none"});

$("body").append('<div id="sp_content"></div>').css({"background":"#ffffff"}).attr("id","top");
$("#sp_content").css({"width":tgw,"position":"absolute","top":"0px","left":"0px"});

$("#sp_content").append("<header></header>");
$("header").css({"background":"#333333","color":"#ffffff","text-align":"center","padding":"5px", "margin-bottom":"0.5rem"});

$("#sp_content").append('<div id="logo"><div style="text-align:center;margin-bottom: 0.3rem; font-size: 2.8rem !important;font-weight: bold;">グーグルサジェスト キーワード一括ＤＬツール</div><img src="http://www.gskw.net/_img/gskpdt.png" width="100%" /></div>');

$("header").append($("#header p").text().replace("まとめて","まとめて<br />"));
$("#sp_content").append('<div id="sp_sns"></div>');
$("#sp_sns").append($("#fav"));
setTimeout(function(){
addHatena();
},500);
$(".fb-like").css({"margin-left":30});
$("#lefticon2").css({"margin-left":-7});
$("#lefticon3").css({"padding-left":5});
$("#fav").css({"margin-left":15});
$("#sp_sns").css({"cssText":"  height: 60px;  background: #333;  padding: 0.5rem 0px;"});

//$("#sp_content").append('<div id="logo2"></div>');
//$("#logo2").append($("h1"));
//$("#logo2 h1").append('<img src="http://www.gskw.net/_img/sitelogo.jpg" width="100%" />');
//$("#logo2 h1").attr("width","100%").css({"background-size":"100% auto","margin":0,"height":"auto"});
//$("#logo2 h1 span").hide();

aAd(1);

///////////////////////////////////////////////////////////////

$("#sp_content").append('<div id="content1"></div>');
$("#content1").append($("#readtex")).css({"padding":"0.5rem 1rem"});

$("#sp_content").append('<div id="content2"></div>');
$("#content2").append($("#form").css({"-webkit-transform-origin":"left top","-webkit-transform":"translateX(-25%) scale(0.5)","position":"relative","left":"50%"}));
$("#content2 input[type='button']").css({"-webkit-transform":"translateY(50%) scale(2)"});
$("#keywordFrm").attr("action","/result?debug=1");
$("#keyword").css({"cssText":"font-size:5.2rem !important"});

aAd(2);

$("#sp_content").append($("footer").attr("id","sp_footer"));
$("#footer-outer-block").css({"width":"auto"});

aAd(3);


$("iframe[name='google_conversion_frame']").css({"height":1,"width":1,"position":"absolute"});
$("body").show();


};

addHatena = function(){
	$(".hatena-bookmark-button-frame").before('<span id="hatena"><span>');
	$(".hatena-bookmark-button-frame").remove();
	$("script[src*='hatena']").remove();
	s = document.createElement("script")
	s.src = "http://b.st-hatena.com/js/bookmark_button.js";
	$(s).attr("charset","utf-8");
	$(s).attr("async","async");
	$("#hatena").append('<a href="http://b.hatena.ne.jp/entry/http://www.gskw.net/" class="hatena-bookmark-button" data-hatena-bookmark-title="グーグルサジェストキーワード一括ＤＬツール" data-hatena-bookmark-layout="vertical-balloon" data-hatena-bookmark-lang="ja" title="このエントリーをはてなブックマークに追加"><img src="http://b.st-hatena.com/images/entry-button/button-only.gif" alt="このエントリーをはてなブックマークに追加" width="20" height="20" style="border: none;" /></a>');
	$("#hatena").append($(s));
	setTimeout(snsFix,500);
}

snsFix = function(){

	if ( $("#fav iframe").length == 3 ) {
		$("#fav iframe").eq(0).css({"width":100});
		$("#fav iframe").eq(1).css({"width":70});
		$("#fav iframe").eq(2).css({"width":120});
	} else {
	}
		setTimeout(snsFix,500);

}

