<?php

namespace Bigfork\SilverStripeSimpleSEO;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\CMS\Model\RedirectorPage;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\CMS\Model\VirtualPage;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\Requirements;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\Forms\LiteralField;

class SimpleSEOExtension extends Extension
{
    private static array $has_one = [
        'CanonicalPage' => SiteTree::class,
        'OGImage'       => Image::class,
    ];

    private static array $owns = [
        'OGImage',
    ];

    public function updateCMSFields(FieldList $fields): void
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
        $fields->addFieldToTab('Root.SEO', $previewField);

        // Push warnings field
        $fields->addFieldToTab('Root.SEO', $warningsField);

        // Move "Metadata" fields to new tab
        $metadataFields = $metadataHolder->getChildren();
        $fields->removeByName('Metadata');
        $fields->addFieldsToTab(
            'Root.SEO',
            [
                UploadField::create('OGImage', 'OG Image')
                    ->setAllowedFileCategories('image/supported'),
                ...$metadataFields,
            ]
        );

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

        /** @var TextareaField $extraMeta */
        $extraMeta = $fields->dataFieldByName('ExtraMeta');
        $extraMeta?->setRows(7);

        // Wrap "Custom meta tags" field in a ToggleCompositeField
        $fields->replaceField(
            'ExtraMeta',
            $advanced = ToggleCompositeField::create(
                'ExtraMeta',
                'Advanced Options',
                $extraMeta
            )
        );

        // Insert canonical page option before custom meta tags
        $advanced->unshift(
            TreeDropdownField::create('CanonicalPageID', 'Canonical page', SiteTree::class)
                ->setRightTitle(
                    'Indicates to search engines that the selected page represents a “master” copy of this page'
                )
        );
    }

    public function MetaComponents(array &$tags): void
    {
        $siteConfig = SiteConfig::current_site_config();
        $owner = $this->getOwner();

        if (!isset($tags ['og:url'])) {
            $tags ['og:url'] = [
                'tag'        => 'meta',
                'attributes' => [
                    'property' => 'og:url',
                    'content'  => $owner->AbsoluteLink(),
                ],
            ];
        }

        // Canonical
        /** @var SiteTree $page */
        $page = $owner->CanonicalPage();
        if ($page->exists()) {
            $tags['canonical'] = [
                'tag'        => 'link',
                'attributes' => [
                    'rel'  => 'canonical',
                    'href' => $page->AbsoluteLink(),
                ],
            ];

            $tags['og:url']['attributes']['content'] = $page->AbsoluteLink();
        }

        // Favicon
        /** @var Image $favicon */
        $favicon = $siteConfig->Favicon();
        if (!isset($tags ['favicon']) && $favicon->exists()) {
            $tags['favicon'] = [
                'tag'        => 'link',
                'attributes' => [
                    'rel'   => 'icon',
                    'type'  => "image/{$favicon->getExtension()}",
                    'sizes' => '32x32',
                    'href'  => $favicon->FocusFill(32, 32)->getAbsoluteURL(),
                ],
            ];
        }

        // Title
        $title = $owner->MetaTitle ?? "{$owner->Title} | {$siteConfig->Title}";
        if (!isset($tags ['og:title'])) {
            $tags ['og:title'] = [
                'tag'        => 'meta',
                'attributes' => [
                    'property' => 'og:title',
                    'content'  => $title
                ],
            ];
        }

        // Twitter Title
        if (!isset($tags ['twitter:title'])) {
            $tags ['twitter:title'] = [
                'tag'        => 'meta',
                'attributes' => [
                    'property' => 'og:title',
                    'content'  => $title
                ],
            ];
        }

        // Page type
        if (!isset($tags ['og:type'])) {
            $tags ['og:type'] = [
                'tag'        => 'meta',
                'attributes' => [
                    'property' => 'og:type',
                    'content'  => $owner->ClassName == 'SilverStripe\Blog\Model\BlogPost' ? 'article' : 'website',
                ],
            ];
        }

        // OG Image
        /** @var Image $image */
        $image = $this->FindOGImage();
        if ($image) {
            $type = "image/{$image->getExtension()}";
            $url = $image->FocusFill(1200, 630)->getURL();

            if (!isset($tags ['og:image:type'])) {
                $tags ['og:image:type'] = [
                    'tag'        => 'meta',
                    'attributes' => [
                        'property' => 'og:image:type',
                        'content'  => $type,
                    ],
                ];
            }

            if (!isset($tags ['og:image'])) {
                $tags ['og:image'] = [
                    'tag'        => 'meta',
                    'attributes' => [
                        'property' => 'og:image',
                        'content'  => $url,
                    ],
                ];
            }

            if (!isset($tags ['image_src'])) {
                $tags ['image_src'] = [
                    'tag'        => 'link',
                    'attributes' => [
                        'rel'     => 'image_src',
                        'type'    => $type,
                        'content' => $url,
                    ],
                ];
            }
        }

        if (!isset($tags ['image_src'])) {
            $tags ['image_src'] = [
                'tag'        => 'meta',
                'attributes' => [
                    'property' => 'og:locale',
                    'content'  => 'en_GB',
                ],
            ];
        }

        if (!isset($tags ['og:site_name'])) {
            $tags ['og:site_name'] = [
                'tag'        => 'meta',
                'attributes' => [
                    'property' => 'og:site_name',
                    'content'  => $siteConfig->Title,
                ],
            ];
        }

        $metaDescription = $owner->MetaDescription ?? $siteConfig->MetaDescription;
        if ($metaDescription) {
            if (!isset($tags ['og:description'])) {
                $tags ['og:description'] = [
                    'tag'        => 'meta',
                    'attributes' => [
                        'property' => 'og:description',
                        'content'  => $metaDescription,
                    ],
                ];
            }

            if (!isset($tags ['twitter:description'])) {
                $tags ['twitter:description'] = [
                    'tag'        => 'meta',
                    'attributes' => [
                        'property' => 'og:description',
                        'content'  => $metaDescription,
                    ],
                ];
            }
        }
    }

    public function getContentPreview(): DBHTMLText
    {
        $preview = $this->owner->dbObject('Content');

        $this->owner->invokeWithExtensions('updateContentPreview', $preview);

        return $preview;
    }

    protected function FindOGImage(): ?Image
    {
        /** @var SiteTree $owner */
        $owner = $this->getOwner();
        $fallbackMethods = Config::inst()->get($owner->ClassName, 'ogimage_fallback_methods') ?? [];
        if ($owner->OGImage()->exists() && $owner->OGImage()->getIsImage()) {
            return $owner->OGImage();
        }

        if (!empty($fallbackMethods)) {
            foreach ($fallbackMethods as $method) {
                if ($owner->hasMethod($method)) {
                    $image = $owner->$method;

                    if ($image instanceof Image && $image->exists()) {
                        return $image;
                    }
                }
            }
        }

        $config = SiteConfig::current_site_config();
        if ($config->DefaultOGImage()->exists()) {
            return $config->DefaultOGImage();
        }

        return null;
    }
}
