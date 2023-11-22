(function($) {
	$.entwine('simpleseo', function($) {
		/**
		 * Limits the string to the max length, breaking on the nearest preceeding space
		 */
		var limitStringLength = function(value, maxLength) {
			if (value.length > maxLength) {
				value = value.substr(0, maxLength); // Limit string to max length
				value = value.substr(0, value.lastIndexOf(' ')); // Limit to the last space before the cut-off
				value = value.replace(/[\.,-\/#!$%\^&\*;:{}=\-_`~()]$/, '') + '...'; // Remove trailing punctuation
			}

			return value;
		}

		/**
		 * Form field update triggers
		 */
		$('#Form_EditForm_Title #Form_ItemEditForm_Title, #Form_EditForm_MetaTitle, #Form_ItemEditForm_MetaTitle').entwine({
			oninput: function() {
				$('.simpleseo-preview__title').update();
			},

			onchange: function() {
				$('.simpleseo-preview__title').update();
			}
		});

		$('#Form_EditForm_Content, #Form_ItemEditForm_Content, #Form_EditForm_MetaDescription, #Form_ItemEditForm_MetaDescription').entwine({
			oninput: function() {
				$('.simpleseo-preview__content').update();
			},

			onchange: function() {
				$('.simpleseo-preview__content').update();
			}
		});

		/**
		 * Preview meta title
		 */
		$('.simpleseo-preview__title').entwine({
			MaxLength: 75,

			getValue: function() {
				return $('#Form_EditForm_MetaTitle, #Form_ItemEditForm_MetaTitle').val() || $('#Form_EditForm_Title, #Form_ItemEditForm_Title').val();
			},

			update: function() {
				var metatitle = this.getValue(),
					target;

				if ($('#Form_EditForm_MetaTitle, #Form_ItemEditForm_MetaTitle').val()) {
					$('.simpleseo-preview__field--title').hide();
					$('.simpleseo-preview__field--metatitle').show();
					target = $('.simpleseo-preview__field--metatitle span');
				} else {
					$('.simpleseo-preview__field--metatitle').hide();
					$('.simpleseo-preview__field--title').show();
					target = $('.simpleseo-preview__field--title span');
				}

				metatitle = limitStringLength(metatitle, this.getMaxLength());
				target.text(metatitle);
			}
		});

		/**
		 * Preview meta description
		 */
		$('.simpleseo-preview__content').entwine({
			MaxLength: 160,

			getValue: function() {
				var value = $('#Form_EditForm_MetaDescription, #Form_ItemEditForm_MetaDescription').val();

				// Try to grab a faux-description from the content, we have to crudely strip HTML from it...
				if (!value) {
					var htmlContent = $('#Form_EditForm_Content, #Form_ItemEditForm_Content').val(),
						tmpElement = $('<div />').html(htmlContent);

					value = tmpElement.text();
					tmpElement.remove();
				}

				return value;
			},

			update: function() {
				var metadesc = this.getValue();

				metadesc = limitStringLength(metadesc, this.getMaxLength());
				this.text(metadesc);
			}
		});
	});

	$.entwine('ss', function($) {
		$('.field.urlsegment:not(.readonly)').entwine({
			/**
			 * Update the URL displayed in the preview when the URL segment changes
			 */
			redraw: function() {
				this._super();

				var field = this.find(':text'),
					url = decodeURI(field.data('prefix')),
					segment = field.val();

				if (segment !== 'home') {
					url += decodeURI(segment);
				}

				$('.simpleseo-preview__url').text(url);
			}
		});
	});
}(jQuery));
