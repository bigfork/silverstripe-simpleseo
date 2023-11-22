(function($) {
	$.entwine('simpleseo', function($) {
		/**
		 * Form field update triggers
		 */
		$('#Form_EditForm_Title, #Form_ItemEditForm_Title, #Form_EditForm_MetaTitle, #Form_ItemEditForm_MetaTitle, #Form_EditForm_Content, #Form_ItemEditForm_Content, #Form_EditForm_MetaDescription, #Form_ItemEditForm_MetaDescription').entwine({
			oninput: function() {
				$('.simpleseo-warnings').checkVisibility();
				this._super();
			},

			onchange: function() {
				$('.simpleseo-warnings').checkVisibility();
				this._super();
			}
		});

		/**
		 * Warning evaluation
		 */
		$('.simpleseo-warnings').entwine({
			onmatch: function() {
				this.checkVisibility();
			},

			checkVisibility: function() {
				var show = false;

				$('.simpleseo-warnings__warning').each(function() {
					if ($(this).shouldDisplay()) {
						$(this).show();
						show = true;
					} else {
						$(this).hide();
					}
				});

				(show) ? this.show() : this.hide();
			}
		});

		/**
		 * Individual warning show/hide rules
		 */
		$('.simpleseo-warnings__warning[data-type=nometatitle]').entwine({
			shouldDisplay: function() {
				return (!$('#Form_EditForm_MetaTitle, #Form_ItemEditForm_MetaTitle').val());
			}
		});

		$('.simpleseo-warnings__warning[data-type=goodmetatitlelength]').entwine({
			shouldDisplay: function() {
				return ($('#Form_EditForm_MetaTitle, #Form_ItemEditForm_MetaTitle').val() && $('.simpleseo-preview__title').getValue().length >= 40 && $('.simpleseo-preview__title').getValue().length <= 60);
			}
		});

		$('.simpleseo-warnings__warning[data-type=shortmetatitlelength]').entwine({
			shouldDisplay: function() {
				return ($('#Form_EditForm_MetaTitle, #Form_ItemEditForm_MetaTitle').val() && $('.simpleseo-preview__title').getValue().length < 40);
			}
		});

		$('.simpleseo-warnings__warning[data-type=longmetatitlelength]').entwine({
			shouldDisplay: function() {
				return ($('#Form_EditForm_MetaTitle, #Form_ItemEditForm_MetaTitle').val() && $('.simpleseo-preview__title').getValue().length > 60);
			}
		});

		$('.simpleseo-warnings__warning[data-type=nometadescription]').entwine({
			shouldDisplay: function() {
				return (!$('#Form_EditForm_MetaDescription, #Form_ItemEditForm_MetaDescription').val());
			}
		});

		$('.simpleseo-warnings__warning[data-type=goodmetadescriptionlength]').entwine({
			shouldDisplay: function() {
				return ($('#Form_EditForm_MetaDescription, #Form_ItemEditForm_MetaDescription').val() && $('.simpleseo-preview__content').getValue().length >= 80 && $('.simpleseo-preview__content').getValue().length <= 150);
			}
		});

		$('.simpleseo-warnings__warning[data-type=shortmetadescriptionlength]').entwine({
			shouldDisplay: function() {
				return ($('#Form_EditForm_MetaDescription, #Form_ItemEditForm_MetaDescription').val() && $('.simpleseo-preview__content').getValue().length < 80);
			}
		});

		$('.simpleseo-warnings__warning[data-type=longmetadescriptionlength]').entwine({
			shouldDisplay: function() {
				return ($('#Form_EditForm_MetaDescription, #Form_ItemEditForm_MetaDescription').val() && $('.simpleseo-preview__content').getValue().length > 150);
			}
		});
	});
}(jQuery));
