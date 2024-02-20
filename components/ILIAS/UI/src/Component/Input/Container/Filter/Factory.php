<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\UI\Component\Input\Container\Filter;

use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Input\Container\Form\FormInput;

/**
 * This is how a factory for filters looks like.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *      The standard filter is the default filter to be used in ILIAS. If there is no good reason
     *      using another filter instance in ILIAS, this is the one that should be used.
     *
     * rules:
     *   usage:
     *     1: Standard filters MUST be used if there is no good reason using another instance.
     *
     * ---
     * @param    array<mixed,FormInput>    $inputs
     * @return    \ILIAS\UI\Component\Input\Container\Filter\Standard
     */
    public function standard(
        array $inputs
    ): Standard;
}
