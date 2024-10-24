<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

global $APPLICATION;

/**
 * @var array $arResult
 * @var \Bitrix\Main\ORM\Objectify\Collection $collection
 */
$collection = $arResult['COLLECTION'];

?>

<h1>Квартиры в продаже</h1>

<?php if($collection->count()): ?>
	<?php /** @var Bitrix\Main\ORM\Entity $item  */ ?>
	<?php foreach ($collection as $item): ?>
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
	<div class="alert alert-warning" role="alert">
		Все квартиры раскупили. Но мы уже строим новые!
	</div>
<?php endif; ?>


