<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/**
 * @var string $componentPath
 */

?>

<h1>Квартиры в продаже</h1>

<div id="js-lsr-apartments">
	<?php require 'ajax.php' ?>
</div>

<script>
	let lsrApartmentsAjaxUrl = "<?= $componentPath ?>/ajax.php";
</script>
