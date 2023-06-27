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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Result;
use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\Data\Factory as DataFactory;
use ilLanguage;
use ILIAS\Refinery\Constraint;
use Closure;
use ILIAS\Data\Result\Ok;
use InvalidArgumentException;
use ILIAS\UI\Implementation\Component\Input\InputGroup;

/**
 * This implements the group input.
 */
class Group extends Field implements C\Input\Field\Group
{
    use ComponentHelper;
    use InputGroup;

    protected ilLanguage $lng;

    /**
     * @param \ILIAS\UI\Implementation\Component\Input\Input[] $inputs
     */
    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        ilLanguage $lng,
        array $inputs,
        string $label,
        ?string $byline = null
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);
        $this->checkArgListElements("inputs", $inputs, InputInternal::class);
        $this->inputs = $inputs;
        $this->lng = $lng;
    }

    public function withRequired(bool $is_required, ?Constraint $requirement_constraint = null): self
    {
        $clone = parent::withRequired($is_required, $requirement_constraint);
        $clone->inputs = array_map(fn ($i) => $i->withRequired($is_required, $requirement_constraint), $this->inputs);
        return $clone;
    }

    public function isRequired(): bool
    {
        if ($this->is_required) {
            return true;
        }
        foreach ($this->getInputs() as $input) {
            if ($input->isRequired()) {
                return true;
            }
        }
        return false;
    }

    public function withOnUpdate(Signal $signal): self
    {
        $clone = parent::withOnUpdate($signal);
        $clone->inputs = array_map(fn ($i) => $i->withOnUpdate($signal), $this->inputs);
        return $clone;
    }

    /**
     * @inheritdoc
     */
    protected function getConstraintForRequirement(): ?Constraint
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getUpdateOnLoadCode(): Closure
    {
        return function () {
            /*
             * Currently, there is no use case for Group here. The single Inputs
             * within the Group are responsible for handling getUpdateOnLoadCode().
             */
        };
    }

    /**
     * @inheritdoc
     */
    public function getContent(): Result
    {
        if (0 === count($this->getInputs())) {
            return new Ok([]);
        }
        return parent::getContent();
    }

    /**
     * @inheritdoc
     */
    public function isClientSideValueOk($value): bool
    {
        if (!is_array($value)) {
            return false;
        }
        if (count($this->getInputs()) !== count($value)) {
            return false;
        }
        foreach ($this->getInputs() as $key => $input) {
            if (!array_key_exists($key, $value)) {
                return false;
            }
            if (!$input->isClientSideValueOk($value[$key])) {
                return false;
            }
        }
        return true;
    }
}
