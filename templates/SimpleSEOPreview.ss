<div class="form-group field">
    <label class="form__field-label" for="">Preview</label>
    <div class="form__field-holder">
        <div class="simpleseo-preview">
			<div class="simpleseo-preview__image">
				<% if $OGImage %>
					{$OGImage.FocusFill(1200, 630)}
				<% else %>
					{$SiteConfig.DefaultOGImage.FocusFill(1200, 630)}
				<% end_if %>
			</div>

            <h1 class="simpleseo-preview__title">
                <span class="simpleseo-preview__field simpleseo-preview__field--metatitle"<% if not $MetaTitle %> style="display: none"<% end_if %>>
                    <span>{$MetaTitle.LimitCharactersToClosestWordHTML(75)}</span>
                </span>
                <span class="simpleseo-preview__field simpleseo-preview__field--title"<% if $MetaTitle %> style="display: none"<% end_if %>>
                    <span>{$Title}</span> | {$SiteConfig.Title}
                </span>
            </h1>

            <span class="simpleseo-preview__url">
				<% if $AbsoluteLink %>
					{$AbsoluteLink}
				<% else %>
					{$AbsoluteBaseURL}
				<% end_if %>
			</span>

            <div class="simpleseo-preview__content">
                <% if $MetaDescription %>
                    {$MetaDescription.LimitCharactersToClosestWordHTML(156)}
				<% else_if $SiteConfig.MetaDescription %>
					{$MetaDescription.LimitCharactersToClosestWordHTML(156)}
				<% else %>
                    {$ContentPreview.LimitCharactersToClosestWord(156)}
                <% end_if %>
            </div>
        </div>
    </div>
    <p class="form__field-extra-label">An example of how this page might look in search engine results.</p>
</div>
