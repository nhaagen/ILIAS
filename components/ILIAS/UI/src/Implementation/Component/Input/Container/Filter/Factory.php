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

namespace ILIAS\UI\Implementation\Component\Input\Container\Filter;

use ILIAS\UI\Component\Input\Container\Filter as F;
use ILIAS\UI\Component\Input\Field;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\Input;

class Factory implements F\Factory
{
    public function __construct(
        protected SignalGeneratorInterface $signal_generator,
        protected Field\Factory $field_factory
    ) {
    }

    /**
     * @inheritdoc
     */
    public function standard(
        array $inputs
    ): F\Standard {
        return new Standard(
            $this->signal_generator,
            new Input\FormInputNameSource(),
            $this->field_factory,
            $inputs,
        );
    }
}
