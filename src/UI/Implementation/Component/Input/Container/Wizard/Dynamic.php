<?php declare(strict_types=1);

/* Copyright (c) 2022 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Wizard;

use ILIAS\UI\Component\Input\Container\Wizard as W;
use ILIAS\UI\Implementation\Component\Input\Container\Wizard\WizardInputNameSource;
use Psr\Http\Message\ServerRequestInterface;

class Dynamic extends Wizard implements W\Dynamic
{
    protected W\StepFactory $step_factory;
    protected W\StepBuilder $builder;
    
    public function __construct(
        W\StepFactory $step_factory,
        WizardInputNameSource $name_source,
        W\Storage $storage,
        W\StepBuilder $builder,
        string $post_url,
        string $title,
        string $description
    ) {
        $this->step_factory = $step_factory;
        $this->builder = $builder;

        parent::__construct(
            $storage,
            $name_source,
            $post_url,
            $title,
            $description
        );
    }

    public function getStepBuilder() : W\StepBuilder
    {
        return $this->builder;
    }
}
