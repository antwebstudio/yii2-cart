(function($) {

	 Cart = function(settings, $list) {
		this.setQuantity = function(id, quantity) {
			var data = {
				action: ['setQuantity'],
				id: [id],
				cartType: [settings.cartType],
				quantity: [quantity],
			};
			
			submitForm(data);
		}
		
		this.deleteCartItem = function(id, callback) {
			var data = {
				action: 'deleteCartItem',
				id: id,
				cartType: settings.cartType,
			};
			//submitForm(data);
			$.get(settings.url, data).done(callback);
		}
		
		var submitForm = function(data) {
			var $form = $('<form/>', {
				action: settings.url,
				method: 'GET',
				style: 'display:none',
				'data-pjax': ''
			//}).appendTo($list);
			}).appendTo('body');

			// temp (form wrap form isse?)
			// $form.on('submit', function () {
			// 	window.location = settings.url + '?' + $(this).serialize();
			// 	return false;
			// });
			
			// console.log(settings.url);
			$.each(data, function (name, values) {
				$.each(values, function (index, value) {
					$form.append($('<input/>').attr({type: 'hidden', name: name, value: value}));
				});
			});
			
			$form.submit();
		}
		
		$('[data-item]').each(function() {
			var $self = $(this);
			var id = $self.attr('data-item');
			
			$(this).find('[data-field=quantity]').on('change', function() {
				var quantity = $(this).val();
				cart.setQuantity(id, quantity);
			});
			
			$(this).find('[data-action=delete]').on('click', function() {
				cart.deleteCartItem(id, function() {
					$self.trigger('deleted');
				});
			});
			
			for (var action in settings.clientEvents) {
				var handler = settings.clientEvents[action];
				var selector = $self ;
				
				$(document).on(action, selector, handler);
			}
		});
		
		return this;
	}
		
})(jQuery);