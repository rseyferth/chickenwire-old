<?php $this->BeginContentFor("head"); ?>

	<?= $this->HTML->Stylesheet("screen"); ?>
	<?= $this->HTML->Stylesheet("http://www.google.com/style.css"); ?>

<?php $this->EndContentFor(); ?>

We have <b><?php echo(count($this->hours)); ?></b> hours.<br />
<br />
<ul>
<?php

	foreach($this->hours as $hour) {

		?><li><?php echo ($hour->date . ": " . $hour->nr_hours); ?> (<?= $hour->description ?>)</li><?php

	}

?>
</ul>