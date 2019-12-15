$(function() {
	// hide all the sub-menus
	$("span.toggle").next().hide();
	
	// set the cursor of the toggling span elements
	$("span.toggle").css("cursor", "pointer");
	
	// prepend a plus sign to signify that the sub-menus aren't expanded
	
	$("span.toggle").prepend("+ ");
	// add a click function that toggles the sub-menu when the corresponding
	// span element is clicked
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
/*
var resultbarObj = $("#form");
if(resultbarObj.length > 0 && $("#list").length > 0){
	
	document.write("<iframe id='resultbar' style='display:block;margin:0 auto;width:730px;height:400px;' " +
			"scrolling='no' frameBorder='0' src='/banner/result.php?param=1'></iframe>");
}

var footbarObj = $("#footer-area");
if(footbarObj.length > 0 && $("#list").length > 0){
	
	document.write("<iframe id='footbar' style='display:block;margin:20px auto;width:750px;height:150px;' " +
			"scrolling='no' frameBorder='0' src='/banner/footer.php?param=1'></iframe>");
}

var footbardownObj = $("#wrapper");
if(footbardownObj.length > 0 && $("#list").length > 0){
	
	document.write("<iframe id='footbardown' style='display:block;margin:20px auto;width:750px;height:150px;' " +
			"scrolling='no' frameBorder='0' src='/banner/footerdown.php?param=1'></iframe>");
}


$(function(){

	// 検索条件部バナー	
	if(resultbarObj.length > 0 && $("#list").length > 0){
		$("#resultbar").insertAfter(resultbarObj);
	} 
	
	// フッターバナー
	if(footbarObj.length > 0 && $("#list").length > 0) {
		$("#footbar").insertBefore(footbarObj);
	}
	
	// フッターバナー
	if(footbardownObj.length > 0 && $("#list").length > 0) {
		$("#footbardown").insertAfter(footbardownObj);
	}	
	
});
*/