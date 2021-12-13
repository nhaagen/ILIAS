<?php declare(strict_types=1);

/* Copyright (c) 2021 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Container\Wizard;

use ILIAS\UI\Component\Input\Container\Form\Form;

/**
 * Factory for Wizards
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *      X
     *   effect: >
     *      X
     * ---
     * @param
     * @return \ILIAS\UI\Component\Input\Container\Wizard\Dynamic
     */
    public function dynamic(
        string $title,
        string $description,
        \Closure $completion_condition,
        StepBuilder $builder
    ) : Dynamic;


    /**
     * ---
     * description:
     *   purpose: >
     *      X
     *   effect: >
     *      X
     * ---
     * @param
     * @return \ILIAS\UI\Component\Input\Container\Wizard\StaticSequence
     */
    public function staticsequence(
        string $title,
        string $description,
        array $steps // Step[]
    ) : StaticSequence;
}
