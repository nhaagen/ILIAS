<?php declare(strict_types=1);

/* Copyright (c) 2022 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

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
     *      A Dynamic Wizard builds Forms based on some data until a certain
     *      condition is fulfilled by this data.
     *      It may organize its fields completely without restrictions.
     *      The number of steps is entirely dependent on data.
     *   composition: >
     *      The Dynamic Wizards sports a Form with Inputs and a labeled submit button
     *      below a title and description.
     *      The Steps themselves can also bear title and description.
     *   effect: >
     *      The user keeps submitting the form until the predefined condition is
     *      met and the consuming program continues.
     *   rivals:
     *      StaticSequence: >
     *          The Static Sequence uses a fixed set of fixed steps;
     * rules:
     *   usage:
     *     1: ...
     * ---
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
     *      A Static Sequence is a Wizard with predefined Steps that are shown
     *      one after the other regadless of provided data/input.
     *   composition: >
     *      Next to title description and Form, the Static Sequence will also use
     *      a Linear Workflow Listing to indicate the current step and the overall
     *      progress.
     *   effect: >
     *      When the user sumbits the Form, the next Step is shown and marked
     *      as "current" in the Workflow Listing; further steps are shown as "inaccessible".
     *      The user may jump to previous Steps.
     *   rivals:
     *      dynamic: >
     *          The Dynamic Wizard will calculate its content; the Static Sequence
     *          will run predefined Step by predefined Step.
     *          Also, in dynamic Wizards, a navigation backwards is not possible.
     *
     * rules:
     *   usage:
     *     1: The Static Sequence Wizard MUST have more than one Step.
     *
     * ---
     * @return \ILIAS\UI\Component\Input\Container\Wizard\StaticSequence
     */
    public function staticSequence(
        Storage $storage,
        array $steps, // Step[]
        string $post_url, //'string' is a legacy from form
        string $title,
        string $description
    ) : StaticSequence;
}
