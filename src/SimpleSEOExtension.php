<?php

namespace Bigfork\SilverStripeSimpleSEO;

use SilverStripe\CMS\Model\RedirectorPage;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\CMS\Model\VirtualPage;
use SilverStripe\Control\Director;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\LinkField\Form\LinkField;
use SilverStripe\LinkField\Models\ExternalLink;
use SilverStripe\LinkField\Models\FileLink;
use SilverStripe\LinkField\Models\Link;
use SilverStripe\LinkField\Models\SiteTreeLink;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\View\Requirements;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\Forms\LiteralField;

/**
 * @method Link CanonicalLink()
 */
class SimpleSEOExtension extends Extension
{
    private static array $has_one = [
        'CanonicalLink' => Link::class,
    ];

    private static array $owns = [
        'CanonicalLink',
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        // No SEO stuff available on redirector/virtual pages
        if ($this->owner instanceof RedirectorPage || $this->owner instanceof VirtualPage) {
            return;
        }

        // Check we have metadata fields to work with
        $metadataHolder = $fields->fieldByName('Root.Main.Metadata');
        if (!$metadataHolder) {
            return;
        }

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
        $metadataFields = $metadataHolder->getChildren();
        $fields->removeByName('Metadata');
        $fields->addFieldsToTab('Root.SEO', $metadataFields);

        // Make "Description" field a TextField
        $fields->replaceField(
            'MetaDescription',
            TextField::create('MetaDescription', $this->owner->fieldLabel('MetaDescription'))
                ->setRightTitle(
                    _t(
                        'SiteTree.METADESCHELP',
                        'Search engines use this content for displaying search results'
                        . ' (although it will not influence their ranking).'
                    )
                )
        );

        $extraMeta = $fields->dataFieldByName('ExtraMeta');
        $extraMeta->setRows(7);

        // Wrap "Custom meta tags" field in a ToggleCompositeField
        $fields->replaceField(
            'ExtraMeta',
            $advanced = ToggleCompositeField::create(
                'ExdtraMeta',
                'Advanced Options',
                $extraMeta
            )
        );

        // Insert canonical page option before custom meta tags
        $advanced->unshift(
            LinkField::create('CanonicalLink', 'Canonical link')
                ->setExcludeLinkTextField(true)
                ->setAllowedTypes([
                    SiteTreeLink::class,
                    ExternalLink::class,
                    FileLink::class,
                ])
                ->setRightTitle(
                    'Indicates to search engines that the selected page represents a “master” copy of this page'
                ),
        );
    }

    /**
     * @param array $tags
     */
    public function MetaComponents(array &$tags)
    {
        /** @var Link $link */
        $link = $this->owner->CanonicalLink();
        if ($link->exists()) {
            $tags['canonical'] = [
                'tag' => 'link',
                'attributes' => [
                    'rel' => 'canonical',
                    'href' => Director::absoluteURL($link->getURL()),
                ]
            ];
        }
    }

    /**
     * @return DBHTMLText
     */
    public function getContentPreview()
    {
        $preview = $this->owner->dbObject('Content');

        $this->owner->invokeWithExtensions('updateContentPreview', $preview);

        return $preview;
    }
}
