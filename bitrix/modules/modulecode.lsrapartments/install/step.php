<?php

/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 28.10.2024
 * Time: 14:33
 */

use Modulecode\Lsrapartments\Installer;
use Modulecode\Lsrapartments\Model\HouseTable;

if (!check_bitrix_sessid()) {
	return;
}

$initCount = HouseTable::getCount();
$installer = new Installer();
$installer->insertDemoData($initCount);

$nowCount = HouseTable::getCount();

CAdminMessage::ShowNote(
	"Установка демо данных. Добавлено $nowCount домов. Пожалуйста, дождитесь окончания процесса установки..."
);
?>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			setTimeout(function () {
				location.reload();
			}, 1000);
		});
	</script>
<?php
exit();