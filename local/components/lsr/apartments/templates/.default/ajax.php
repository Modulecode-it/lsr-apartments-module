<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 24.10.2024
 * Time: 17:38
 */

global $APPLICATION;

/**
 * @var $component
 * @var string $componentPath
 * @var array $arParams
 * @var array $arResult
 * @var \Bitrix\Main\ORM\Objectify\Collection $apartments
 */
$apartments = $arResult['APARTMENTS'];

?>

<div class="bg-light p-3 mb-3">
	<h2>Фильтр</h2>
	<form action="" method="get" id="js-filter-form">
		<div class="mb-3">
			<label for="house" class="form-label">Дом</label>
			<select class="form-select" aria-label="Выберите дом" id="house" name="HOUSE">
				<option <?= !$arResult['FORM_DATA']['HOUSE'] ? "selected" : "" ?>>Выберите дом</option>
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
					Есть скидка
				</label>
			</div>
		</div>
		<button type="submit" class="btn btn-primary">Submit</button>
	</form>
</div>

<?php if($apartments->count()): ?>
	<div>
		<?php /** @var Bitrix\Main\ORM\Entity $item  */ ?>
		<?php foreach ($apartments as $item): ?>
			<div class="mb-3">
				<h4>Квартира № <?= $item->getNumber() ?></h4>
				<? if($item->getImages()->count() > 0): ?>
					<div>
						<?php foreach ($item->getImages() as $image): ?>
							<?= \CFile::ShowFile($image->getFileId(), 0, 100, 100) ?>
						<?php endforeach; ?>
					</div>
				<? endif; ?>
				<div>Стоимость: <?= number_format($item->getPrice(), 0, '.', ' ') ?> руб.</div>
				<? if($item->getSalePrice()): ?>
					<div>Стоимость со скидкой: <?= number_format($item->getSalePrice(), 0, '.', ' ') ?> руб.</div>
				<? endif; ?>
				<div>Адрес: <?= $item->getHouse()->getAddress() ?></div>
			</div>
		<?php endforeach; ?>
	</div>
	<div>
		<?php $APPLICATION->IncludeComponent(
			'lsr:apartments.pagenavigation',
			'',
			array(
				'NAV_OBJECT' => $arResult['NAV'],
				'SEF_MODE' => 'N',
			),
			$component
		); ?>
	</div>
<?php else:  ?>
	<div class="alert alert-warning mt-3" role="alert">
		Все квартиры раскупили. Но мы уже строим новые!
	</div>
<?php endif; ?>

