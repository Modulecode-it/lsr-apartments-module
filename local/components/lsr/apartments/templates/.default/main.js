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
			url: lsrApartments.ajaxUrl,
			method: 'GET',
			data: formData, // Передаем данные фильтра и страницы
			dataType: 'html',
			success: function(response) {
				listWrap.html(response);

				//Изменяем адрес страницы на актуальный
				let hasQuery = lsrApartments.pageUrl.indexOf('?') > -1;
				let newPageUrl = lsrApartments.pageUrl + (hasQuery ? "&" : "?") + formData;
				history.pushState({}, "", newPageUrl);
			}
		});
	}
});