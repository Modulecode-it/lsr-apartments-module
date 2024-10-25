<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Адаптация компонента bitrix:main.pagenavigation
 * для показа постраничной навигации в режиме без перезагрузки страницы
 */
class ApartmentsPageNavigationComponent extends CBitrixComponent
{
	public function onPrepareComponentParams($arParams)
	{
		$arParams["PAGE_WINDOW"] =
			isset($arParams["PAGE_WINDOW"]) && (int)$arParams["PAGE_WINDOW"] > 0 ? (int)$arParams["PAGE_WINDOW"] : 5
		;

		return $arParams;
	}

	public function executeComponent()
	{
		if (!is_object($this->arParams["~NAV_OBJECT"]) || !($this->arParams["~NAV_OBJECT"] instanceof \Bitrix\Main\UI\PageNavigation))
		{
			return;
		}
		/** @var \Bitrix\Main\UI\PageNavigation $nav */
		$nav = $this->arParams["~NAV_OBJECT"];

		$this->arResult["PAGE_COUNT"] = $nav->getPageCount();
		$this->arResult["CURRENT_PAGE"] = $nav->getCurrentPage();

		$this->calculatePages();

		$this->IncludeComponentTemplate();
	}

	protected function calculatePages()
	{
		if ($this->arResult["CURRENT_PAGE"] > floor($this->arParams["PAGE_WINDOW"]/2) + 1 && $this->arResult["PAGE_COUNT"] > $this->arParams["PAGE_WINDOW"])
		{
			$startPage = $this->arResult["CURRENT_PAGE"] - floor($this->arParams["PAGE_WINDOW"]/2);
		}
		else
		{
			$startPage = 1;
		}

		if ($this->arResult["CURRENT_PAGE"] <= $this->arResult["PAGE_COUNT"] - floor($this->arParams["PAGE_WINDOW"]/2) && $startPage + $this->arParams["PAGE_WINDOW"]-1 <= $this->arResult["PAGE_COUNT"])
		{
			$endPage = $startPage + $this->arParams["PAGE_WINDOW"] - 1;
		}
		else
		{
			$endPage = $this->arResult["PAGE_COUNT"];
			if($endPage - $this->arParams["PAGE_WINDOW"] + 1 >= 1)
			{
				$startPage = $endPage - $this->arParams["PAGE_WINDOW"] + 1;
			}
		}

		$this->arResult["START_PAGE"] = $startPage;
		$this->arResult["END_PAGE"] = $endPage;
	}
}
