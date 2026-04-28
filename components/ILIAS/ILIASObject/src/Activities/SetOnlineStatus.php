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

namespace ILIAS\ILIASObject\Activities;

use ILIAS\Component\Dependencies\Name;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\Data\Result;
use ILIAS\Data\Text;
use ILIAS\Data\Description;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Factory as UIFactory;

class SetOnlineStatus extends \ILIAS\Component\Activities\Command implements ObjectActivity
{
    public function __construct(
        private DataFactory $data_factory,
        private UIFactory $ui_factory
    ) {
    }

    public function getDescription(): Text\SimpleDocumentMarkdown
    {

        return $this->data_factory->text()->markdown()->simpleDocument('
            This sets the online status for an ILIAS Object.
        ');
    }

    public function getInputDescription(): FormInput
    {
        $ff = $this->ui_factory->input()->field();
        return $ff->group(
            [
                $ff->numeric('obj_id'),
                $ff->checkbox('online'),
            ]
        );
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        return $f->bool(
            $this->data_factory->text()->markdown()->simpleDocument(
                'returns true if the object is online and false, if the object is offline'
            )
        );
    }

    public function isAllowedToPerform(int $usr_id, mixed $parameters): bool
    {
    }

    public function perform(mixed $parameters): mixed
    {

        $parameters = $this->getInputDescription()->withValue($parameters)->getValue();
        try {
            $result = $parameters;//actually do something
            //throw new \Exception('dddd');

        } catch (\Exception $e) {
            return $this->data_factory->error($e->getMessage());
        }
        return $this->data_factory->ok($result);
    }

    public function maybePerformAs(int $usr_id, array $raw_parameters): Result
    {
    }
}
