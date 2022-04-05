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
        return $this->current_step >= count($this->step_closures);
    }

    public function build(W\StepFactory $factory, mixed $data) : W\Step
    {
        $step_building_closure = $this->step_closures[$this->current_step];
        $step = $step_building_closure($factory, $data);
        return $step;

        //add a hidden field to relay the step number
        $hidden = $factory->fields()->numeric('hidden', '')
            ->withValue($this->current_step);

        $step_trafo = $factory->refinery()->custom()->transformation(
            function ($v) {
                var_dump($v);
                $step_nr = array_pop($v);
                return [
                    'CURRENT_STEP_FROM_POST' => $step_nr,
                    'POST_VALUES' => $v
                ];
            }
        );

        $inputs = $step->getInputs();
        $inputs['hidden'] = $hidden;
        return $factory->step($inputs, $step->getTitle(), $step->getDescription())
            ->withAdditionalTransformation($step_trafo);
    }


    public function withCurrentStep(int $step) : self
    {
        $clone = clone $this;
        $clone->current_step = $step;
        return $clone;
    }
}
