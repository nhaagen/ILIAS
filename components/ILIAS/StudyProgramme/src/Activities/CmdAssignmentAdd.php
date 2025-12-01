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

namespace ILIAS\StudyProgramme\Activities\Assignment;

use ILIAS\Component\Activities\Command;
use ILIAS\Data\Text;
use ILIAS\Data\Result;
use ILIAS\Data\Description;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\StudyProgramme\Activities\ActivityFactories;

class CmdAssignmentAdd extends Command
{
    public function __construct(
        private ActivityFactories $f
    ) {
    }

    public function getDescription(): Text\SimpleDocumentMarkdown
    {
        return $this->f->md('
            This is the description of Cmd "AddAssignment" for Study Programme.
        ');
    }

    public function getInputDescription(): FormInput
    {
        return $this->f->fieldFactory()->group([
            $this->f->idField('prg_obj_id', 'The StudyProgramme\'s object ID'),
            $this->f->idField('usr_obj_id', 'The User\'s  ID to assign')
            //$this->f->idField('acting_usr_obj_id', 'The User\'s  ID performing the action')
        ]);
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        return $f->int($this->f->md('returns the (new) ID of the assignment'));
    }

    public function isAllowedToPerform(int $usr_id, mixed $parameters): bool
    {
        //prg->getStatus !
        //
    }

    public function perform(mixed $parameters): mixed
    {
        $parameters = $this->getInputDescription()->withValue($parameters)->getValue();
        try {
            $result = null;//actually do something
            $result = $parameters;//actually do something
            //throw new \Exception('dddd');
        } catch (\Exception $e) {
            return $this->f->error($e->getMessage());
        }
        return $this->f->ok($result);
    }

    public function maybePerformAs(int $usr_id, array $raw_parameters): Result
    {
        //transform params
        $parameters = $this->f->toInputData($raw_parameters);
        $parameters = $this->getInputDescription()->withInput($parameters)->getValue();

        if (!$this->isAllowedToPerform($usr_id, $parameters)) {
            return $this->f->error('not allowed');
        }

        return $this->f->ok($this->perform($parameters));

    }

}
