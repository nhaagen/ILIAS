<?php declare(strict_types=1);

/* Copyright (c) 2021 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Wizard;

use ILIAS\UI\Component\Input\Container\Wizard as W;
use ILIAS\UI\Implementation\Component\Input;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Implementation\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Implementation\Component\Listing\Factory as ListingFactory;
use ILIAS\Data\Factory as DataFactory;
use ilLanguage;

/**
 * Factory for the Wizard Containers
 */
class Factory implements W\Factory
{
    protected RefineryFactory $refinery;
    protected FieldFactory $field_factory;
    protected ListingFactory $listing_factory;
    protected DataFactory $data_factory;
    protected ilLanguage $lng;

    public function __construct(
        RefineryFactory $refinery,
        FieldFactory $field_factory,
        ListingFactory $listing_factory,
        DataFactory $data_factory,
        ilLanguage $lng
    ) {
        $this->refinery = $refinery;
        $this->field_factory = $field_factory;
        $this->listing_factory = $listing_factory;
        $this->data_factory = $data_factory;
        $this->lng = $lng;
    }

    /**
     * @inheritdoc
     */
    public function dynamic(
        W\Storage $storage,
        W\StepBuilder $builder,
        string $post_url,
        string $title,
        string $description
    ) : W\Dynamic {
        $step_factory = new StepFactory(
            $this->refinery,
            $this->field_factory,
            $this->data_factory,
            $this->lng
        );
        return new Dynamic($step_factory, $storage, $builder, $post_url, $title, $description);
    }


    /**
     * @inheritdoc
     */
    public function staticsequence(
        W\Storage $storage,
        array $steps,
        string $post_url,
        string $title,
        string $description
    ) : W\StaticSequence {
        return new StaticSequence(
            $this->workflow_listing_factory,
            $storage,
            $steps,
            $post_url,
            $title,
            $description
        );
    }
}
