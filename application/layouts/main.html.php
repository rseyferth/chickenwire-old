<html>

	<head>
		<title></title>
<!--		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">-->
		<?= $this->HTML->Stylesheet("screen"); ?>
		<?php $this->Yield("head"); ?>
	</head>
	<body>
		<?php $this->Yield(); ?>
	</body>
</html>