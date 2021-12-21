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
        Storage $storage,
        StepBuilder $builder,
        string $post_url, //'string' is a legacy from form
        string $title,
        string $description
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
