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

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\Data\URI;
use ILIAS\Refinery\Factory as Refinery;

class LauncherInlineTest extends ILIAS_UI_TestBase
{
    public function setUp(): void
    {
        $this->df = new \ILIAS\Data\Factory();
    }

    protected function getInputFactory(): I\Input\Field\Factory
    {
        $this->language = $this->createMock(ilLanguage::class);
        return new I\Input\Field\Factory(
            $this->createMock(I\Input\UploadLimitResolver::class),
            new I\SignalGenerator(),
            $this->df,
            new Refinery($this->df, $this->language),
            $this->language
        );
    }

    protected function getFormFactory(): I\Input\Container\Form\Factory
    {
        return new I\Input\Container\Form\Factory(
            $this->getInputFactory(),
            new DefNamesource()
        );
    }

    protected function getIconFactory(): I\Symbol\Icon\Factory
    {
        return new I\Symbol\Icon\Factory();
    }

    protected function getURI(): URI
    {
        return $this->df->uri('http://localhost/ilias.php');
    }

    protected function getLauncher(): I\Launcher\Inline
    {
        $target = $this->df->link('LaunchSomething', $this->getURI());
        return new I\Launcher\Inline(
            $this->getFormFactory(),
            $target
        );
    }
    protected function getMessageBox(): I\MessageBox\MessageBox
    {
        return new I\MessageBox\MessageBox(C\MessageBox\MessageBox::INFO, 'message');
    }

    public function testLauncherInlineConstruction(): void
    {
        $l = $this->getLauncher();
        $this->assertInstanceOf(C\Launcher\Inline::class, $l);
        $this->assertEquals($this->df->link('LaunchSomething', $this->getURI()), $l->getTarget());
        $this->assertEquals('LaunchSomething', $l->getButtonLabel());
        $this->assertTrue($l->isLaunchable());
        $this->assertNull($l->getStatusIcon());
        $this->assertNull($l->getStatusMessage());
        $this->assertNull($l->getForm());
    }

    public function testLauncherInlineBasicModifier(): void
    {
        $msg = $this->getMessageBox();
        $icon = $this->getIconFactory()->standard('course', 'some icon');
        $l = $this->getLauncher()
            ->withDescription('some description')
            ->withButtonLabel('different label', false)
            ->withStatusMessage($msg)
            ->withStatusIcon($icon)
        ;

        $this->assertEquals($this->df->link('LaunchSomething', $this->getURI()), $l->getTarget());
        $this->assertEquals('different label', $l->getButtonLabel());
        $this->assertfalse($l->isLaunchable());
        $this->assertEquals($msg, $l->getStatusMessage());
        $this->assertEquals($icon, $l->getStatusIcon());
        $this->assertNull($l->getForm());
    }

    public function testLauncherInlineWithFields(): void
    {
        $ff = $this->getInputFactory();
        $group = $ff->group([$ff->checkbox('Understood', 'ok')]);
        $evaluation = fn (Result $result, Launcher &$launcher) => true;
        $instruction = $this->getMessageBox();
        $l = $this->getLauncher()
            ->withInputs($group, $evaluation, $instruction);

        $form = $this->getFormFactory()->standard(
            (string)$l->getTarget()->getURL(),
            [$group]
        );

        $this->assertEquals($form, $l->getForm());
        $this->assertEquals($evaluation, $l->getEvaluation());
        $this->assertEquals($instruction, $l->getInstruction());
    }
}
