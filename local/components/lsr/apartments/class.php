<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Компонент списка квартир с фильтрацией
 */
class CLsrApartmentsComponent extends CBitrixComponent
{
	public function executeComponent()
	{
		$this->IncludeComponentTemplate();
	}
}