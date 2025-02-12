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

use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component as C;
use ILIAS\Refinery\Constraint;
use Closure;
use ilLanguage;

class DynamicGroup extends HasDynamicInputsBase implements C\Input\Field\DynamicGroup
{
    public function __construct(
        protected ilLanguage $language,
        protected DataFactory $data_factory,
        protected Refinery $refinery,
        protected $template,
        protected string $label,
        protected ?string $byline
    ) {
        parent::__construct(
            $language,
            $data_factory,
            $refinery,
            $label,
            $template,
            $byline
        );
    }

    public function getUpdateOnLoadCode(): Closure
    {
        return static function () {
        };
    }

    protected function getConstraintForRequirement(): ?Constraint
    {
        if ($this->requirement_constraint !== null) {
            return $this->requirement_constraint;
        }

        return $this->refinery->custom()->constraint(
            function ($value) {
                return (is_array($value) && count($value) > 0);
            },
            function ($txt, $value) {
                return $txt("dyngroup_required_fields");
            },
        );
    }

    protected function isClientSideValueOk($value): bool
    {
        if (!is_array($value)) {
            return false;
        }
        foreach ($value as $data) {
            if ($this->hasMetadataInputs()) {
                if (!$this->dynamic_input_template->isClientSideValueOk($data)) {
                    return false;
                }
            }
        }

        return true;
    }
}
