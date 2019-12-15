$(function() {
	// hide all the sub-menus
	$("span.toggle").next().hide();
	
	// set the cursor of the toggling span elements
	$("span.toggle").css("cursor", "pointer");
	
	$("span.toggle").prepend("+ ");

	$("span.toggle").click(function() {
		//$(this).next().toggle(1000);
		$(this).next().toggle(6);
		
		// switch the plus to a minus sign or vice-versa
		var v = $(this).html().substring( 0, 1 );
		if ( v == "+" )
			$(this).html( "-" + $(this).html().substring( 1 ) );
		else if ( v == "-" )
			$(this).html( "+" + $(this).html().substring( 1 ) );
	});

});

// トップページにバナーを表示しないので、$("#list").lengthでトップページかどうかを判断する

var resultbarObj = $("#form");

if($("#list").length > 0){
	// side.html
	document.write("<iframe onload='setToDiv(this);' id='sidebar' style='display:none;margin:0 auto;width:730px;height:400px;' " +
			"scrolling='no' frameBorder='0' src='/banner/side.html?param=1'></iframe>");
}


function setToDiv(iframe){

	var divObj = document.createElement("div");
	divObj.id = "bannerWrap";
	resultbarObj[0].appendChild(divObj);
	
	var childs = $("#sidebar").contents().find("body:eq(0)")[0].childNodes;
	var cnt = childs.length;
	for(var i = cnt - 1; i >= 0; i--){
		var child = childs[i];
		var c = $(child).detach();
		c.prependTo("#bannerWrap");
	}
}

var href = window.location.href ;
var bar = href.match(/.+history.archive.([0-9]+)_.+/);
var showAddOneFlag = false;
var displayDate = "20161201133457";

// displayDateより過去の履歴頁にバナーを表示する
try {
	if (bar != null && bar.length >= 1 && bar[1] < displayDate )
	{
		showAddOneFlag = true;
	}
} catch (e) {

}
var resultbarObj = $(".aiueobox-txt:eq(0)");
if(resultbarObj.length > 0 && $("#list").length > 0 && showAddOneFlag){
	document.write("<iframe id='resultbar' style='display:block;margin:0 auto;width:730px;height:400px;' " +
			"scrolling='no' frameBorder='0' src='/banner/result.php?param=1'></iframe>");
}

//footerバナー
var footerObj = $("#pc-12-admax");
if(footerObj.length > 0 && $("#list").length > 0 && showAddOneFlag){
	document.write("<iframe id='footerbar' style='display:block;margin:0 auto;width:900px;height:220px;' " +
			"scrolling='no' frameBorder='0' src='/banner/footer.php?param=1'></iframe>");
}


$(function(){

	// 検索条件部バナー	
	if(resultbarObj.length > 0 && $("#list").length > 0){
		$("#resultbar").insertAfter(resultbarObj);		
		$("#sidebar").insertAfter(resultbarObj);
	} 

	// footerバナー
	if(footerObj.length > 0 && $("#list").length > 0){
		$("#footerbar").insertAfter(footerObj);
	}
	
});
