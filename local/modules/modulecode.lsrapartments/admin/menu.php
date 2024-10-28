<?php

return [
	"parent_menu" => "global_menu_content",
	"sort" => 100,
	'text' => GetMessage("MENU_GROUP_TEXT"),
	'icon' => 'lsr-apartments',
	'items_id' => 'lsr-apartments',
	'items' => [
		[
			'text' => GetMessage("MENU_APARTMENTS_TEXT"),
			'url' => '/bitrix/admin/lsr_apartments_list.php',
		],
		[
			'text' => GetMessage("MENU_HOUSES_TEXT"),
			'url' => '/bitrix/admin/lsr_houses_list.php',
		],
	],
	'more_url' => [
		'/bitrix/admin/lsr_apartments_edit.php',
		'/bitrix/admin/lsr_houses_edit.php'
	]
];
