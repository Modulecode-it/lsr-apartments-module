<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

global $APPLICATION;

/**
 * @var array $arResult
 * @var \Bitrix\Main\ORM\Objectify\Collection $apartments
 */
$apartments = $arResult['APARTMENTS'];

?>

<h1>Квартиры в продаже</h1>

<div>
	<h2>Фильтр</h2>
	<form action="">
		<div class="mb-3">
			<label for="house" class="form-label">Дом</label>
			<select class="form-select" aria-label="Выберите дом" id="house">
				<option selected>Выберите дом</option>
				<?php foreach ($arResult['HOUSES'] as $house): ?>
					<option value="<?= $house->getId() ?>"><?= $house->getAddress() ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="form-check">
			<input class="form-check-input" type="checkbox" value="" id="hasSale">
			<label class="form-check-label" for="hasSale">
				Есть скидка
			</label>
		</div>
	</form>
</div>

<?php if($apartments->count()): ?>
	<?php /** @var Bitrix\Main\ORM\Entity $item  */ ?>
	<?php foreach ($apartments as $item): ?>
		<div>
			<h4>Квартира № <?= $item->getNumber() ?></h4>
			<? if($item->getImages()->count() > 0): ?>
				<div>
					<?php foreach ($item->getImages() as $image): ?>
						<?= \CFile::ShowFile($image->getFileId(), 0, 100, 100) ?>
					<?php endforeach; ?>
				</div>
			<? endif; ?>
			<div>Стоимость: <?= $item->getPrice() ?></div>
			<? if($item->getSalePrice()): ?>
				<div>Стоимость со скидкой: <?= $item->getSalePrice() ?></div>
			<? endif; ?>
			<div>Адрес: <?= $item->getHouse()->getAddress() ?></div>
		</div>
	<?php endforeach; ?>

	<?php $APPLICATION->IncludeComponent(
		'bitrix:main.pagenavigation',
		'',
		array(
			'NAV_OBJECT' => $arResult['NAV'],
			'SEF_MODE' => 'N',
		),
		$component
	); ?>
<?php else:  ?>
	<div class="alert alert-warning mt-3" role="alert">
		Все квартиры раскупили. Но мы уже строим новые!
	</div>
<?php endif; ?>


