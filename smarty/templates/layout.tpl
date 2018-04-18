<!DOCTYPE HTML>
<html>
	<head>
		<link rel="stylesheet" property="stylesheet" href="{block name=stylesheet}css/style.css{/block}">
		<title>{block name=title}Default Page Title{/block}</title>
	</head>
	<body>
	<div id="heading">
		<h1><a href="{block name=hl_back}index.php{/block}">{block name=heading}Default Heading{/block}</a></h1>
		<h2><a href="{block name=hl_back}index.php{/block}">{block name=subheading}Default Subheading{/block}</a></h2>
		<h3>{block name=subheading2}Default Subheading2{/block}</h3>	
	</div>
	{block name=body}{/block}
	</body>
</html>

{* vim: set ts=2 sw=2 :*}

