<?php declare(strict_types=1);

/* Copyright (c) 2021 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Container\Wizard;

/**
 * This describes a highly customizable Wizard
 */
interface Dynamic extends Wizard
{
    public function getStepBuilder() : StepBuilder;
}
