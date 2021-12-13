<?php declare(strict_types=1);

/* Copyright (c) 2021 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Wizard;

use ILIAS\UI\Component\Input\Container\Wizard as W;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use Psr\Http\Message\ServerRequestInterface;

/**
 *
 */
abstract class Wizard implements W\Wizard
{
    use ComponentHelper;

    protected string $title;
    protected string $description;
    protected \Closure $completion_condition;
    protected mixed $data = null;

    public function __construct(string $title, string $description, \Closure $completion_condition)
    {
        $this->title = $title;
        $this->description = $description;
        $this->completion_condition = $completion_condition;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function withTitle(string $title) : self
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function withDescription(string $description) : self
    {
        $clone = clone $this;
        $clone->description = $description;
        return $clone;
    }
    
    public function withRequest(ServerRequestInterface $request) : self
    {
        global $DIC; //TODO: remove
        $factory = $DIC['ui.factory'];
        $field_factory = $factory->input()->field();
        $refinery = $DIC['refinery'];

        $nullstep = $factory->input()->container()->wizard()->step(
            $field_factory
        );

        $data = $this->getData();
        $step = $this->getStepBuilder()->build(
            $field_factory,
            $refinery,
            $nullstep,
            $data
        );
        $data = $step->withRequest($request)->getData();

        if ($data) {
            return $this->withData($data);
        }
        return $this;
    }

    public function withData(mixed $data) : self
    {
        $clone = clone $this;
        $clone->data = $data;
        return $clone;
    }

    public function getData() : mixed
    {
        return $this->data;
    }

    public function isFinished() : bool
    {
        return $this->checkForCompleteness($this->data);
    }

    protected function checkForCompleteness() : bool
    {
        return call_user_func_array($this->completion_condition, [$this->data]);
    }
}
