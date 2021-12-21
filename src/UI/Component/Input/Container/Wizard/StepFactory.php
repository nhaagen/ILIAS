<?php declare(strict_types=1);

/* Copyright (c) 2021 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Container\Wizard;

use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

/**
 * Factory used internally by Wizards
 */
interface StepFactory
{
    public function refinery() : RefineryFactory;
    public function fields() : FieldFactory;
    public function step(
        array $inputs,
        string $label = '',
        string $byline = null
    ) : Step;
}
