try{
//document.body.style.display = "none";
}catch(ex){ }
var isSP = false;
if ( navigator.userAgent.match(/iPhone|iPad|iPod|Android|Windows\ Phone/)) {
	//if ( location.search.indexOf("?debug") >= 0 ) {
		isSP = true;
	//}
} else {
//	document.body.style.display = "block";
}
adsPush = function(){
	try{
	(adsbygoogle = window.adsbygoogle || []).push({});
	} catch(ex){}
}


aAd = function(n){

	$("#sp_content").append('<div id="ad' + n + '" class="mt10"></div>');
	$("#ad" + n).load(base + adname + n + ".txt",
		function(){
			for ( i in $("ins",$(this)) ) {
				setTimeout(function(){
					adsPush();
				},500);
			}
		}
	);

}