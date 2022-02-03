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
    protected array $step_closures;
    protected int $current_step = 0;

    public function __construct(array $step_closures)
    {
        $this->step_closures = $step_closures;
    }

    public function isComplete(mixed $data) : bool
    {
        return $this->current_step > $count($this->step_closures);
    }

    public function build(W\StepFactory $factory, mixed $data) : W\Step
    {
        $step_building = $this->step_closures[$this->current_step];
        $step = $step_building($factory, $data);
        return $step;
    }
}
