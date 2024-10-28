<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!$classToList || !$tableId || !$titleForList || !$editPhpUrl) {
	throw new \Exception('предполагается, что для вызова должны быть заданы переменные');
}

//проверка доступа. кому можно? только админам? не сказано
//if (!$GLOBALS['USER']->IsAdmin()) {
//	$APPLICATION->AuthForm("Доступ запрещен.");
//}

$instance = \Bitrix\Main\Application::getInstance();
$context = $instance->getContext();
$lang = $context->getLanguage();
$request = $context->getRequest();

$oSort = new CAdminSorting($tableId, "ID", "asc");
$lAdmin = new CAdminList($tableId, $oSort);

$arFilterFields = array(
	"filter_active",
);
if ($classToList == 'Modulecode\Lsrapartments\Model\ApartmentTable') {
	$arFilterFields[] = 'HOUSE_ID';
}

$lAdmin->InitFilter($arFilterFields);

$filter = array();

if (strlen($filter_active) > 0 && $filter_active != "NOT_REF")
	$filter["ACTIVE"] = trim($filter_active);
if ($classToList == 'Modulecode\Lsrapartments\Model\ApartmentTable' && $_GET['HOUSE_ID'])
	$filter["HOUSE_ID"] = (int)$_GET['HOUSE_ID'];

if ($request->get('action_button') == 'delete' && $request->get('ID')) {
	$id = $request->get('ID');
	$result = $classToList::delete($id);
	if (!$result->isSuccess()) {
		if ($result->getErrorMessages())
			$lAdmin->AddGroupError(join(', ', $result->getErrorMessages()), $id);
		else
			$lAdmin->AddGroupError(GetMessage("ITEM_DELETE_FAILED"), $id);
	}
}

$navyParams = array();
$params = array(
	'select' => array('*'),
	'filter' => $filter
);
if (isset($by)) {
	$order = isset($order) ? $order : "ASC";
	$params['order'] = array($by => $order);
}
$navyParams = CDBResult::GetNavParams(CAdminResult::GetNavSize($tableId));
if ($navyParams['SHOW_ALL']) {
	$usePageNavigation = false;
} else {
	$navyParams['PAGEN'] = (int)$navyParams['PAGEN'];
	$navyParams['SIZEN'] = (int)$navyParams['SIZEN'];
}

if ($usePageNavigation) {
	$params['limit'] = $navyParams['SIZEN'];
	$params['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
}

$totalPages = 0;

if ($usePageNavigation) {
	$countQuery = new \Bitrix\Main\Entity\Query($classToList::getEntity());
	$countQuery->addSelect(new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(1)'));
	$countQuery->setFilter($params['filter']);

	foreach ($params['runtime'] as $key => $field)
		$countQuery->registerRuntimeField($key, clone $field);

	$totalCount = $countQuery->setLimit(null)->setOffset(null)->exec()->fetch();
	unset($countQuery);
	$totalCount = (int)$totalCount['CNT'];

	if ($totalCount > 0) {
		$totalPages = ceil($totalCount/$navyParams['SIZEN']);

		if ($navyParams['PAGEN'] > $totalPages)
			$navyParams['PAGEN'] = $totalPages;

		$params['limit'] = $navyParams['SIZEN'];
		$params['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
	} else {
		$navyParams['PAGEN'] = 1;
		$params['limit'] = $navyParams['SIZEN'];
		$params['offset'] = 0;
	}
}

$dbResultList = new CAdminResult($classToList::getList($params), $tableId);

if ($usePageNavigation) {
	$dbResultList->NavStart($params['limit'], $navyParams['SHOW_ALL'], $navyParams['PAGEN']);
	$dbResultList->NavRecordCount = $totalCount;
	$dbResultList->NavPageCount = $totalPages;
	$dbResultList->NavPageNomer = $navyParams['PAGEN'];
} else {
	$dbResultList->NavStart();
}


$headers = [];
$translationMaps = [];
foreach ($classToList::getMap() as $tableField) {
	if ($tableField::class == 'Bitrix\Main\ORM\Fields\Relations\Reference'
		|| $tableField::class == 'Bitrix\Main\ORM\Fields\Relations\OneToMany'
	) {
		continue;
	}
	if ($tableField::class == 'Bitrix\Main\ORM\Fields\EnumField') {
		$valuesMap = $tableField->getValues();
		$translationMaps[$tableField->getColumnName()] = array_flip($valuesMap);
	}
	if ($tableField::class == 'Bitrix\Main\ORM\Fields\BooleanField') {
		$booleanFieldMap = $tableField->getValues();
		foreach ($booleanFieldMap as $booleanFieldMapKey => $booleanFieldValue) {
			if ($booleanFieldValue == 'Y') {
				$booleanFieldMap[$booleanFieldValue] = GetMessage('MODULECODE_LSRAPARTMENTS_YES');
			}
			if ($booleanFieldValue == 'N') {
				$booleanFieldMap[$booleanFieldValue] = GetMessage('MODULECODE_LSRAPARTMENTS_NO');
			}
		}
		$translationMaps[$tableField->getColumnName()] = $booleanFieldMap;
	}
	$columName = $tableField->getColumnName();
	$title = $tableField->getTitle();
	$headers[] = [
		"id" => $columName,
		"content" => $title,
		"sort" => $columName,
		"default" => true
	];
}

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("group_admin_nav")));

$lAdmin->AddHeaders($headers);

$visibleHeaders = $lAdmin->GetVisibleHeaderColumns();

while ($cursor = $dbResultList->Fetch()) {
	foreach ($translationMaps as $translationKey => $translationValue) {
		if ($cursor[$translationKey]) {
			$cursor[$translationKey] = $translationValue[$cursor[$translationKey]];
		}
	}

	$row =& $lAdmin->AddRow($cursor['ID'], $cursor, $editPhpUrl."?ID=".$cursor['ID']."&lang=".LANG, GetMessage("EDIT_ITEM"));

	$row->AddField("ID", "<a href=\"".$editPhpUrl."?ID=".$cursor['ID']."&lang=".LANG."\">".$cursor['ID']."</a>");
	if ($cursor['HOUSE_ID']) {
		$houseEditUrl = '/bitrix/admin/lsr_houses_edit.php?ID='.$cursor['HOUSE_ID'];
		$row->AddField("HOUSE_ID", '<a href="' . $houseEditUrl . '">' . $cursor['HOUSE_ID'] . "</a>");
	}

	$arActions = [
		[
			"ICON" => "edit",
			"TEXT" => GetMessage("EDIT_TEXT"),
			"TITLE" => GetMessage("EDIT_TITLE"),
			"ACTION" => $lAdmin->ActionRedirect($editPhpUrl."?ID=".$cursor['ID']."&lang=".$context->getLanguage()),
			"DEFAULT" => true,
		],
	];

	if ($classToList == 'Modulecode\Lsrapartments\Model\HouseTable') {
		$urlToLinkedApartments = "/bitrix/admin/lsr_apartments_list.php?HOUSE_ID=".$cursor['ID'];
		$arActions[] = [
			"TEXT" => GetMessage("FIND_LINKED_APARTMENTS_TEXT"),
			"TITLE" => GetMessage("FIND_LINKED_APARTMENTS_TITLE"),
			"LINK" => $urlToLinkedApartments,
		];
	}
	$arActions[] = ["SEPARATOR" => true];
	$arActions[] = [
		"ICON" => "delete",
		"TEXT" => GetMessage("DELETE_TEXT"),
		"TITLE" => GetMessage("DELETE_TITLE"),
		"ACTION" => "if(confirm('".GetMessage("DELETE_CONFIRM")."')) ".$lAdmin->ActionDoGroup($cursor['ID'], "delete"),
	];

	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("SELECTED_TITLE"),
			"value" => $dbResultList->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => GetMessage("MARKED_TITLE"),
			"value" => "0"
		),
	)
);


$aContext = array(
	array(
		"TEXT" => GetMessage("ADD_TEXT"),
		"TITLE" => GetMessage("ADD_TITLE"),
		"LINK" => $editPhpUrl,
		"ICON" => "btn_new",
	)
);
$lAdmin->AddAdminContextMenu($aContext);


$lAdmin->CheckListMode();

$APPLICATION->SetTitle($titleForList);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
	<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
		<?
		$oFilter = new CAdminFilter(
			$tableId."_filter",
			array()
		);

		$oFilter->Begin();
		?>
		<tr>
			<td><?=GetMessage("ACTIVE")?></td>
			<td>
				<select name="filter_active">
					<option value="NOT_REF">(Все)</option>
					<option value="Y"<?if ($filter_active=="Y") echo " selected"?>>Да</option>
					<option value="N"<?if ($filter_active=="N") echo " selected"?>>Нет</option>
				</select>
			</td>
		</tr>
		<?
		if ($classToList == 'Modulecode\Lsrapartments\Model\ApartmentTable') {
			?>
			<tr>
				<td><?=GetMessage("HOUSE")?></td>
				<td>
					<input name="HOUSE_ID" type="text" value="<?=(int)$_GET['HOUSE_ID'] ?: ''?>">
				</td>
			</tr>
			<?
		}

		$oFilter->Buttons(
			array(
				"table_id" => $tableId,
				"url" => $APPLICATION->GetCurPage(),
				"form" => "find_form"
			)
		);
		$oFilter->End();
		?>
	</form>
<?

$lAdmin->DisplayList();
