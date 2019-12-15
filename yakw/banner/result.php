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


<?php //2016/12/11 ADD STR ?>
<div class="result_div">

<p><font color="#ED1F27">[PR]</font> <a href="http://www.ad8.co.jp/sks/" onmouseover="color='#0000FF'" onmouseout="color='#111111'" target="_blank">サイト売買ならココ。相場より高い値段で査定。他の売買サイトとの比較OK。</a></p><br>

<!-- 広告 -->
<div class="boxA">
    <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <ins class="adsbygoogle"
         style="display:block"
         data-ad-format="autorelaxed"
         data-ad-client="ca-pub-0338168428759491"
         data-ad-slot="1920105154"></ins>
    <script>
         (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
</div>
</div>
<?php //2016/12/11 ADD END ?>


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