<?php
require('_.php');
Oxygen::Go();
?>
<!DOCTYPE html>
<html><head>
	<?= Oxygen::GetHead('mmx.css') ?>
	<?php foreach (Fs::Browse('jsc','*.js') as $f) echo Js::GetLink("jsc/$f"); ?>
	<title>MMX</title>
</head>
<body class="<?php echo Browser::GetCssClasses(); ?>">


<div id="mmx-bar">
	<div id="mmx-tab-1" class="mmx-tab active" onclick="window.MMX.ShowTab(1)">
		<?= mmm::icoHome() ?>Home
	</div>
	<div id="mmx-tab-2" class="mmx-tab" onclick="window.MMX.ShowTab(2)">
		<?= mmm::icoPerson() ?>People
	</div>
	<div id="mmx-tab-3" class="mmx-tab" onclick="window.MMX.ShowTab(3)">
		<?= mmm::icoProduction() ?>Titles
	</div>
</div>

<div id="mmx-side">

	<div id="mmx-tab-page-1" class="mmx-tab-page">


		<div class="mmx-subtitle">Search</div>
		<div id="mmx-category-search" class="mmx-category active" onclick="window.MMX.ShowCategory('search')">
			<?= TextBox::Make('searchstring')->WithWidth('207px') ?>&nbsp;<?= ButtonBox::Make()->WithIsRich(true)->WithValue(oxy::icoSearchGlass()) ?>
		</div>

		<div class="mmx-subtitle">
			<?//= mmm::icoPerson() ?>
			People
		</div>
		<div id="mmx-category-people-1" class="mmx-category mmx-color-1" onclick="window.MMX.ShowCategory('people-1')"><div class="mmx-number">0</div>
			<?= mmm::icoRatingBest() ?>
			Favorite
		</div>
		<div id="mmx-category-people-2" class="mmx-category mmx-color-2" onclick="window.MMX.ShowCategory('people-2')"><div class="mmx-number">2</div>
			<?= mmm::icoRatingOkay() ?>
			Okay
		</div>
		<div id="mmx-category-people-3" class="mmx-category mmx-color-3" onclick="window.MMX.ShowCategory('people-3')"><div class="mmx-number">412</div>
			<?= mmm::icoRatingSoSo() ?>
			So and so
		</div>
		<div id="mmx-category-people-4" class="mmx-category mmx-color-4" onclick="window.MMX.ShowCategory('people-4')"><div class="mmx-number">23</div>
			<?= mmm::icoRatingFail() ?>
			Awful
		</div>

		<div class="mmx-subtitle">
			<?//= mmm::icoProduction() ?>
			Titles
		</div>
		<div id="mmx-category-productions-1" class="mmx-category mmx-color-1" onclick="window.MMX.ShowCategory('productions-1')"><div class="mmx-number">0</div>
			<?= mmm::icoRatingBest() ?>
			Favorite
		</div>
		<div id="mmx-category-productions-2" class="mmx-category mmx-color-2" onclick="window.MMX.ShowCategory('productions-2')"><div class="mmx-number">0</div>
			<?= mmm::icoRatingOkay() ?>
			Okay
		</div>
		<div id="mmx-category-productions-3" class="mmx-category mmx-color-3" onclick="window.MMX.ShowCategory('productions-3')"><div class="mmx-number">0</div>
			<?= mmm::icoRatingSoSo() ?>
			So and so
		</div>
		<div id="mmx-category-productions-4" class="mmx-category mmx-color-4" onclick="window.MMX.ShowCategory('productions-4')"><div class="mmx-number">0</div>
			<?= mmm::icoRatingFail() ?>
			Awful
		</div>
		<div id="mmx-category-productions-5" class="mmx-category mmx-color-5" onclick="window.MMX.ShowCategory('productions-5')"><div class="mmx-number">0</div>
			<?= mmm::icoRatingHalf() ?>
			Partially seen
		</div>
		<div id="mmx-category-productions-6" class="mmx-category mmx-color-6" onclick="window.MMX.ShowCategory('productions-6')"><div class="mmx-number">0</div>
			<?= mmm::icoTarget() ?>
			Watch list
		</div>
	</div>
	<div id="mmx-tab-page-2" class="mmx-tab-page" style="display:none;">
2
	</div>
	<div id="mmx-tab-page-3" class="mmx-tab-page"  style="display:none;">
3
	</div>
</div>

<div id="mmx-main">
<?= Oxygen::GetContent() ?>
</div>

</body></html>

