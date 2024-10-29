<?php

use Bitrix\Main\Application;
use Bitrix\Main\Web\MimeType;
use Modulecode\Lsrapartments\AdminInterface;
use Modulecode\Lsrapartments\Model\AbstractImageTable;
use Modulecode\Lsrapartments\Service\FileService;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

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

$id = (int)$request->get('ID');

$elementToEdit = array();
$structureToEdit = [];
$classToEditMap = $classToEdit::getMap();
/** @var \Bitrix\Main\ORM\Fields\ScalarField $tableField */
foreach ($classToEditMap as $tableField) {
	if ($tableField::class == 'Bitrix\Main\ORM\Fields\Relations\Reference'
		|| $tableField::class == 'Bitrix\Main\ORM\Fields\Relations\OneToMany'
	) {
		continue;
	}

	$tableFieldData = [
		'EXTERNAL' => false,
		'CODE' => $tableField->getColumnName(),
		'IS_PRIMARY' => $tableField->isPrimary(),
		'IS_REQUIRED' => $tableField->isRequired(),
		'TITLE' => $tableField->getTitle(),
		'TYPE' => $tableField::class,
		'DEFAULT_VALUE' => $tableField->getDefaultValue(),
	];
	if ($tableField::class == 'Bitrix\Main\ORM\Fields\EnumField') {
		$tableFieldData['ENUMS_MAP'] = $tableField->getValues();
	}
	$structureToEdit[] = $tableFieldData;
}
//а вдруг если какое-то свойство используется как референс на другой объект.
foreach ($classToEditMap as $tableField) {
	if ($tableField::class == 'Bitrix\Main\ORM\Fields\Relations\Reference') {
		$classToLink = $tableField->getDataType();
		$linkStructure = $tableField->getElementals();
		$codeToSubstitute = array_keys($linkStructure)[0];
		foreach ($structureToEdit as $structureToEditKey => $structureToEditValue) {
			if ($structureToEditValue['CODE'] == $codeToSubstitute) {
				$structureToEdit[$structureToEditKey]['EXTERNAL'] = true;
				$structureToEdit[$structureToEditKey]['LINK'] = AdminInterface::getLinkToElementEditByClassString(
					$classToLink
				);
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
		'CLASS' => $imagesClass,
		'TITLE' => $tableField->getTitle()
	];
}

$errorMessage = '';

if ($server->getRequestMethod() == "GET"
	&& !empty($_GET['ID'])
	&& $_GET['delete'] == 'Y'
) {
	$classToEdit::delete($id);
	LocalRedirect($backurl);
}

$elementToEdit = [];
//Значение по умолчанию
foreach ($structureToEdit as $structureElement) {
	$elementToEdit[$structureElement['CODE']] = $structureElement['DEFAULT_VALUE'];
}

if ($server->getRequestMethod() == "POST"
	&& ($request->get('save') !== null || $request->get('apply') !== null)
	&& check_bitrix_sessid()
) {
	//Значения из формы
	foreach ($structureToEdit as $structureElement) {
		if ($structureElement['CODE'] == 'ACTIVE') {
			$elementToEdit[$structureElement['CODE']] = (isset($_POST['ACTIVE']) && 'Y' == $_POST['ACTIVE'] ? 'Y' : 'N');
		} else {
			$elementToEdit[$structureElement['CODE']] = $request->getPost($structureElement['CODE']);
		}

		//но если поле не обязательное, и не заполнено, то null
		if (
			$structureElement['CODE'] == 'SALE_PRICE'   //стоимость со скидкой
			&& !$structureElement['IS_REQUIRED']
			&& $request->getPost($structureElement['CODE']) === ''
		) {
			$elementToEdit[$structureElement['CODE']] = null;
		}
	}

	if ($id > 0) {
		$result = $classToEdit::update($id, $elementToEdit);
	} else {
		unset($elementToEdit['ID']);
		$result = $classToEdit::add($elementToEdit);
		$id = $result->getId();
		$elementToEdit['ID'] = $id;
	}

	if ($result->isSuccess() && $_POST['FILE_ID']) {
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

					if (!$fileName) {
						//а если вдруг имени не пришло, то будет default. чтобы картинка отобразилась нужно расширение
						$uploadedImageArray = \CFile::MakeFileArray(
							BX_TEMPORARY_FILES_DIRECTORY . $fileEntry['tmp_name']
						);
						$fileName = 'default.' . array_flip(MimeType::getMimeTypeList())[$uploadedImageArray['type']];
					}

					$filePath = BX_TEMPORARY_FILES_DIRECTORY . $fileEntry['tmp_name'];
					$fileId = $imageService->saveExistingFileToBFile(
						$filePath,
						$classToEdit::getTableName(),
						$fileName
					);
					$image->set($imagesClass::FILE_ID, $fileId);

					$result = $image->save();

					if (!$result->isSuccess()) {
						throw new \LogicException(
							GetMessage("IMAGE_SAVE_FAILED") . join(", ", $result->getErrorMessages())
						);
					}
				}
			}
		}
	}

	if (!$result->isSuccess()) {
		$errors = $result->getErrorMessages();
	} else {
		if ($request->get('save') !== null) {
			LocalRedirect($backurl);
		} else {
			if ($_POST['apply'] && $id && !$_GET['ID']) {
				LocalRedirect($APPLICATION->GetCurPage() . "?ID=" . $id . "&lang=" . $lang);
			} else {
				LocalRedirect($_SERVER['REQUEST_URI']);
			}
		}
	}
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$APPLICATION->SetTitle(($id > 0) ? $tabName . ' № ' . $id . ': ' . GetMessage("TITLE_REDACT") : $tabName . ': ' . GetMessage("TITLE_ADD"));

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
		"TEXT" => GetMessage("BACK"),
		"LINK" => $backurl,
		"ICON" => "btn_list"
	)
);
if ($id > 0) {
	$aMenu[] = array(
		"TEXT" => GetMessage("DELETE_TEXT"),
		"TITLE" => GetMessage("DELETE_TITLE"),
		"ICON" => "btn_delete",
		"LINK" => "javascript:if(confirm('" . GetMessage(
				"DELETE_CONFIRM"
			) . "'))window.location=window.location.origin + window.location.pathname + '?ID=" . $id . "&delete=Y'"
	);

	if ($classToEdit == 'Modulecode\Lsrapartments\Model\HouseTable') {
		$aMenu[] = array(
			"TEXT" => GetMessage('FIND_LINKED_APARTMENTS_TEXT'),
			"TITLE" => GetMessage('FIND_LINKED_APARTMENTS_TITLE'),
			"LINK" => "/bitrix/admin/lsr_apartments_list.php?HOUSE_ID=" . $id
		);
	}
}

$contextMenu = new CAdminContextMenu($aMenu);
$contextMenu->Show();

$tabControl->BeginEpilogContent();
echo GetFilterHiddens("filter_");
echo bitrix_sessid_post();
?>

	<input type="hidden" name="Update" value="Y">
	<input type="hidden" name="lang" value="<?= $context->getLanguage(); ?>">
	<input type="hidden" name="ID" value="<?= $id; ?>" id="ID">

<?
$tabControl->EndEpilogContent();
$tabControl->Begin(array("FORM_ACTION" => $APPLICATION->GetCurPage() . "?ID=" . $id . "&lang=" . $lang));
$tabControl->BeginNextFormTab();

$externalLinkToPassForJs = '';
foreach ($structureToEdit as $structureElement) {
	if (!$structureElement['EXTERNAL']) {
		$elementValue = $elementToEdit[$structureElement['CODE']];

		if ($structureElement['IS_PRIMARY']) {
			$tabControl->AddViewField($structureElement['CODE'], $structureElement['CODE'] . ':', $elementValue);
		} else {
			switch ($structureElement['TYPE']) {
				case 'Bitrix\Main\ORM\Fields\BooleanField':
					$tabControl->AddCheckBoxField(
						$structureElement['CODE'],
						$structureElement['TITLE'] . ':',
						$structureElement['IS_REQUIRED'],
						'Y',
						$elementValue == "Y"
					);
					break;
				case 'Bitrix\Main\ORM\Fields\StringField':
				case 'Bitrix\Main\ORM\Fields\IntegerField':
				case 'Bitrix\Main\ORM\Fields\FloatField':
					$tabControl->AddEditField(
						$structureElement['CODE'],
						$structureElement['TITLE'] . ':',
						$structureElement['IS_REQUIRED'],
						array('SIZE' => 40),
						$elementValue
					);
					break;
				case 'Bitrix\Main\ORM\Fields\EnumField':
					$tabControl->AddDropDownField(
						$structureElement['CODE'],
						$structureElement['TITLE'] . ':',
						$structureElement['IS_REQUIRED'],
						array_flip($structureElement['ENUMS_MAP']),
						$elementValue
					);
					break;
			}
		}
	} else {
		if ($structureElement['LINK']) {
			$externalLinkToPassForJs = $structureElement['LINK'];

			$linkedElementsSelectionQuery = ($classToLink . 'Table')::getList(array('filter' => array()));
			$linkedElementsSelectionArray = ["" => ""];
			while ($linkedElementsSelectionCursor = $linkedElementsSelectionQuery->fetch()) {
				$linkedElementsSelectionArray[$linkedElementsSelectionCursor['ID']] = $linkedElementsSelectionCursor['ADDRESS'] . ' [' . $linkedElementsSelectionCursor['ID'] . ']';
			}
			$tabControl->AddDropDownField(
				$structureElement['CODE'],
				$structureElement['TITLE'] . ':',
				true,
				$linkedElementsSelectionArray,
				$elementToEdit[$structureElement['CODE']],
				['onchange="showLinkToLinkedElement()"']
			);
		} elseif ($structureElement['CODE'] == AbstractImageTable::FILE_ID) {
			$linkedElementValues = [];
			$linkedElementQuery = $structureElement['CLASS']::getList(
				array('filter' => array(AbstractImageTable::ENTITY_ID => $id))
			);

			$i = 0;
			while ($linkedElementCursor = $linkedElementQuery->fetch()) {
				$linkedElementValues[$structureElement['CODE'] . "[" . $i . "]"] = $linkedElementCursor[$structureElement['CODE']];
				$i++;
			}
			$tabControl->BeginCustomField('FILES_CUSTOM_FIELD', 'FILES_CUSTOM_FIELD_CONTENT');
			?>
			<tr class="">
				<td width="40%">
					<?= $structureElement['TITLE'] . ':' ?>
				</td>
				<td>
					<?
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
						'allowUploadExt' => false
					))->show($linkedElementValues);
					?>
				</td>
			</tr>
			<?
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
?>
	<script>
		function showLinkToLinkedElement() {
			if (document.querySelector('[onchange="showLinkToLinkedElement()"]')) {
				if (!document.querySelector('#linkToLinkedElement')) {
					document.querySelector('[onchange="showLinkToLinkedElement()"]').insertAdjacentHTML('afterend', '<div><a id="linkToLinkedElement" href="#" target="_blank"><?=GetMessage(
						"TO_LINKED_ELEMENT"
					)?></a></div>');
				}
                var linkToLinkedElementVar = document.querySelector('#linkToLinkedElement');
                if (document.querySelector('[onchange="showLinkToLinkedElement()"]').value) {
                    linkToLinkedElementVar.href = '<?=$externalLinkToPassForJs?>?ID='
	                    + document.querySelector('[onchange="showLinkToLinkedElement()"]').value;
                    linkToLinkedElementVar.style.display = '';
                } else {
                    linkToLinkedElementVar.style.display = 'none';
                }
			}
		}

		document.addEventListener("DOMContentLoaded", function () {
			showLinkToLinkedElement();
		});
	</script>
<?php

if (!empty($errors)) {
	CAdminMessage::ShowMessage(join("\n", $errors));
}
$tabControl->Show();