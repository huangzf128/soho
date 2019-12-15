
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
adname = "index_ad_";


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
$("#ada").load("/sp/adscript/00/contents_ad_a.txt",adsPush);

$(".contentsin tr").first().remove();

$("#sp_content").append('<div id="contents_1" ></div>');
$("#gosearch").remove();
$("#contents_1").append($(".contentsin").css({"width":320,"padding":0,"margin-top":"1rem"}));

$("#contents_1 h2").css({"width":"100%"});

$("#gosearch").parent().remove();

$("table[width='200']").remove();

$(".contentsin>tbody").append("<tr></tr>");
$(".contentsin>tbody>tr").last().append($(".contentsin>tbody>tr>td").eq(1));

$(".contentsin>tbody>tr").last().after("<tr></tr>");
$(".contentsin>tbody>tr").last().append('<div id="ad' + "b" + '" class="adbox mt10"></div>');
$(".contentsin>tbody>tr").last().append('<div id="ad' + "c" + '" class="adbox mt10"></div>');
$("#adb").load("/sp/adscript/00/contents_ad_b.txt",adsPush);
$("#adc").load("/sp/adscript/00/contents_ad_c.txt",adsPush);

$(".contentsin>tbody").append("<tr></tr>");
$(".contentsin>tbody>tr").last().append($(".contentsin>tbody>tr>td").eq(1));



$("table:contains('スポンサーサイト')").last().remove();

for ( e = 0; e < $("tr[bgcolor='#FF6600'] table tr").length; e++ ) {
e++;
$("tr[bgcolor='#FF6600'] table tr").eq(e-1).append($("tr[bgcolor='#FF6600'] table tr").eq(e).find("td"));
$("tr[bgcolor='#FF6600'] table tr").eq(e).remove();
e--;
}
$("td[width=200][nowrap]").eq(1).attr("width","300").attr("id","preflink");
$("#preflink").find("[width='180']").attr("width","100%");
$("#preflink").find("[width='180']").attr("width","100%");
$("#preflink table table tr").eq(0).find("td").attr("width","25%");

$("#sp_content").append('<div id="_footnavi" ></div>');
$(".footnavi td p a").eq(0).remove();
//$("#_footnavi").append($(".footnavi td p").html());

//$("#_footnavi a").eq(2).before("<br />｜");
//$("#_footnavi a").eq(5).before("<br />｜");

$("#sp_content").append('<div id="_copy" ></div>');
$("#_copy").append($("p:contains('Reserved')").last());

aAd(2);

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

window.onload = jqload2;
