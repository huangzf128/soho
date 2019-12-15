<?php
	if(!isset($_GET["param"])){
		echo "";
	}else{
?>
<html>
<head>

<style type="text/css">
	.tool {
		width: 85%;
		margin: 20px auto 10px;
		border-collapse: collapse;
		font-size: 90%;
		line-height: 1.6em;
		border: 1px solid #0167CC;
		border-radius: 5px;
	}
	.tool ul {
		margin-top: 0px;
		padding-left: 20px;
		list-style: none;
	}
	.tool caption {
		padding-top: 10px;
		padding-bottom: 7px;
		font-weight: bold;
		line-height: 1.5em;
		color: #000;
		background-color: #FEE299;
		border-bottom: 1px solid #0167CC;
		border-radius: 5px 5px 0 0;
	}
	.tool th {
		width: 395px;
		padding-top: 10px;
		text-align: left;
		padding-left: 20px;
	}
	.tool td {
		padding-top: 0px;
		padding-bottom: 10px;
	}
	.tool a {
		text-decoration: none;
	}
/*---toolレスポンシブ----*/
@media (min-width:481px) and (max-width:800px){
	.tool th {
		width: 50%;
	}
	.tool td {
		width: 50%;
	}
}
@media (max-width : 480px){
	.tool {
		width: 98%;
	}
	.tool th {
		width: 50%;
	}	
}
</style>

</head>
<body>

<div class="tool" >
	<table style="font-size:90%;line-height: 1.6em;">
		<caption>あなたにおススメの「サイト」＆「ツール」をご紹介</caption>
		<thead>
			<tr>
				<th>■SEO関連</th>
				<th>■キーワード関連</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<ul>
						<li><a href="http://www.y-seo.net/" target="_blank">1回5000円or月額1000円からの「安いSEO対策サービス」</a></li>
						<li><script type="text/javascript" src="http://www.infotop.jp/click.js"></script>
<a href="http://www.web-f.net/seoplus/" onClick="return clickCount(130546, 15722);">たった43ページの本物ＳＥＯマニュアル</a></li>
						<li><script type="text/javascript" src="http://www.infotop.jp/click.js"></script>
<a href="http://az.ctwpromotion.net/aff-compass/lp/kjag1p798.html" onClick="return clickCount(130546, 65474);">アフィリエイター専用SEO分析ツール「COMPASS」</a></li>
						<li><script type="text/javascript" src="http://www.infotop.jp/click.js"></script>
<a href="http://www.seo-keni.jp/" onClick="return clickCount(130546, 2058);">SEOに強い戦略的テンプレート「賢威7」</a>
</li>
						<li><a href="http://www.seo10.net/" target="_blank">目標順位を選べる成果報酬型「SEOファースト」</a></li>
					</ul>
				</td>
				<td>
					<ul>
						<li><script type="text/javascript" src="http://www.infotop.jp/click.js"></script>
<a href="http://az.ctwpromotion.net/pandora2/infotop.html" onClick="return clickCount(130546, 63824);">5000本を突破したキーワードツールの決定版「Pandora2」</a></li>
						<li><script type="text/javascript" src="http://www.infotop.jp/click.js"></script>
<a href="http://zqdle.net/prek/" onClick="return clickCount(130546, 62391);">お宝キーワードの発掘は「プレシャスキーワード」</a></li>
						<li><script type="text/javascript" src="http://www.infotop.jp/click.js"></script>
<a href="http://keywordstrike.com/" onClick="return clickCount(130546, 62776);">キーワード選定マニュアル「キーワードストライク」</a></li>
						<li><script type="text/javascript" src="http://www.infotop.jp/click.js"></script>
<a href="http://az.ctwpromotion.net/amc/keyword/top.html" onClick="return clickCount(130546, 60881);">ライバル皆無キーワードを発掘「キーワードスカウターS」</a></li>
						<li><a href="http://www.kwkt.net/" target="_blank">検索結果をそのまま保存「キーワード検索結果登録ツール」</a></li>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>
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