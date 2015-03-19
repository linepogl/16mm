<?php
require('_.php');
Oxygen::Go();
?>
<!DOCTYPE html>
<html><head>
	<?= Oxygen::GetHead('mmx/res/_.css','mmx/res/_.js') ?>
	<title><?= new Html(Oxygen::GetAction()->GetTitle()); ?></title>
</head>
<body class="<?php echo Browser::GetCssClasses(); ?>">

<?= Oxygen::GetContent() ?>

</body></html>

