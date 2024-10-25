<?php

return [
	"parent_menu" => "global_menu_content",
	"sort" => 100,
	'text' => 'Квартиры',
	'icon' => 'lsr-apartments',
	'items' => [
		[
			'text' => 'Квартиры',
			'url' => '/bitrix/admin/lsr_apartments_list.php',
		],
		[
			'text' => 'Дома',
			'url' => '/bitrix/admin/lsr_houses_list.php',
		],
	]
];
