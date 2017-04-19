<?php

namespace Bigfork\SilverStripeSimpleSEO;

use Extension;
use FieldList;
use LiteralField;
use Requirements;
use TextField;
use ToggleCompositeField;

class SimpleSEOExtension extends Extension
{
    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        Requirements::css(SIMPLESEO_DIR . '/css/simpleseo-preview.css');
        Requirements::css(SIMPLESEO_DIR . '/css/simpleseo-warnings.css');
        Requirements::javascript(SIMPLESEO_DIR . '/javascript/SimpleSEOPreview.js');
        Requirements::javascript(SIMPLESEO_DIR . '/javascript/SimpleSEOWarnings.js');

        // Move "Metadata" fields to new tab
        $metadataFields = $fields->fieldByName('Root.Main.Metadata')->getChildren();
        $fields->removeByName('Metadata');
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

        // Push preview field
        $fields->insertBefore(
            'MetaTitle',
            LiteralField::create('SimpleSEOPreview', $this->owner->renderWith('SimpleSEOPreview'))
        );

        // Push warnings field
        $fields->insertBefore(
            'MetaTitle',
            LiteralField::create('SimpleSEOWarnings', $this->owner->renderWith('SimpleSEOWarnings'))
        );
    }
}
