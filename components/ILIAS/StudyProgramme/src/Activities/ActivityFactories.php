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

namespace ILIAS\StudyProgramme\Activities;

/*
use ILIAS\Component\Dependencies\Name;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\Data\Result;
use ILIAS\Data\Text;
use ILIAS\Data\Description;
*/

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Numeric;
use ILIAS\UI\Implementation\Component\Input\ArrayInputData;
use ILIAS\Data\Result;
use ILIAS\Data\Text;

class ActivityFactories
{
    public function __construct(
        private DataFactory $data_factory,
        private FieldFactory $field_factory,
        private Refinery $refinery
    ) {

    }

    public function md(string $text): Text\SimpleDocumentMarkdown
    {
        return $this->data_factory->text()->markdown()->simpleDocument($text);
    }

    public function fieldFactory(): FieldFactory
    {
        return $this->field_factory;
    }

    public function idField(string $label, string $description): Numeric
    {
        return $this->field_factory->numeric($label, $description)
            ->withStepSize(1)
            ->withRequired(true, $this->refinery->int()->isGreaterThan(0))
            /*->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    fn(int $id) => $this->data_factory->objId($id)
                )
            )
            */
        ;
    }

    public function toInputData(array $parameters): InputData
    {
        return new ArrayInputData($parameters);
    }

    public function error(string|\Exception $error): Result
    {
        return $this->data_factory->error($error);
    }

    public function ok(mixed $value): Result
    {
        return $this->data_factory->ok($value);
    }

}
