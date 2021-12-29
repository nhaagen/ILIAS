<?php declare(strict_types=1);

use ILIAS\UI\Component\Input\Container\Wizard;
use ILIAS\UI\Implementation\Component\Input\Container\Wizard as WizardImpl;
use ILIAS\UI\Implementation\Component\Input\Field\Factory as FieldFactory;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Implementation\Component\SignalGenerator;

//use Psr\Http\Message\ServerRequestInterface;
/**
 * Test the wizard
 */
class DynamicWizardTest extends ILIAS_UI_TestBase
{
    public function getWizardFactory() : WizardImpl\Factory
    {
        $data_factory = new DataFactory();
        $language = $this->createMock(ilLanguage::class);
        $refinery_factory = new RefineryFactory($data_factory, $language);
        $field_factory = new FieldFactory(
            new SignalGenerator(),
            $data_factory,
            $refinery_factory,
            $language
        );

        return new WizardImpl\Factory(
            $refinery_factory,
            $field_factory,
            $data_factory,
            $language
        );
    }

    public function setUp() : void
    {
        $this->storage = new class() implements Wizard\Storage {
            protected $data = [];
            public function set(mixed $data) : void
            {
                $this->$data = $data;
            }
            public function get() : mixed
            {
                return $this->data;
            }
        };

        $this->magic = new class() implements Wizard\StepBuilder {
            public function isComplete(mixed $data) : bool
            {
                return is_array($data) && array_sum($data) === 10;
            }
            public function build(Wizard\StepFactory $factory, mixed $data) : Wizard\Step
            {
                $inputs = [];
                foreach ($data as $idx => $summand) {
                    $inputs[] = $factory->fields()->numeric('')->withValue($summand);
                }
                $inputs[] = $factory->fields()->numeric('');
                return $factory->step($inputs);
            }
        };
        $this->url = '#';
        $this->label = 'count to ten';
        $this->description = 'add numbers';

        $this->wizard = $this->getWizardFactory()->dynamic(
            $this->storage,
            $this->magic,
            $this->url,
            $this->label,
            $this->description
        );
    }

    public function testDynamicIsATrueWizard() : void
    {
        $this->assertInstanceOf(Wizard\Wizard::class, $this->wizard);
        $this->assertInstanceOf(Wizard\Dynamic::class, $this->wizard);
    }

    public function testBaseAttributes() : void
    {
        $this->assertEquals($this->title, $this->wizard->getTitle());
        $this->assertEquals($this->description, $this->wizard->getDescription());
        $this->assertInstanceOf(WizardImpl\StepBuilder::class, $this->wizard->getStepBuilder());
    }
}
