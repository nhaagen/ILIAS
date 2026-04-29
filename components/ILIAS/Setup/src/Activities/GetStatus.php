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
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\Data\Result;
use ILIAS\Data\Text;
use ILIAS\Data\UserId;
use ILIAS\Data\Description;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Setup\CLI\StatusCommand;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\Output;

class GetStatus extends \ILIAS\Component\Activities\Query
{
    public function __construct(
        private StatusCommand $status_command,
        private Input $symfony_input,
        private Output $symfony_output,
        protected DataFactory $data_factory,
    ) {
    }

    public function getDescription(): Text\SimpleDocumentMarkdown
    {
        return $this->data_factory->text()->markdown()->simpleDocument(
            'Retrieve status information about the installation.'
        );
    }

    public function getInputDescription(FieldFactory $f): FormInput
    {
        return $f->hidden();
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        $md = fn(string $markdown) => $this->data_factory->text()->markdown()->simpleDocument($markdown);

        return $f->map(
            $md(implode("\n\r", [
                'Status information about the installation.',
                'Result of Setup\CLI\StatusCommand.',
                'Format is YAML.',
            ])),
            $f->string(
                $md('The component\'s name')
            ),
            $f->map(
                $md('The metrics of a certain Step or Objective'),
                $f->string(
                    $md('The topic of the metric')
                ),
                $f->map(
                    $md('Measurements/Aggregations'),
                    $f->string(
                        $md('the topic')
                    ),
                    $f->int(
                        $md('the value (actually \'mixed\')')
                    )
                )
            )
        );
    }

    public function isAllowedToPerform(UserId $usr_id, mixed $parameters): bool
    {
        return true;
    }

    public function perform(mixed $parameters): string
    {
        try {
            $this->status_command->run($this->symfony_input, $this->symfony_output);
            return $this->symfony_output->fetch();

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
