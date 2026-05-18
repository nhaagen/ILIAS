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

namespace ILIAS\Component\Activities;

use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

trait ObjectActivityTrait
{
    public function getCheckedInputDescription(FieldFactory $f): FormInput
    {
        // [FEATURE] UI: introduce catamorphism for UI components.
        // https://github.com/ILIAS-eLearning/ILIAS/commit/244225db385c0be0732b6662b48c7d0cf03acd6a
        $input_description = $this->getInputDescription($f);
        $dedicated_names = $input_description->reduceWith(
            fn($c, $res) => array_merge([$c->getDedicatedName()], ...$res)
        );
        if (!in_array('id', $dedicated_names)) {
            throw new \LogicException('There is no "id" field in the Input.');
        }
        return $input_description;
    }
}
