<html>

	<head>
		<title></title>
		<?= $this->HTML->Stylesheet("screen"); ?>
		<?php $this->Yield("head"); ?>
	</head>
	<body>
		<?php $this->Yield(); ?>
	</body>
</html>