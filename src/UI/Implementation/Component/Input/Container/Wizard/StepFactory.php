<?php declare(strict_types=1);

/* Copyright (c) 2021 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Wizard;

use ILIAS\UI\Component\Input\Container\Wizard as W;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\Data\Factory as DataFactory;
use ilLanguage;

class StepFactory implements W\StepFactory
{
    private RefineryFactory $refinery;
    private FieldFactory $fields;
    private DataFactory $data_factory;
    private ilLanguage $lng;


    public function __construct(
        RefineryFactory $refinery,
        FieldFactory $fields,
        DataFactory $data_factory,
        ilLanguage $lng
    ) {
        $this->refinery = $refinery;
        $this->fields = $fields;
        $this->data_factory = $data_factory;
        $this->lng = $lng;
    }

    public function refinery() : RefineryFactory
    {
        return $this->refinery;
    }

    public function fields() : FieldFactory
    {
        return $this->fields;
    }

    public function step(
        array $inputs,
        string $label = '',
        string $byline = null
    ) : Step {
        return new Step(
            $this->data_factory,
            $this->refinery,
            $this->lng,
            $inputs,
            $label,
            $byline
        );
    }
}
