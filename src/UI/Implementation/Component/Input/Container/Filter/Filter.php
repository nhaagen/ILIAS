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
use Component\Input\Group as InputGroup;
use ILIAS\UI\Component\Input\Container\Filter\FilterInput;

/**
 * This implements commonalities between all Filters.
 */
abstract class Filter implements C\Input\Container\Filter\Filter, CI\Input\NameSource
{
    use ComponentHelper;
    use JavaScriptBindable;

    /**
     * @var string|Signal
     */
    protected $toggle_action_on;

    /**
     * @var string|Signal
     */
    protected $toggle_action_off;

    /**
     * @var string|Signal
     */
    protected $expand_action;

    /**
     * @var string|Signal
     */
    protected $collapse_action;

    /**
     * @var string|Signal
     */
    protected $apply_action;

    /**
     * @var string|Signal
     */
    protected $reset_action;


    protected C\Input\Field\Group $input_group;

    /**
     * @var bool[]
     */
    protected array $is_input_rendered;

    protected bool $is_activated;

    protected bool $is_expanded;

    protected C\Input\Field\Factory $field_factory;

    protected SignalGeneratorInterface $signal_generator;

    protected Signal $update_signal;

    /**
     * For the implementation of NameSource.
     */
    private int $count = 0;
    private array $used_names = [];

    protected ?ServerRequestInterface $request = null;

    protected array $status_field_names = [];

    /**
     * @param string|Signal $toggle_action_on
     * @param string|Signal $toggle_action_off
     * @param string|Signal $expand_action
     * @param string|Signal $collapse_action
     * @param string|Signal $apply_action
     * @param string|Signal $reset_action
     * @param C\Input\Container\Form\FormInput[] $inputs
     * @param bool[] $is_input_rendered
     */
    public function __construct(
        SignalGeneratorInterface $signal_generator,
        CI\Input\Field\Factory $field_factory,
        $toggle_action_on,
        $toggle_action_off,
        $expand_action,
        $collapse_action,
        $apply_action,
        $reset_action,
        array $inputs,
        array $is_input_rendered,
        bool $is_activated,
        bool $is_expanded
    ) {
        $this->signal_generator = $signal_generator;
        $this->field_factory = $field_factory;
        $this->toggle_action_on = $toggle_action_on;
        $this->toggle_action_off = $toggle_action_off;
        $this->expand_action = $expand_action;
        $this->collapse_action = $collapse_action;
        $this->apply_action = $apply_action;
        $this->reset_action = $reset_action;
        //No further handling for actions needed here, will be done in constructors of the respective component

        $classes = ['\ILIAS\UI\Component\Input\Container\Filter\FilterInput'];
        $this->checkArgListElements("input", $inputs, $classes);
        foreach ($is_input_rendered as $r) {
            $this->checkBoolArg("is_input_rendered", $r);
        }
        $this->initSignals();

        $this->is_input_rendered = $is_input_rendered;
        $this->is_activated = $is_activated;
        $this->is_expanded = $is_expanded;

        $status_fields = [];
        foreach(array_keys($inputs) as $index => $possible_filter) {
            $status_name = 'filter_status_' . $index + 1;
            $status_fields[$status_name] = $field_factory->text($status_name, '')
            //$status_fields[$status_name]  = $field_factory->hidden()
                ->withDedicatedName($status_name)
                ->withValue((string)$is_input_rendered[$index]);
        }
        $toggle_states = [
            'filter_all_active' => $field_factory->text('filter_all_active', '')
                ->withDedicatedName('filter_all_active')
                ->withValue((string)$is_activated),
            'filter_all_expanded' => $field_factory->text('filter_all_expanded', '')
                ->withDedicatedName('filter_all_expanded')
                ->withValue((string)$is_expanded),

        ];
        $this->status_field_names = array_merge(array_keys($status_fields), array_keys($toggle_states));
        $inputs = array_merge($inputs, $status_fields, $toggle_states);
        $this->input_group = $field_factory->group($inputs)
            ->withDedicatedName('filter')
            ->withNameFrom($this);

        /*
        foreach($inputs as $k => $in) {
            print '<li>' . $k . '--' . $in->getValue();
        }
        print '<hr>';
        foreach($this->input_group->getInputs() as $k => $in) {
            print '<li>' . $k . '--' . $in->getName();
        }
        */
        //die();

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
    public function getInputGroup(): C\Input\Field\Group
    {
        return $this->input_group;
    }

    /**
     * @inheritdocs
     */
    public function withRequest(ServerRequestInterface $request)
    {
        $this->request = $request;
        $param_data = $this->extractParamData($request);

        $clone = clone $this;
        try {
            $clone->input_group = $this->getInputGroup()->withInput($param_data);
        } catch (\Exception $e) {
            //TODO: use StackedInputData as in https://github.com/ILIAS-eLearning/ILIAS/pull/6520
        }

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
        return array_filter(
            $content->value(),
            static fn($k) => ! str_starts_with($k, 'filter_status_'),
            ARRAY_FILTER_USE_KEY
        );
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
    public function getNewName(): string
    {
        $name = "filter_input_$this->count";
        $this->count++;

        return $name;
    }

    /**
     * Implementation of NameSource
     * for using dedicated names in filter fields
     */
    public function getNewDedicatedName(string $dedicated_name): string
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

    public function getComponentInternalNames(
        InputGroup $component = null,
        array $names = []
    ): array {
        if(is_null($component)) {
            $component = $this->getInputGroup();
        }
        foreach ($component->getInputs() as $input) {
            if ($input instanceof InputGroup) {
                $names = $this->getComponentInternalValues($input, $names);
            }
            if ($input instanceof HasInputGroup) {
                $names = $this->getComponentInternalValues($input->getInputGroup(), $names);
            }
            if($name = $input->getName()) {
                $names[] = $input->getName();
            }
        }
        return $names;
    }

    /**
     * @return FilterInput[]
     */
    public function getUserEditableInputs(): array
    {
        $inputs = [];
        foreach ($this->getInputGroup()->getInputs() as $input) {
            $add = true;
            foreach($this->status_field_names as $status_field_name) {
                if(str_ends_with($input->getName(), $status_field_name)) {
                    $add = false;
                    continue;
                }
            }
            if($add) {
                $inputs[] = $input;
            }
        }
        return $inputs;
    }

}
