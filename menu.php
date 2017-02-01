<div id="topnav">
<ul>
<?php echo in_array($page, array('index.html')) ? '<li id="current">' : '<li>'; ?>
<a href="index.html" shape="rect">主页</a>
</li>
<?php echo in_array($page, array('company.html')) ? '<li id="current">' : '<li>'; ?>
<a href="company.html?submenu=greeting" shape="rect">公司介绍</a>
</li>
<?php echo in_array($page, array('box.html')) ? '<li id="current">' : '<li>'; ?>
<a href="box.html?submenu=aboutbox" shape="rect">波士维纳温疗仓</a>
</li>
<?php echo in_array($page, array('plan.html')) ? '<li id="current">' : '<li>'; ?>
<a href="plan.html?submenu=sell" shape="rect">中国发展方针</a>
</li>
<?php echo in_array($page, array('business.html')) ? '<li id="current">' : '<li>'; ?>
<a href="business.html" shape="rect">美容事业紹介</a>
</li>
<?php echo in_array($page, array('query.html')) ? '<li id="current">' : '<li>'; ?>
<a href="query.html" shape="rect">联络我们</a>
</li>
</ul>
</div>
