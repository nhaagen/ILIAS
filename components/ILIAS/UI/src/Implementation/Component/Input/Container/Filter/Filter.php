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

namespace ILIAS\UI\Implementation\Component\Input\Container\Filter;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as CI;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Signal;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Implementation\Component\Input\QueryParamsFromServerRequest;

use ILIAS\UI\Implementation\Component\Input;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Implementation\Component\Input\StackedInputData;
use ILIAS\UI\Implementation\Component\Input\Container\Container;

/**
 * This implements commonalities between all Filters.
 */
abstract class Filter extends Container implements C\Input\Container\Filter\Filter
{
    use ComponentHelper;
    use JavaScriptBindable;


    /**
     * @var string|Signal
     */
    protected $toggle_action_on = '#';

    /**
     * @var string|Signal
     */
    protected $toggle_action_off = '#';

    /**
     * @var string|Signal
     */
    protected $expand_action = '#';

    /**
     * @var string|Signal
     */
    protected $collapse_action = '#';

    /**
     * @var string|Signal
     */
    protected $apply_action = '#';

    /**
     * @var string|Signal
     */
    protected $reset_action = '#';


    protected C\Input\Group $input_group;

    /**
     * @var bool[]
     */
    protected array $is_input_rendered = [];

    protected bool $is_activated = true;

    protected bool $is_expanded = false;

    protected Signal $update_signal;

    /**
     * For the implementation of NameSource.
     */
    private int $count = 0;
    private array $used_names = [];


    protected ?ServerRequestInterface $request = null;
    protected Input\ArrayInputData $stored_input;


    /**
     * @param C\Input\Container\Form\FormInput[] $inputs
     */
    public function __construct(
        protected SignalGeneratorInterface $signal_generator,
        Input\NameSource $name_source,
        CI\Input\Field\Factory $field_factory,
        array $inputs
    ) {
        parent::__construct($name_source);

        $classes = ['\ILIAS\UI\Component\Input\Container\Filter\FilterInput'];
        $this->checkArgListElements("input", $inputs, $classes);

        $this->initSignals();

        $this->input_group = $field_factory->group($inputs)
            ->withNameFrom($name_source)
            ->withDedicatedName('filter');
        //global $DIC;
        //$this->input_group = $DIC['ui.factory']->input()->viewControl()->group($inputs)->withDedicatedName('filter');
        $this->stored_input = new Input\ArrayInputData([]);
    }


    /**
     * @inheritdoc
     */
    public function getToggleOnAction()
    {
        return $this->toggle_action_on;
    }

    /**
     * @inheritdoc
     */
    public function getToggleOffAction()
    {
        return $this->toggle_action_off;
    }

    /**
     * @inheritdoc
     */
    public function getExpandAction()
    {
        return $this->expand_action;
    }

    /**
     * @inheritdoc
     */
    public function getCollapseAction()
    {
        return $this->collapse_action;
    }


    /**
     * @inheritdoc
     */
    public function getApplyAction()
    {
        return $this->apply_action;
    }

    /**
     * @inheritdoc
     */
    public function getResetAction()
    {
        return $this->reset_action;
    }


    /**
     * @inheritdocs
     */
    public function getInputs(): array
    {
        return $this->getInputGroup()->getInputs();
    }

    /**
     * @inheritdocs
     */
    public function isInputRendered(): array
    {
        return $this->is_input_rendered;
    }

    /**
     * @inheritdocs
     */
    public function getInputGroup(): C\Input\Group
    {
        return $this->input_group;
    }

    /**
     * @inheritdoc
     */
    public function withRequest(ServerRequestInterface $request): Container
    {
        //$clone = parent::withRequest($request);
        $clone = clone $this;

        $clone->request = $request;
        return $clone;
    }

    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }


    /**
     * @inheritdocs
     */
    public function getData()
    {
        $content = $this->getInputGroup()->getContent();
        if (!$content->isok()) {
            return null;
        }

        return $content->value();
    }

    /**
     * Extract post data from request.
     */
    protected function extractParamData(ServerRequestInterface $request): CI\Input\InputData
    {
        return new QueryParamsFromServerRequest($request);
    }

    /**
     * Implementation of NameSource
     *
     * @inheritdoc
     */
    public function XXXgetNewName(): string
    {
        $name = "filter_input_$this->count";
        $this->count++;

        return $name;
    }

    /**
     * Implementation of NameSource
     * for using dedicated names in filter fields
     */
    public function XXXgetNewDedicatedName(string $dedicated_name): string
    {
        if ($dedicated_name == 'filter_input') {
            return $this->getNewName();
        }
        if (in_array($dedicated_name, $this->used_names)) {
            return $dedicated_name . '_' . $this->count++;
        } else {
            $this->used_names[] = $dedicated_name;
            return $dedicated_name;
        }
    }

    /**
     * @inheritdoc
     */
    public function isActivated(): bool
    {
        return $this->is_activated;
    }

    /**
     * @inheritdoc
     */
    public function withActivated(): Filter
    {
        $clone = clone $this;
        $clone->is_activated = true;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withDeactivated(): Filter
    {
        $clone = clone $this;
        $clone->is_activated = false;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function isExpanded(): bool
    {
        return $this->is_expanded;
    }

    /**
     * @inheritdoc
     */
    public function withExpanded(): Filter
    {
        $clone = clone $this;
        $clone->is_expanded = true;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withCollapsed(): Filter
    {
        $clone = clone $this;
        $clone->is_expanded = false;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getUpdateSignal(): Signal
    {
        return $this->update_signal;
    }

    /**
     * @inheritdoc
     */
    public function withResetSignals(): Filter
    {
        $clone = clone $this;
        $clone->initSignals();
        return $clone;
    }

    /**
     * Set the update signal for this input
     */
    protected function initSignals(): void
    {
        $this->update_signal = $this->signal_generator->create();
    }

    protected function extractRequestData(ServerRequestInterface $request): InputData
    {
        $internal_input_data = new Input\ArrayInputData($this->getComponentInternalValues());

        return new StackedInputData(
            new QueryParamsFromServerRequest($request),
            $this->stored_input,
            $internal_input_data,
        );
    }

    /**
    * @return array     with key input name and its current value
    */
    public function getComponentInternalValues(
        C\Input\Group $component = null,
        array $input_values = []
    ): array {
        if(is_null($component)) {
            $component = $this->getInputGroup();
        }
        foreach ($component->getInputs() as $input) {
            if ($input instanceof C\Input\Group) {
                $input_values = $this->getComponentInternalValues($input, $input_values);
                //$input_values = $this->getComponentInternalValues($input->getInputGroup(), $input_values);
            }
            if ($input instanceof HasInputGroup) {
                $input_values = $this->getComponentInternalValues($input->getInputGroup(), $input_values);
            }
            if($name = $input->getName()) {
                $input_values[$input->getName()] = $input->getValue();
            }
        }

        return $input_values;
    }
}
