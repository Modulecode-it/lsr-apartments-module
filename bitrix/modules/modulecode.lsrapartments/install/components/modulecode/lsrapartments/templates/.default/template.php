<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/**
 * @var array $arResult
 * @var string $componentPath
 */

?>


<div class="mb-3">
	<h1><?= GetMessage("title") ?></h1>

	<? if($arResult['APARTMENTS']->count() > 0): ?>
		<div class="bg-light p-3 mb-3">
			<h2><?= GetMessage("filter") ?></h2>
			<form action="" method="get" id="js-lsr-apartments-filter">
				<div class="mb-3">
					<label for="house" class="form-label"><?= GetMessage("house") ?></label>
					<select class="form-select" aria-label="Выберите дом" id="house" name="HOUSE">
						<option <?= !$arResult['FORM_DATA']['HOUSE'] ? "selected" : "" ?>><?= GetMessage("anyone") ?></option>
						<?php foreach ($arResult['HOUSES'] as $house): ?>
							<option value="<?= $house->getId() ?>"
								<?= $arResult['FORM_DATA']['HOUSE'] == $house->getId() ? "selected" : "" ?>
							><?= $house->getAddress() ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="mb-3">
					<div class="form-check">
						<input class="form-check-input"
							   type="checkbox"
							   value="1"
							   id="hasSale"
							   name="HAS_SALE"
							<?= $arResult['FORM_DATA']['HAS_SALE'] ? "checked" : "" ?>
						>
						<label class="form-check-label" for="hasSale">
							<?= GetMessage("sale_checkbox") ?>
						</label>
					</div>
				</div>
			</form>
		</div>
	<? endif; ?>
	<div id="js-lsr-apartments-list">
		<?php require 'ajax.php' ?>
	</div>
</div>

<script>
	let lsrApartments = {
		ajaxUrl: "<?= $componentPath ?>/ajax.php",
		pageUrl: "<?= $arResult['PAGE_URL'] ?>",
	};
</script>
