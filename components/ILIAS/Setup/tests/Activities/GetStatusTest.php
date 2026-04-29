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

namespace ILIAS\Tests\Setup\Activities;

use PHPUnit\Framework\TestCase;
use ILIAS\Setup;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI;

//use ILIAS\Refinery\Factory as Refinery;

class GetStatusTest extends TestCase
{
    protected Setup\CLI\StatusCommand $cli_command;
    protected \Symfony\Component\Console\Input\StringInput $symfony_input;
    protected \Symfony\Component\Console\Output\BufferedOutput $symfony_output;
    protected DataFactory $data_factory;
    protected UI\Component\Input\Factory $input_factory;

    public function setUp(): void
    {
        $this->cli_command = $this->createMock(Setup\CLI\StatusCommand::class);
        $this->symfony_input = new \Symfony\Component\Console\Input\StringInput('');
        $this->symfony_output = new \Symfony\Component\Console\Output\BufferedOutput();

        $this->data_factory = $this->createMock(DataFactory::class);
        $this->data_factory
            ->method('ok')
            ->willReturn(
                new \ILIAS\Data\Result\Ok(true)
            );
        $this->data_factory
            ->method('userId')
            ->willReturn(
                new \ILIAS\Data\UserId(6)
            );

        $form_factory = $this->createMock(UI\Component\Input\Container\Form\Factory::class);
        $form_factory
            ->method('standard')
            ->willReturn(
                $this->createMock(UI\Component\Input\Container\Form\Standard::class)
            );
        $container_factory = $this->createMock(UI\Component\Input\Container\Factory::class);
        $container_factory
            ->method('form')
            ->willReturn($form_factory);

        $field_factory = $this->createMock(UI\Component\Input\Field\Factory::class);
        $this->input_factory = $this->createMock(UI\Component\Input\Factory::class);
        $this->input_factory
            ->method('container')
            ->willReturn($container_factory);
        $this->input_factory
            ->method('field')
            ->willReturn($field_factory);
    }

    public function getActivity(): Setup\Activities\GetStatus
    {
        return new Setup\Activities\GetStatus(
            $this->cli_command,
            $this->symfony_input,
            $this->symfony_output,
            $this->data_factory,
            $this->input_factory
        );
    }

    public function testActivityGetStatusType(): void
    {
        $this->assertEquals(
            \ILIAS\Component\Activities\ActivityType::Query,
            $this->getActivity()->getType()
        );
    }
    public function testActivityGetStatusPerforms(): void
    {
        $this->cli_command
            ->expects($this->once())
            ->method('run');

        $this->assertIsString($this->getActivity()->perform(null));
    }

    public function testActivityGetStatusMaybePerformAs(): void
    {
        $this->cli_command
            ->expects($this->once())
            ->method('run');

        $this->assertTrue(
            $this->getActivity()->maybePerformAs(
                $this->input_factory,
                $this->data_factory,
                $this->data_factory->userId(6),
                []
            )->isOK()
        );
    }
}
