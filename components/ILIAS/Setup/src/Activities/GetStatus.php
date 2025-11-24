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

namespace ILIAS\Setup\Activities;

use ILIAS\Component\Dependencies\Name;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\Data\Result;
use ILIAS\Data\Text;
use ILIAS\Data\Description;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Setup\CLI\StatusCommand;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\Output;

class GetStatus extends \ILIAS\Component\Activities\Query
{
    public function __construct(
        private StatusCommand $status_command,
        private Input $symfony_input,
        private Output $symfony_output,
        private DataFactory $data_factory,
        private UIFactory $ui_factory
    ) {
    }

    public function getDescription(): Text\SimpleDocumentMarkdown
    {

        return $this->data_factory->text()->markdown()->simpleDocument('
            This is the description of Query "GetStatus".
        ');
    }

    public function getInputDescription(): FormInput
    {
        return $this->ui_factory->input()->field()->hidden();
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
    }

    public function isAllowedToPerform(int $usr_id, mixed $parameters): bool
    {
    }

    public function perform(mixed $parameters): mixed
    {
        $parameters = $this->getInputDescription()->withValue($parameters)->getValue();
        try {
            $this->status_command->run($this->symfony_input, $this->symfony_output);
            $result = $this->symfony_output->fetch();

        } catch (\Exception $e) {
            return $this->data_factory->error($e->getMessage());
        }
        return $this->data_factory->ok($result);
    }

    public function maybePerformAs(int $usr_id, array $raw_parameters): Result
    {
    }
}
