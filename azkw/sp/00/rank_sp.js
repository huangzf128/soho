
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
	//empty

	$("body").children().hide();
	$("body").append("<center style='margin-top:5rem'>Now Loading...</center>");

}
jqload2 = function(){

if ( !isSP ) { return; }

if ( jqcnt >= 1 ) {
	document.body.style.display="block";
}
jqcnt = 1;

tgw = "100%";
pnum = location.pathname.split("/")[1] + "";
base = "/sp/adscript/" + pnum + "/";
adname = "rank_";


$("body").append('<div id="sp_content"></div>').css({"background":"#ffffff"});
$("#sp_content").css({"width":tgw,"position":"absolute","top":"0px","left":"0px"});

$("td.yahoogif").remove();
$("iframe").remove();
$("ins").remove();

$("#sp_content").append('<div id="header"></div>');
$("#header").css({"background":"#006699"}).append('<div id="_sub_title">' + $("#subtitle").text() + '</div>');
$("#_sub_title").css({"color":"#ffffff"});
$("#header").append('<div id="_logo"></div>');
$("#_logo").append($("#logo a"));
$("#_logo img").attr("width","100%").removeAttr("height");
$("#sp_content").append('<div id="_read"></div>');
$("#_read").append($("#read").removeAttr("id").attr("width","100%"));
$("#_read td a").remove();
$("#_read td").css({"padding":"5px"});
$(".hatena-bookmark-button-frame").before('<span id="hatena"><span>');
$(".hatena-bookmark-button-frame").remove();
$("script[src*='hatena']").remove();
s = document.createElement("script")
s.src = "http://b.st-hatena.com/js/bookmark_button.js";
$(s).attr("charset","utf-8");
$(s).attr("async","async");
$("#hatena").append('<a href="http://b.hatena.ne.jp/entry/http://www.douseidoumei.net/" class="hatena-bookmark-button" data-hatena-bookmark-title="同姓同名辞典" data-hatena-bookmark-layout="standard" title="このエントリーをはてなブックマークに追加"><img src="http://b.st-hatena.com/images/entry-button/button-only.gif" alt="このエントリーをはてなブックマークに追加" width="20" height="20" style="border: none;" /></a>');
$("#hatena").append($(s));
setTimeout(snsFix,500);

aAd(1);

$("#sp_content").append('<div id="pankuzu" ></div>');
$("#pankuzu").append($("#pan").html());



$("#sp_content").append('<div id="ad' + "a" + '" class="adbox mt10"></div>');
$("#ad" + "a").load("/sp/adscript/00/rank_a.txt",adsPush);

$("#sp_content").append('<div id="contents_1" ></div>');
$("#contents_1").append($("#sogolink tr").eq(0).find("h1"));

$("#sp_content").append('<div id="contents_2" ></div>');
$("#contents_2").append($("#sogolink tr").eq(1).find("a"));
$("#contents_2 a").css({"padding-right":"1rem","line-height":"1.8rem"});
$("#contents_1 h1").css({"padding":0,"margin":"0px 0px 1rem 0px"});


$("#sp_content").append('<div id="adb" class="adbox mt10"></div>');
$("#adb").load("/sp/adscript/00/rank_b.txt",adsPush);
$("#sp_content").append('<div id="adc" class="adbox mt10"></div>');
$("#adc").load("/sp/adscript/00/rank_c.txt",adsPush);


$("#sp_content").append('<div id="contents_3" ></div>');
$("#contents_3").append($("#sogolink>tbody>tr").eq(2).find("h1"));
$("#contents_3").append($("#sogolink>tbody>tr").eq(3).find("a"));
$("#contents_3 a").css({"padding-right":"1rem","line-height":"1.8rem"});
$("#contents_3 h1").css({"padding":0,"margin":"0px 0px 1rem 0px"});

$("#sp_content").append('<div id="contents_4" ></div>');
$("#contents_4").append($("table#contents>tbody>tr>td>table>tbody>tr>td:contains('順位') table").attr("width","49%"));
$("#contents_4>table").css({"float":"left","margin":"0.4%"});
$("#contents_4>table:odd").after("<br clear='all' />");
$("#contents_4").append("<br clear='all' />");

$("#sp_content").append('<div id="_footnavi" ></div>');
$(".footnavi td p a").eq(0).remove();
//$("#_footnavi").append($(".footnavi td p").html());

//$("#_footnavi a").eq(2).before("<br />｜");
//$("#_footnavi a").eq(5).before("<br />｜");

$("#sp_content").append('<div id="_copy" ></div>');
$("#_copy").append($("p:contains('Reserved')").last());

aAd(2);


$("#sp_content").append('<div id="namesrc_w" ></div>');
$("#namesrc_w").append('<div id="namesrc" style="color:#ffffff;font-weight:bold;position:relative;" ></div>');
$("#namesrc").append('<span id="form_sei">姓：<input type="text" name="sei" size="5" /></span>');
$("#namesrc").append('<span id="form_mei">　名：<input type="text" name="mei" size="6" /></span>');
$("#namesrc").append('　<input type="button" id="btnsrc" value="検索" />');
$("#namesrc").append('<div id="srcclose" style="position:absolute;right:0.5rem;top:1.3rem; font-size: 3rem !important;">×</div>');

if ( location.pathname.indexOf("/mei") > 0 ) {
	$("#form_sei").hide();
}
if ( location.pathname.indexOf("/sei") > 0 ) {
	$("#form_mei").hide();
}

$("#namesrc_w").css({"width":"100%","background":"#0099dd"});
$("#namesrc").css({"padding":"1rem"});

$("#btnsrc").bind("click",function(){
	sei = "&"; mei = "&";
	$(".srchit").removeClass("srchit");
	if ( location.pathname.indexOf("/mei") < 0 ) {
		sei = $("input[name='sei']").val();
	}
	if ( location.pathname.indexOf("/sei") < 0 ) {
		mei = $("input[name='mei']").val();
	}
	

	if ( sei == "" || mei == "" ) {
		alert("姓名を入力してください")
	} else {
		sei = sei.replace(/\&/,"");
		mei = mei.replace(/\&/,"");
		sep = "";
		if ( location.pathname.indexOf("/dou") > 0 ) {
			sep = "　";
		}

		td = $("td:contains('" + sei + sep + mei + "')");
		if ( td.length == 0 ) {
			alert("お探しの姓名は見つかりませんでした")
		} else {
			scrollTo(0,$(td).offset().top);
			$(td).parent().css({"background":"#ffff66"}).addClass("srchit");
		}
	}
});
$("#sp_content").css({"margin-bottom":$("#namesrc_w").height()});

if ( navigator.userAgent.match(/Android/)) {
	$("#namesrc_w").css({"position":"absolute","left":0});
	$(window).bind("scroll",function(){
		$("#namesrc_w").css({"top":$(window).scrollTop() + $(window).height() - ($("#namesrc_w").height()*2)});
	});
	$("#namesrc_w").css({"top":$(window).scrollTop() + $(window).height() - ($("#namesrc_w").height()*2)});
} else {
	$("#namesrc_w").css({"position":"fixed","left":0,"bottom":0});
}

$("#srcclose").bind("click",function(){

	if ( $("#namesrc_w").hasClass("close") ) {
		//広げる
		$("#namesrc_w").css({"left":0,"margin-left":0}).removeClass("close");
		$("#srcclose").text("×");
	} else {
		//閉じる
		$("#namesrc_w").css({"left":"-100%","margin-left":"2rem"}).addClass("close")
		$("#srcclose").text("＞");
	}

	
})


$("body>table").remove();
$("center").eq(0).remove();
$("iframe[name='google_conversion_frame']").css({"height":1,"width":1,"position":"absolute"});
$("body").show();


};

snsFix = function(){

	if ( $("#_read iframe").length == 3 ) {
		$("#_read iframe").eq(0).css({"width":100});
		$("#_read iframe").eq(1).css({"width":70});
		$("#_read iframe").eq(2).css({"width":120});
	} else {
	}
		setTimeout(snsFix,500);

}

//aAd = function(n){
//
//$("#sp_content").append('<div id="ad' + n + '" class="mt10 adbox"></div>');
//if ( isNaN(n) ) {
//	pnum = location.pathname.split("/")[1] + "";
//	$("#ad" + n).load("/sp/adscript/" + pnum + "/common_" + n + ".txt",adsPush);
//} else {
//	$("#ad" + n).load(base + adname + n + ".txt",adsPush);
//}
//
//}

window.onload = jqload2;
