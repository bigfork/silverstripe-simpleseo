<?php

namespace Bigfork\SilverStripeSimpleSEO;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\View\Requirements;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\Forms\LiteralField;


class SimpleSEOExtension extends Extension
{
    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        Requirements::css('bigfork/silverstripe-simpleseo:client/css/simpleseo-preview.css');
        Requirements::css('bigfork/silverstripe-simpleseo:client/css/simpleseo-warnings.css');
        Requirements::javascript('bigfork/silverstripe-simpleseo:client/javascript/SimpleSEOPreview.js');
        Requirements::javascript('bigfork/silverstripe-simpleseo:client/javascript/SimpleSEOWarnings.js');

        $previewField = LiteralField::create('SimpleSEOPreview', $this->owner->renderWith('SimpleSEOPreview'));
        $warningsField = LiteralField::create('SimpleSEOWarnings', $this->owner->renderWith('SimpleSEOWarnings'));

        // Push preview field
        $fields->addFieldToTab("Root.SEO", $previewField);

        // Push warnings field
        $fields->addFieldToTab("Root.SEO", $warningsField);

        // Move "Metadata" fields to new tab
        $metadataFields = $fields->fieldByName('Root.Main.Metadata')->getChildren();
        // $fields->removeByName('Metadata');
        $fields->addFieldsToTab('Root.SEO', $metadataFields);

        // Make "Description" field a TextField
        $fields->replaceField(
            'MetaDescription',
            TextField::create('MetaDescription', $this->owner->fieldLabel('MetaDescription'))
                ->setRightTitle(
                    _t(
                        'SiteTree.METADESCHELP',
                        'Search engines use this content for displaying search results
                            (although it will not influence their ranking).'
                    )
                )
        );

        // Wrap "Custom meta tags" field in a ToggleCompositeField
        $fields->replaceField(
            'ExtraMeta',
            ToggleCompositeField::create(
                'ExtraMeta',
                'Advanced Options',
                $fields->dataFieldByName('ExtraMeta')
            )
        );
    }

    /**
     * @return HTMLText|null
     */
    public function getContentPreview()
    {
        return $this->owner->dbObject('Content');
    }
}
