<?php
	
	if(!isset($_GET["param"])){
		echo "";
	}else{
?>

<html>
<head>
<style type="text/css">
body {
	margin:0;
	
}
p#readtex {
	margin:0;
	padding:0;
}
table.vici{
	border-top: 1px solid #0167CC;
	border-bottom: 1px solid #0167CC;
	border-collapse: collapse;
	border-left: 1px solid #0167CC;
	margin: 40px 0 0px;
}
.vici th{
	padding: 4px;
	border-right: 1px solid #0167CC;
	border-bottom: 1px solid #0167CC;
	background-color: #0167CC;
	font-weight: bold;
	text-align: center;
	font-size: 12px;
	color: #fff;
}
.vici td{
	padding: 4px;
	border-bottom: 1px solid #0167CC;
	border-right: 1px solid #0167CC;
}
</style>
</head>
<body>

<div id="footer_div" class="adbar"><script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- アマゾンサジェスト_728x15 -->
<ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:15px"
     data-ad-client="ca-pub-0338168428759491"
     data-ad-slot="3806644357"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script><br>
<br>
<!-- Rakuten Widget FROM HERE -->
<script type="text/javascript">rakuten_design="slide";rakuten_affiliateId="0db2b8b7.9e24b860.0e37ebb6.52ef4894";rakuten_items="ctsmatch";rakuten_genreId=0;rakuten_size="728x90";rakuten_target="_blank";rakuten_theme="gray";rakuten_border="on";rakuten_auto_mode="off";rakuten_genre_title="off";rakuten_recommend="on";</script>
<script type="text/javascript" src="http://xml.affiliate.rakuten.co.jp/widget/js/rakuten_widget.js">
</script><!-- Rakuten Widget TO HERE -->
</div>
</body>

<script>
    var pram = location.search;
    if (!pram){
		document.getElementById("footer_div").style.display = "none";
    }
</script>

</html>

<?php 
	}
?>