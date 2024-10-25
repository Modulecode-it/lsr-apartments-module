<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */

/** @var ApartmentsPageNavigationComponent $component */
$component = $this->getComponent();

$this->setFrameMode(true);
?>

<div class="bx-pagination">
	<div class="bx-pagination-container">
		<ul>
			<?if ($arResult["CURRENT_PAGE"] > 1):?>
				<?if ($arResult["CURRENT_PAGE"] > 2):?>
					<li class="bx-pag-prev"><button data-page="<?= $arResult["CURRENT_PAGE"]-1 ?>"><span><?echo GetMessage("round_nav_back")?></span></button></li>
				<?else:?>
					<li class="bx-pag-prev"><button data-page="1"><span><?echo GetMessage("round_nav_back")?></span></button></li>
				<?endif?>
					<li class=""><button data-page="1"><span>1</span></button></li>
			<?else:?>
					<li class="bx-pag-prev"><span><?echo GetMessage("round_nav_back")?></span></li>
					<li class="bx-active"><span>1</span></li>
			<?endif?>

			<?
			$page = $arResult["START_PAGE"] + 1;
			while($page <= $arResult["END_PAGE"]-1):
			?>
				<?if ($page == $arResult["CURRENT_PAGE"]):?>
					<li class="bx-active"><span><?=$page?></span></li>
				<?else:?>
					<li class=""><button data-page="<?= $page ?>"><span><?=$page?></span></button></li>
				<?endif?>
				<?$page++?>
			<?endwhile?>

			<?if($arResult["CURRENT_PAGE"] < $arResult["PAGE_COUNT"]):?>
				<?if($arResult["PAGE_COUNT"] > 1):?>
					<li class=""><button data-page="<?= $arResult["PAGE_COUNT"] ?>"><span><?=$arResult["PAGE_COUNT"]?></span></button></li>
				<?endif?>
					<li class="bx-pag-next"><button data-page="<?= $arResult["CURRENT_PAGE"]+1 ?>"><span><?echo GetMessage("round_nav_forward")?></span></button></li>
			<?else:?>
				<?if($arResult["PAGE_COUNT"] > 1):?>
					<li class="bx-active"><span><?=$arResult["PAGE_COUNT"]?></span></li>
				<?endif?>
					<li class="bx-pag-next"><span><?echo GetMessage("round_nav_forward")?></span></li>
			<?endif?>

		</ul>
		<div style="clear:both"></div>
	</div>
</div>
