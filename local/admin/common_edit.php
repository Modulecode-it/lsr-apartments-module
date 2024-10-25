<?php
use Bitrix\Main\Application;
use Bitrix\Main\Page;
use Bitrix\Main\Config;
use Lsr\Service\FileService;

if (!$classToEdit || !$backurl || !$tabName || !$tabName || !$imagesClass) {
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
$classToEditMap = $classToEdit::getMap();
foreach ($classToEditMap as $tableField) {
	if ($tableField::class == 'Bitrix\Main\ORM\Fields\Relations\Reference'
		|| $tableField::class == 'Bitrix\Main\ORM\Fields\Relations\OneToMany'
	) {
		continue;
	}
	$structureToEdit[] = [
		'EXTERNAL' => false,
		'CODE' => $tableField->getColumnName(),
		'IS_PRIMARY' => $tableField->isPrimary(),
		'IS_REQUIRED' => $tableField->isRequired(),
		'TYPE' => $tableField::class
	];
}
//а вдруг если какое-то свойство используется как референс на другой объект.
foreach ($classToEditMap as $tableField) {
	if ($tableField::class == 'Bitrix\Main\ORM\Fields\Relations\Reference') {
		$classToLink = $tableField->getDataType();
		$linkStructure = $tableField->getElementals();
		$codeToSubstitute = array_keys($linkStructure)[0];
		foreach ($structureToEdit as $structureToEditKey=>$structureToEditValue) {
			if ($structureToEditValue['CODE'] == $codeToSubstitute) {
				$structureToEdit[$structureToEditKey]['EXTERNAL'] = true;
				$structureToEdit[$structureToEditKey]['LINK'] = \Lsr\AdminInterface::getLinkToElementEditByClassString($classToLink);
				unset($structureToEdit[$structureToEditKey]['IS_PRIMARY']);
				unset($structureToEdit[$structureToEditKey]['IS_REQUIRED']);
			}
		}
	}
}


//картинки?
foreach ($imagesClass::getMap() as $tableField) {
	if ($tableField::class == 'Bitrix\Main\ORM\Fields\Relations\Reference'
		|| $tableField::class == 'Bitrix\Main\ORM\Fields\Relations\OneToMany'
	) {
		continue;
	}

	$structureToEdit[] = [
		'EXTERNAL' => true,
		'CODE' => $tableField->getColumnName(),
		'IS_PRIMARY' => $tableField->isPrimary(),
		'IS_REQUIRED' => $tableField->isRequired(),
		'TYPE' => $tableField::class,
		'CLASS' => $imagesClass
	];
}

$errorMessage = '';

if ($server->getRequestMethod() == "POST"
	&& ($request->get('save') !== null || $request->get('apply') !== null)
	&& check_bitrix_sessid()
){
	$elementToEdit = [];
	foreach ($structureToEdit as $structureElement) {
		if ($structureElement['CODE'] == 'ID' && !$id) {
			continue;
		} elseif ($structureElement['CODE'] == 'ACTIVE') {
			$elementToEdit[$structureElement['CODE']] = (isset($_POST['ACTIVE']) && 'Y' == $_POST['ACTIVE'] ? 'Y' : 'N');
			continue;
		} else {
			$elementToEdit[$structureElement['CODE']] = $request->getPost($structureElement['CODE']);
		}
	}

	//todo валидация?

	if ($id > 0) {
		$result = $classToEdit::update($id, $elementToEdit);
	} else {
		$result = $classToEdit::add($elementToEdit);
		$id = $result->getId();
		$elementToEdit['ID'] = $id;
	}

	if ($_POST['FILE_ID']) {
		if ($_POST['FILE_ID_del']) {
			foreach ($_POST['FILE_ID_del'] as $keyToDel => $FILE_ID_delValue) {
				$imagesClass::delete($_POST['FILE_ID'][$keyToDel]);
			}
		}
		$element = $classToEdit::getList(array('filter' => array('ID' => $id)));
		foreach ($_POST['FILE_ID'] as $fileEntry) {
			//новый файл?
			if (is_array($fileEntry)) {
				if ($fileEntry['name']) {
					$fileName = $fileEntry['name'];
				}
				if ($fileEntry['tmp_name']) {
					$imageService = new FileService();

					$image = $imagesClass::getEntity()->createObject();
					$image->set($imagesClass::ENTITY_ID, $id);

					$filePath = BX_TEMPORARY_FILES_DIRECTORY . $fileEntry['tmp_name'];
					$fileId = $imageService->saveExistingFileToBFile($filePath, $classToEdit::getTableName(), $fileName);
					$image->set($imagesClass::FILE_ID, $fileId);

					$result = $image->save();

					if (!$result->isSuccess()) {
						throw new \LogicException("Сущность изображения не сохранена. " . join(", ", $result->getErrorMessages()));
					}
				}
			}
		}
	}

	if (!$result->isSuccess()) {
		$errorMessage .= implode("\n", $result->getErrorMessages());
		echo $errorMessage;
	} else {
		if ($request->get('save') !== null) {
			LocalRedirect($backurl);
		}
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
	if (!$structureElement['EXTERNAL']) {
		$elementValue = $elementToEdit[$structureElement['CODE']];

		if ($structureElement['IS_PRIMARY']) {
			$tabControl->AddViewField($structureElement['CODE'], $structureElement['CODE'] . ':', $elementValue);
		} else {
			switch ($structureElement['TYPE']) {
				case 'Bitrix\Main\ORM\Fields\BooleanField':
					$tabControl->AddCheckBoxField($structureElement['CODE'], $structureElement['CODE'] . ':', $structureElement['IS_REQUIRED'], 'Y', $elementValue == "Y");
					break;
				case 'Bitrix\Main\ORM\Fields\StringField':
				case 'Bitrix\Main\ORM\Fields\IntegerField':
				case 'Bitrix\Main\ORM\Fields\FloatField':
					$tabControl->AddEditField($structureElement['CODE'], $structureElement['CODE'] . ':', $structureElement['IS_REQUIRED'], array('SIZE' => 40), $elementValue);
					break;
				case 'Bitrix\Main\ORM\Fields\EnumField':
					$tabControl->AddDropDownField($structureElement['CODE'], $structureElement['CODE'] . ':', $structureElement['IS_REQUIRED'], [
						\Lsr\Model\ApartmentTable::STATUS_SALE => \Lsr\Model\ApartmentTable::STATUS_SALE,
						\Lsr\Model\ApartmentTable::STATUS_NOT_SALE => \Lsr\Model\ApartmentTable::STATUS_NOT_SALE
					],
						$elementValue);
					break;
			}
		}
	} else {
		if ($structureElement['LINK']) {
			$hrefToLinkedElement = $structureElement['LINK'].'?ID='.$elementToEdit[$structureElement['CODE']];
			$tabControl->AddViewField($structureElement['CODE'], $structureElement['CODE'] . ':', '<a href="'.$hrefToLinkedElement.'">'.$elementToEdit[$structureElement['CODE']].'</a>');
		} elseif ($structureElement['CODE'] == \Lsr\Model\AbstractImageTable::FILE_ID) {
			$linkedElementValues = [];
			$linkedElementQuery = $structureElement['CLASS']::getList(array('filter' => array(\Lsr\Model\AbstractImageTable::ENTITY_ID => $id)));

			$i = 0;
			while($linkedElementCursor = $linkedElementQuery->fetch()) {
				$linkedElementValues[$structureElement['CODE']."[".$i."]"] = $linkedElementCursor[$structureElement['CODE']];
				$i++;
			}
			$tabControl->BeginCustomField('FILES_CUSTOM_FIELD', 'FILES_CUSTOM_FIELD_CONTENT');

			echo \Bitrix\Main\UI\FileInput::createInstance(array(
				"name" => 'FILE_ID[]',
				"description" => false,
				"upload" => true,
				"allowUpload" => "I",
				"fileDialog" => false,
				"cloud" => false,
				"delete" => true,
				"maxCount" => 10,
				"multiple" => true,
				'edit' => false,
				'allowUploadExt' =>false
			))->show($linkedElementValues);
			$tabControl->EndCustomField('FILES_CUSTOM_FIELD');
		}
	}
}
$tabControl->EndTab();

$tabControl->Buttons(
	array(
		"disabled" => false,
		"back_url" => $backurl
	)
);

$tabControl->Show();