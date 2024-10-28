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

<?php if($apartments->count()): ?>
	<div>
		<?php /** @var Bitrix\Main\ORM\Entity $item  */ ?>
		<?php foreach ($apartments as $item): ?>
			<div class="mb-3">
				<h4><?= GetMessage("apartment_number") ?> <?= $item->getNumber() ?></h4>
				<? if($item->getImages()->count() > 0): ?>
					<div>
						<?php foreach ($item->getImages() as $image): ?>
							<?= \CFile::ShowFile($image->getFileId(), 0, 100, 100) ?>
						<?php endforeach; ?>
					</div>
				<? endif; ?>
				<div><?= GetMessage("price") ?>: <?= number_format($item->getPrice(), 0, '.', ' ') ?> руб.</div>
				<? if($item->getSalePrice()): ?>
					<div><?= GetMessage("sale_price") ?>: <?= number_format($item->getSalePrice(), 0, '.', ' ') ?> руб.</div>
				<? endif; ?>
				<div><?= GetMessage("address") ?>: <?= $item->getHouse()->getAddress() ?></div>
			</div>
		<?php endforeach; ?>
	</div>
	<div>
		<?php $APPLICATION->IncludeComponent(
			'modulecode:lsrapartments.pagenavigation',
			'',
			['NAV_OBJECT' => $arResult['NAV'],],
			$component
		); ?>
	</div>
<?php else:  ?>
	<div class="alert alert-warning mt-3" role="alert">
		Все квартиры раскупили. Но мы уже строим новые!
	</div>
<?php endif; ?>

