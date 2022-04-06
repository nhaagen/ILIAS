<?php declare(strict_types=1);

/* Copyright (c) 2022 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Wizard;

use ILIAS\UI\Component\Input\Container\Wizard as W;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Implementation\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Implementation\Component\Listing\Factory as ListingFactory;
use ILIAS\UI\Implementation\Component\Input\Container\Wizard\WizardInputNameSource;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ilLanguage;

/**
 * Factory for the Wizard Containers
 */
class Factory implements W\Factory
{
    protected RefineryFactory $refinery;
    protected FieldFactory $field_factory;
    protected WizardInputNameSource $name_source;
    protected DataFactory $data_factory;
    protected ArrayBasedRequestWrapper $query_wrapper;
    protected ilLanguage $lng;
    protected W\StepFactory $step_factory;

    public function __construct(
        RefineryFactory $refinery,
        ArrayBasedRequestWrapper $query_wrapper,
        FieldFactory $field_factory,
        WizardInputNameSource $name_source,
        DataFactory $data_factory,
        ilLanguage $lng
    ) {
        $this->refinery = $refinery;
        $this->query_wrapper = $query_wrapper;
        $this->field_factory = $field_factory;
        $this->name_source = $name_source;
        $this->data_factory = $data_factory;
        $this->lng = $lng;

        $this->step_factory = new StepFactory(
            $this->refinery,
            $this->field_factory,
            $this->data_factory,
            $this->lng
        );
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
        return new Dynamic(
            $this->step_factory,
            $this->name_source,
            $storage,
            $builder,
            $post_url,
            $title,
            $description
        );
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
        $builder = new StaticStepBuilder($steps);

        return new StaticSequence(
            $this->query_wrapper,
            $this->step_factory,
            $this->name_source,
            $storage,
            $builder,
            $post_url,
            $title,
            $description
        );
    }
}
