<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\Language\Language;
use ILIAS\Refinery\Constraint;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Implementation\Component\Input\DynamicInputDataIterator;
use ILIAS\UI\Implementation\Component\Input\DynamicInputsNameSource;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\Input\InputData;

class DynSection extends HasDynamicInputsBase implements C\Input\Field\DynSection
{
    public function getValue(): array
    {
        return array_map(fn($i) => $i->getValue(), $this->inputs);
    }

    /**
     * @inheritdoc
     */
    protected function getConstraintForRequirement(): ?Constraint
    {
        return null;
    }

    /** ATTENTION: @see GroupInternals::_isClientSideValueOk() */
    protected function isClientSideValueOk($value): bool
    {
        return $this->_isClientSideValueOk($value);
    }

    public function getUpdateOnLoadCode(): \Closure
    {
        return function () {
        };
    }
}
