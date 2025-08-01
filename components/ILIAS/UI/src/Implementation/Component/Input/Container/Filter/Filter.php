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

use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Container\Filter as I;
use ILIAS\UI\Implementation\Component\Signal;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Input;
use ILIAS\UI\Implementation\Component\Input\Container\Container;
use ILIAS\UI\Component as C;
use ILIAS\Refinery\Factory as Refinery;

abstract class Filter extends Container implements I\Filter
{
    use JavaScriptBindable;
    public const DEDICATED_NAME = 'filter';
    public const TOGGLE_FIELD = '__toggle';
    public const EXPAND_FIELD = '__expand';
    public const INACTIVE_FILTERS_FIELD = '__inactive';

    protected Signal $submit_signal;
    protected Signal $expand_signal;
    protected ?ServerRequestInterface $request = null;
    protected Input\ArrayInputData $stored_input;

    /**
     * @param I\FilterInput[] $inputs
     */
    public function __construct(
        SignalGeneratorInterface $signal_generator,
        Input\NameSource $name_source,
        Input\Field\Factory $field_factory,
        Refinery $refinery,
        array $filters,
    ) {
        parent::__construct($name_source);

        $required = array_map(
            fn($filter) => $filter->withRequired(
                true,
                $refinery->custom()->constraint(fn() => true, '')
            ),
            array_filter($filters, fn($filter) => $filter->isRequired())
        );

        $optional = array_filter($filters, fn($filter) => !$filter->isRequired());
        $filters = array_merge($required, $optional);

        $filters[self::TOGGLE_FIELD] = $field_factory->text('toggle')
            ->withDedicatedName(self::TOGGLE_FIELD)
            ->withRequired(true)
            ->withValue('true');
        $filters[self::EXPAND_FIELD] = $field_factory->text('expand')
            ->withDedicatedName(self::EXPAND_FIELD)
            ->withRequired(true)
            ->withValue('true');
        $filters[self::INACTIVE_FILTERS_FIELD] = $field_factory->text('inactive')
            ->withDedicatedName(self::INACTIVE_FILTERS_FIELD)
            ->withRequired(
                true,
                $refinery->custom()->constraint(fn() => true, '')
            )
            ->withValue('');

        $this->setInputGroup(
            $field_factory->group($filters)->withDedicatedName(self::DEDICATED_NAME)
        );
        $this->submit_signal = $signal_generator->create();
        $this->expand_signal = $signal_generator->create();
        $this->stored_input = new Input\ArrayInputData(['']);
    }

    public function getSubmissionSignal(): Signal
    {
        return $this->submit_signal;
    }

    public function getExpandSignal(bool $expand = true): Signal
    {
        $signal = clone $this->expand_signal;
        $signal->addOption('expanded', $expand);
        return $signal;
    }

    public function withRequest(ServerRequestInterface $request): Container
    {
        $expected_key = self::DEDICATED_NAME . '/' . self::TOGGLE_FIELD;
        if (array_key_exists($expected_key, $request->getQueryParams())) {
            $clone = parent::withRequest($request);
        } else {
            $clone = clone $this;
        }
        $clone->request = $request;
        return $clone;
    }

    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }

    /**
    * @inheritDoc
    */
    protected function extractRequestData(ServerRequestInterface $request): C\Input\InputData
    {
        $internal_input_data = new Input\ArrayInputData($this->getComponentInternalValues());
        return new Input\StackedInputData(
            new Input\QueryParamsFromServerRequest($request),
            $this->stored_input,
            //$internal_input_data,
        );
    }

    /**
     * @return array     with key input name and its current value
     */
    public function getComponentInternalValues(
        C\Input\Group $component = null,
        array $input_values = []
    ): array {
        if (is_null($component)) {
            $component = $this->getInputGroup();
        }
        foreach ($component->getInputs() as $input) {
            if ($input instanceof C\Input\Group) {
                $input_values = $this->getComponentInternalValues($input, $input_values);
            }
            if ($input instanceof HasInputGroup) {
                $input_values = $this->getComponentInternalValues($input->getInputGroup(), $input_values);
            }
            if ($name = $input->getName()) {
                $input_values[$input->getName()] = $input->getValue();
            }
        }

        return $input_values;
    }

    public function isActive(
    ): bool {
        $key = self::DEDICATED_NAME . '/' . self::TOGGLE_FIELD;
        return
            !array_key_exists($key, $this->getRequest()?->getQueryParams())
            || in_array(
                $this->getRequest()?->getQueryParams()[$key],
                [null, 'true']
            );
    }

    public function isExpanded(
    ): bool {
        $key = self::DEDICATED_NAME . '/' . self::EXPAND_FIELD;
        return
            !array_key_exists($key, $this->getRequest()?->getQueryParams())
            || in_array(
                $this->getRequest()?->getQueryParams()[$key],
                [null, 'true']
            );
    }

    public function getInactiveFilters(
    ): array {
        $key = self::DEDICATED_NAME . '/' . self::INACTIVE_FILTERS_FIELD;
        if (!array_key_exists($key, $this->getRequest()?->getQueryParams())) {
            return [];
        }
        return explode(':', $this->getRequest()?->getQueryParams()[$key]);
    }

    public function getData()
    {
        $data = parent::getData();
        if ($data === null || $data[self::TOGGLE_FIELD] === 'false') {
            return null;
        }
        return array_filter(
            $data,
            fn($v, $k) => !in_array($k, [
                self::TOGGLE_FIELD,
                self::EXPAND_FIELD,
                self::INACTIVE_FILTERS_FIELD,
            ]) && !in_array($k, $this->getInactiveFilters()),
            ARRAY_FILTER_USE_BOTH
        );
    }
}
