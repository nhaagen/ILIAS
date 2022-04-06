<?php declare(strict_types=1);

/* Copyright (c) 2022 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Wizard;

use ILIAS\UI\Component\Input\Container\Wizard as W;
use ILIAS\UI\Implementation\Component\Input\Field\Group;

/**
 * The preset StepBuilder for StaticSequences
 */
class StaticStepBuilder implements W\StepBuilder
{
    protected array $step_definitions;
    protected int $current_step = 0;

    public function __construct(array $step_definitions)
    {
        $this->step_definitions = $step_definitions;
    }

    public function isComplete(mixed $data) : bool
    {
        return $this->current_step >= count($this->step_definitions);
    }

    public function build(W\StepFactory $factory, mixed $data) : W\Step
    {
        list($title, $description, $step_building_closure) = $this->step_definitions[$this->current_step];
        $step = $step_building_closure($factory, $data, $title, $description);
        return $step;
    }

    public function withCurrentStep(int $step) : self
    {
        $clone = clone $this;
        $clone->current_step = $step;
        return $clone;
    }

    public function getStepDescriptions() : array
    {
        $ret = [];
        foreach ($this->step_definitions as $idx => $def) {
            list($title, $description, $step_building_closure) = $def;
            $ret[] = [$idx, $title, $description];
        }
        return $ret;
    }
}
