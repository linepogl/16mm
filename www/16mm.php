<?php
require('_.php');
Oxygen::Go();
?>
<!DOCTYPE html>
<html><head>
	<?= Oxygen::GetHead('16mm.css') ?>
	<?= Js::GetLink("mmx/jsc/mousetrap.js"); ?>
	<?= Js::GetLink("16mm.js"); ?>
	<title>16mm</title>
</head>
<body class="<?php echo Browser::GetCssClasses(); ?>">




<div id="mmx-bar-people" class="mmx-bar">
	<div id="mmx-tab-people" class="mmx-tab" onclick="window.mmx.ShowTab('people')">
		<?= mmx::icoPeople(); ?>
	</div>
	<div id="mmx-tab-people-1" class="mmx-tab" onclick="window.mmx.ShowTab('people-1')"><?= mmx::icoRatingBest(); ?></div>
	<div id="mmx-tab-people-2" class="mmx-tab" onclick="window.mmx.ShowTab('people-2')"><?= mmx::icoRatingOkay(); ?></div>
	<div id="mmx-tab-people-3" class="mmx-tab" onclick="window.mmx.ShowTab('people-3')"><?= mmx::icoRatingSoSo(); ?></div>
	<div id="mmx-tab-people-4" class="mmx-tab" onclick="window.mmx.ShowTab('people-4')"><?= mmx::icoRatingFail(); ?></div>
</div>

<div id="mmx-bar-titles" class="mmx-bar">
	<div id="mmx-tab-titles" class="mmx-tab" onclick="window.mmx.ShowTab('titles')">
		<?= mmx::icoTitles(); ?>
	</div>
	<div id="mmx-tab-titles-1" class="mmx-tab" onclick="window.mmx.ShowTab('titles-1')"><?= mmx::icoRatingBest(); ?></div>
	<div id="mmx-tab-titles-2" class="mmx-tab" onclick="window.mmx.ShowTab('titles-2')"><?= mmx::icoRatingOkay(); ?></div>
	<div id="mmx-tab-titles-3" class="mmx-tab" onclick="window.mmx.ShowTab('titles-3')"><?= mmx::icoRatingSoSo(); ?></div>
	<div id="mmx-tab-titles-4" class="mmx-tab" onclick="window.mmx.ShowTab('titles-4')"><?= mmx::icoRatingFail(); ?></div>
	<div id="mmx-tab-titles-5" class="mmx-tab" onclick="window.mmx.ShowTab('titles-5')"><?= mmx::icoRatingHalf(); ?></div>
	<div id="mmx-tab-titles-6" class="mmx-tab" onclick="window.mmx.ShowTab('titles-6')"><?= mmx::icoRatingStar(); ?></div>
</div>

<form id="mmx-search" style="display:none;" onsubmit="window.mmx.Search();return false;">
	<div class="mmx-title">16<span class="mm">mm</span></div>
	<div class="mmx-button-wrapper"><?= ButtonBox::Make('searchbutton')->WithIsRich(true)->WithValue(oxy::icoSearchGlass())->WithIsSubmit(true) ?></div>
	<?= TextBox::Make('searchstring') ?>
</form>

<div style="display:none;">
	<div id="mmx-movie-tile-template" class="mmx-movie-tile">
		<div class="title"></div>
		<div class="languages"></div>
		<div class="countries"></div>
		<div class="genres"></div>
	</div>
</div>
<!--


<div id="mmx-bar">
	<div id="mmx-tab-1" class="mmx-tab active" onclick="window.mmx.ShowTab(1)">
		<?= mmx::icoHome() ?>Home
	</div>
	<div id="mmx-tab-2" class="mmx-tab" onclick="window.mmx.ShowTab(2)">
		<?= mmx::icoPerson() ?>People
	</div>
	<div id="mmx-tab-3" class="mmx-tab" onclick="window.mmx.ShowTab(3)">
		<?= mmx::icoProduction() ?>Titles
	</div>
</div>

<div id="mmx-side">

	<div id="mmx-tab-page-1" class="mmx-tab-page">


		<div class="mmx-subtitle">Search</div>
		<div id="mmx-category-search" class="mmx-category active" onclick="window.mmx.ShowCategory('search')">
			<?= TextBox::Make('searchstring')->WithWidth('207px') ?>&nbsp;<?= ButtonBox::Make()->WithIsRich(true)->WithValue(oxy::icoSearchGlass()) ?>
		</div>

		<div class="mmx-subtitle">
			<?//= mmx::icoPerson() ?>
			People
		</div>
		<div id="mmx-category-people-1" class="mmx-category mmx-color-1" onclick="window.mmx.ShowCategory('people-1')"><div class="mmx-number">0</div>
			<?= mmx::icoRatingBest() ?>
			Favorite
		</div>
		<div id="mmx-category-people-2" class="mmx-category mmx-color-2" onclick="window.mmx.ShowCategory('people-2')"><div class="mmx-number">2</div>
			<?= mmx::icoRatingOkay() ?>
			Okay
		</div>
		<div id="mmx-category-people-3" class="mmx-category mmx-color-3" onclick="window.mmx.ShowCategory('people-3')"><div class="mmx-number">412</div>
			<?= mmx::icoRatingSoSo() ?>
			So and so
		</div>
		<div id="mmx-category-people-4" class="mmx-category mmx-color-4" onclick="window.mmx.ShowCategory('people-4')"><div class="mmx-number">23</div>
			<?= mmx::icoRatingFail() ?>
			Awful
		</div>

		<div class="mmx-subtitle">
			<?//= mmx::icoProduction() ?>
			Titles
		</div>
		<div id="mmx-category-productions-1" class="mmx-category mmx-color-1" onclick="window.mmx.ShowCategory('productions-1')"><div class="mmx-number">0</div>
			<?= mmx::icoRatingBest() ?>
			Favorite
		</div>
		<div id="mmx-category-productions-2" class="mmx-category mmx-color-2" onclick="window.mmx.ShowCategory('productions-2')"><div class="mmx-number">0</div>
			<?= mmx::icoRatingOkay() ?>
			Okay
		</div>
		<div id="mmx-category-productions-3" class="mmx-category mmx-color-3" onclick="window.mmx.ShowCategory('productions-3')"><div class="mmx-number">0</div>
			<?= mmx::icoRatingSoSo() ?>
			So and so
		</div>
		<div id="mmx-category-productions-4" class="mmx-category mmx-color-4" onclick="window.mmx.ShowCategory('productions-4')"><div class="mmx-number">0</div>
			<?= mmx::icoRatingFail() ?>
			Awful
		</div>
		<div id="mmx-category-productions-5" class="mmx-category mmx-color-5" onclick="window.mmx.ShowCategory('productions-5')"><div class="mmx-number">0</div>
			<?= mmx::icoRatingHalf() ?>
			Partially seen
		</div>
		<div id="mmx-category-productions-6" class="mmx-category mmx-color-6" onclick="window.mmx.ShowCategory('productions-6')"><div class="mmx-number">0</div>
			<?= mmx::icoTarget() ?>
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
-->


<div id="mmx-main">
<?= Oxygen::GetContent() ?>
</div>

</body></html>

