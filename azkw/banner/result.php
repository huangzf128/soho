<?php
	
	if(!isset($_GET["param"])){
		echo "";
	}else{
?>

<html>
<head>
<style type="text/css">
body {
}
table.vici {
	border-top: 1px solid #C8E1F5;
	border-bottom: 1px solid #C8E1F5;
	border-collapse: collapse;
	border-left: 1px solid #C8E1F5;
	margin: 40px auto 5px;
	width:100%;
}
.vici th{
	padding: 4px;
	border-right: 1px solid #C8E1F5;
	border-bottom: 1px solid #C8E1F5;
	background-color: #C8E1F5;
	font-weight: bold;
	text-align: center;
	font-size: 12px;
	color: #fff;
}
.vici td{
	padding: 4px;
	border-bottom: 1px solid #C8E1F5;
	border-right: 1px solid #C8E1F5;
}
.adbar {
	margin-right: auto;
	margin-left: auto;
	margin-top:20px;
}
</style>
</head>
<body>

<span id="result_div">
<table class="vici">
  <tr>
    <th colspan="2">スポンサードリンク</th>
    </tr>
  <tr>
    <td><script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- アマゾンサジェスト_336x280_t -->
<ins class="adsbygoogle"
     style="display:inline-block;width:336px;height:280px"
     data-ad-client="ca-pub-0338168428759491"
     data-ad-slot="2748713555"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</td>
    <td><script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- アマゾンサジェスト_336x280_i -->
<ins class="adsbygoogle"
     style="display:inline-block;width:336px;height:280px"
     data-ad-client="ca-pub-0338168428759491"
     data-ad-slot="4085845951"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script></td>
  </tr>
</table>


<div class="adbar">
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- アマゾンサジェスト_728x15 -->
<ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:15px"
     data-ad-client="ca-pub-0338168428759491"
     data-ad-slot="3806644357"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script></div>

</span>

<div class="clear"></div>
</body>

<script>
    var pram = location.search;
    if (!pram){
		document.getElementById("result_div").style.display = "none";
    }
</script>

</html>
<?php 
	}
?>