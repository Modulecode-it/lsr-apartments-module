<?php
use Bitrix\Main\Application;
use Bitrix\Main\Page;
use Bitrix\Main\Config;

if (!$classToEdit || !$backurl || !$tabName || !$tabName) {
	throw new \Exception('предполагается, что для вызова должны быть заданы переменные');
}

//проверка доступа. кому можно? только админам? не сказано
//if (!$GLOBALS['USER']->IsAdmin()) {
//	$APPLICATION->AuthForm("Доступ запрещен.");
//}

$instance = Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();
$server = $context->getServer();
$lang = $context->getLanguage();
$pageTitle = 'Редактирование элемента: '. $tabName;

$id = (int)$request->get('ID');

$elementToEdit = array();
$structureToEdit = [];
foreach ($classToEdit::getMap() as $tableField) {
	$structureToEdit[] = [
		'CODE' => $tableField->getColumnName(),
		'IS_PRIMARY' => $tableField->isPrimary(),
		'IS_REQUIRED' => $tableField->isRequired(),
		'TYPE' => $tableField::class
	];
}

$errorMessage = '';

if ($server->getRequestMethod() == "POST"
	&& ($request->get('save') !== null || $request->get('apply') !== null)
	&& check_bitrix_sessid()
){
	$elementToEdit = [];
	foreach ($structureToEdit as $structureElement) {
		$elementToEdit[$structureElement['CODE']] = $request->getPost($structureElement['CODE']);
	}

	//todo валидация?

	if ($id > 0) {
		$result = $classToEdit::update($id, $elementToEdit);
	} else {
		$result = $classToEdit::add($elementToEdit);
		$id = $result->getId();
	}

	if (!$result->isSuccess()) {
		$errorMessage .= implode("\n", $result->getErrorMessages());
		echo $errorMessage;
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$APPLICATION->SetTitle(($id > 0) ? $pageTitle . ' № ' . $id : $pageTitle);

$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => $tabName,
		"ICON" => "sale",
		"TITLE" => $tabTitle,
	)
);

if ($id > 0 && !$request->isPost()) {
	$res = $classToEdit::getList(array('filter' => array('ID' => $id)));
	$elementToEdit = $res->fetch();
}

$tabControl = new CAdminForm("tabControl", $aTabs);

$aMenu = array(
	array(
		"TEXT" => 'Назад',
		"LINK" => $backurl,
		"ICON" => "btn_list"
	)
);

$contextMenu = new CAdminContextMenu($aMenu);
$contextMenu->Show();

$tabControl->BeginEpilogContent();
echo GetFilterHiddens("filter_");
echo bitrix_sessid_post();
?>

	<input type="hidden" name="Update" value="Y">
	<input type="hidden" name="lang" value="<?=$context->getLanguage();?>">
	<input type="hidden" name="ID" value="<?=$id;?>" id="ID">

<?
$tabControl->EndEpilogContent();
$tabControl->Begin(array("FORM_ACTION" => $APPLICATION->GetCurPage()."?ID=".$id."&lang=".$lang));
$tabControl->BeginNextFormTab();

foreach ($structureToEdit as $structureElement) {
	$elementValue = $elementToEdit[$structureElement['CODE']];

	if ($structureElement['IS_PRIMARY']) {
		$tabControl->AddViewField($structureElement['CODE'], $structureElement['CODE'].':', $elementValue);
	} else {
		switch ($structureElement['TYPE']) {
			case 'Bitrix\Main\ORM\Fields\StringField':
				$tabControl->AddEditField($structureElement['CODE'], $structureElement['CODE'] . ':', $structureElement['IS_REQUIRED'], array('SIZE' => 40), $elementValue);
				break;
			case 'Bitrix\Main\ORM\Fields\BooleanField':
				$tabControl->AddCheckBoxField($structureElement['CODE'], $structureElement['CODE'] . ':', $structureElement['IS_REQUIRED'], 'Y', $elementValue);
				break;
		}
	}
}

$tabControl->Buttons(
	array(
		"disabled" => false,
		"back_url" => $backurl
	)
);

$tabControl->Show();