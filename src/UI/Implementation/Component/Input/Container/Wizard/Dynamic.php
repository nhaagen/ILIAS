<?php declare(strict_types=1);

/* Copyright (c) 2021 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Wizard;

use ILIAS\UI\Component\Input\Container\Wizard as W;

/**
 *
 */
class Dynamic extends Wizard implements W\Dynamic
{
    protected $builder;

    public function __construct(string $title, string $description, callable $completion_condition, $builder)
    {
        $this->builder = $builder;
        parent::__construct($title, $description, $completion_condition);
    }

    public function getStepBuilder() : W\StepBuilder
    {
        return $this->builder;
    }
}
