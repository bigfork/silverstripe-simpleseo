<div class="field">
    <label class="left" for="">Preview</label>
    <div class="middleColumn">
        <div class="simpleseo-preview">
            <h1 class="simpleseo-preview__title">
                <span class="simpleseo-preview__field simpleseo-preview__field--metatitle"<% if not $MetaTitle %> style="display: none"<% end_if %>>
                    <span>{$MetaTitle.LimitCharactersToClosestWordHTML(75)}</span>
                </span>
                <span class="simpleseo-preview__field simpleseo-preview__field--title"<% if $MetaTitle %> style="display: none"<% end_if %>>
                    <span>{$Title}</span> &raquo; {$SiteConfig.Title}
                </span>
            </h1>
            <span class="simpleseo-preview__url">{$AbsoluteLink}</span>
            <div class="simpleseo-preview__content">
                <% if $MetaDescription %>
                    {$MetaDescription.LimitCharactersToClosestWordHTML(156)}
                <% else %>
                    {$Content.LimitCharactersToClosestWord(156)}
                <% end_if %>
            </div>
        </div>
    </div>
    <label class="right" for="">An example of how this page might look in search engine results.</label>
</div>
