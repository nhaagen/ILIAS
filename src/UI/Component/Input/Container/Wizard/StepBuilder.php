<?php declare(strict_types=1);

/* Copyright (c) 2022 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Container\Wizard;

/**
 * Implement this (e.g. in an anon-class) to build a Wizard
 */
interface StepBuilder
{
    public function isComplete(mixed $data) : bool;
    public function build(StepFactory $factory, mixed $data) : Step;
}
