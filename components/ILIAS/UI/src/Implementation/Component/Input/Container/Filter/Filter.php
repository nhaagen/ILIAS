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

abstract class Filter extends Container implements I\Filter
{
    use JavaScriptBindable;
    public const TOGGLE_FIELD = '__filtertoggle';

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
        array $inputs
    ) {
        parent::__construct($name_source);
        $filters = [];
        foreach ($inputs as $key => $filter) {
            $filters[$key] = $field_factory->optionalGroup(
                [$filter->withLabel('')],
                $filter->getLabel()
            )
            ->withValue(null);
        }
        $filters[] = $field_factory->text('toggle')->withValue('')
                ->withDedicatedName(self::TOGGLE_FIELD);

        $this->setInputGroup(
            $field_factory->group($filters)->withDedicatedName('filter')
        );
        $this->submit_signal = $signal_generator->create();
        $this->expand_signal = $signal_generator->create();
        $this->stored_input = new Input\ArrayInputData([]);
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
        $clone = parent::withRequest($request);
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
        //return new Input\PostDataFromServerRequest($request);
        $internal_input_data = new Input\ArrayInputData($this->getComponentInternalValues());

        /*
                var_dump($request->getParsedBody());
                print '<hr>';
                var_dump($request->getQueryParams());
                print '<hr>';
                var_dump($internal_input_data);
                print '<hr>';
                var_dump(new Input\PostDataFromServerRequest($request));
                print '<hr>';
                var_dump(
                    new Input\StackedInputData(
                        new Input\QueryParamsFromServerRequest($request),
                        //$this->stored_input,
                        //$internal_input_data,
                    )
                );
                die();
        */

        return new Input\StackedInputData(
            new Input\QueryParamsFromServerRequest($request),
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
}
