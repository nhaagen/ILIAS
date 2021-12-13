<?php declare(strict_types=1);

/* Copyright (c) 2021 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Wizard;

use ILIAS\UI\Component\Input\Container\Wizard as W;

/**
 * Factory for the Wizard Containers
 */
class Factory implements W\Factory
{
    public function step($field_factory) : W\Step
    {
        return new Step($field_factory, []);
    }

    /**
     * @inheritdoc
     */
    public function dynamic(
        string $title,
        string $description,
        \Closure $completion_condition,
        W\StepBuilder $builder
    ) : W\Dynamic {
        return new Dynamic($title, $description, $completion_condition, $builder);
    }


    /**
     * @inheritdoc
     */
    public function staticsequence(
        string $title,
        string $description,
        array $steps
    ) : W\StaticSequence {
    }
}
