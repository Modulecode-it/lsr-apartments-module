$(document).ready(function() {
	console.log(lsrApartmentsAjaxUrl);

	$("#js-filter-form").on('submit', function(event) {
		event.preventDefault(); // Отменяем стандартное действие формы
		loadProducts(1); // Загружаем товары с первой страницы
	});

	$("#js-lsr-apartments ").on("click", ".bx-pagination button", function(event) {
		event.preventDefault(); // Отменяем стандартное действие
		let page = $(this).data('page');
		loadProducts(page);
	});

	function loadProducts(page = 1) {
		// Собираем данные формы
		let formData = $('#js-filter-form').serialize();
		formData += '&nav=page-' + page; // Добавляем номер страницы к данным

		$.ajax({
			url: lsrApartmentsAjaxUrl,
			method: 'GET',
			data: formData, // Передаем данные фильтра и страницы
			dataType: 'html',
			success: function(response) {
				$('#js-lsr-apartments').html(response);
			}
		});
	}
});