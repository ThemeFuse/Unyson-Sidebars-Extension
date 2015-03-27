(function ($, fwe) {
	var init = function () {
		var $this = $(this),
			elements = {
				$image_picker: $this.find('.fw-option-type-image-picker select'),
				$choicesGroups: $this.find('> .choice-group')
			};

		$this.on('fw:option-type:image-picker:clicked', function (e, data) {
			methods.showSidebarsLocation(data.data.colors);
		});

		methods = {
			showSidebarsLocation: function ($colors) {
				$this.find('.fw-ext-sidebars-location').addClass('empty').find('select').attr('disabled', true).end()
					.slice(0, $colors)
					.removeClass('empty').find('select').attr('disabled', false);
			}
		}

		//first run.
		if (elements.$image_picker.length) {
			methods.showSidebarsLocation(elements.$image_picker.find('option:selected').data('extraData').colors);
		}
	};

	fwe.on('fw:options:init', function (data) {
		data.$elements
			.find('.fw-option-type-sidebar-picker:not(.fw-option-initialized)').each(init)
			.addClass('fw-option-initialized');
	});
})(jQuery, fwEvents);
