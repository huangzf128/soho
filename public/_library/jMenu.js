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
if(resultbarObj.length > 0 && $("#list").length > 0){
	// result.html
	document.write("<iframe id='resultbar' style='display:block;margin:0 auto;width:700px;height:360px;' " +
	"scrolling='no' frameBorder='0' src='/banner/result.html?param=1'></iframe>");
}

var footbarObj = $("#wrapper");
if(footbarObj.length > 0 && $("#list").length > 0){
	// footer.html
	document.write("<iframe id='footbar' style='display:block;margin:20px auto;width:750px;height:150px;' " +
			"scrolling='no' frameBorder='0' src='/banner/footer.html?param=1'></iframe>");
}

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

$(function(){

	// 検索条件部バナー	
	if(resultbarObj.length > 0 && $("#list").length > 0){
		$("#resultbar").insertAfter(resultbarObj);		
		$("#sidebar").insertAfter(resultbarObj);
	} 
	
	// フッターバナー
	if(footbarObj.length > 0 && $("#list").length > 0) {
		$("#footbar").insertAfter(footbarObj);
	}	
});
