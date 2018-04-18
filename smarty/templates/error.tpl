<!DOCTYPE HTML>
<html>
	<head>
		<link rel="stylesheet" property="stylesheet" href="{block name=stylesheet}css/style.css{/block}">
		<title>Not Authorized</title>
	</head>
	<body>
		<p>User not authorized: {$pawprint}</p>
		<p><a href="mailto:{$webmaster_contact}?Subject=Darklib%20whitelist" target="_top">Contact webmaster</a> to be added to the whitelist.</p>
	</body>
</html>

{* vim: set ts=2 sw=2 :*}

