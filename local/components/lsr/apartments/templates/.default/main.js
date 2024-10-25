$(document).ready(function() {
	let listWrap = $("#js-lsr-apartments-list");
	let filter = $("#js-lsr-apartments-filter");

	filter.on("change", ":input", function() {
		loadProducts(1); // Загружаем товары с первой страницы
	});

	listWrap.on("click", ".bx-pagination button", function() {
		loadProducts($(this).data('page'));
	});

	function loadProducts(page = 1) {
		// Собираем данные формы
		let formData = filter.serialize();
		formData += '&nav=page-' + page; // Добавляем номер страницы к данным

		$.ajax({
			url: lsrApartmentsAjaxUrl,
			method: 'GET',
			data: formData, // Передаем данные фильтра и страницы
			dataType: 'html',
			success: function(response) {
				listWrap.html(response);
			}
		});
	}
});