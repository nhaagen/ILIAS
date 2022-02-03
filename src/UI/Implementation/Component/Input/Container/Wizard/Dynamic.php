<?php declare(strict_types=1);

/* Copyright (c) 2022 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Wizard;

use ILIAS\UI\Component\Input\Container\Wizard as W;
use Psr\Http\Message\ServerRequestInterface;

class Dynamic extends Wizard implements W\Dynamic
{
    protected W\StepFactory $step_factory;
    protected W\StepBuilder $builder;
    
    public function __construct(
        W\StepFactory $step_factory,
        W\Storage $storage,
        W\StepBuilder $builder,
        string $post_url,
        string $title,
        string $description
    ) {
        $this->step_factory = $step_factory;
        $this->builder = $builder;
        parent::__construct(
            $storage,
            $post_url,
            $title,
            $description
        );
    }
    
    public function withRequest(ServerRequestInterface $request) : self
    {
        $step_factory = $this->getStepFactory();
        $data = $this->getStoredData();
        $step = $this->getStepBuilder()->build($step_factory, $data);

        $post_data = $this->extractPostData($request);
        $clone = clone $this;
        $clone->input_group = $step
            ->withNameFrom($this)
            ->withInput($post_data);

        $nu_data = $clone->getData();
        if ($nu_data) {
            $clone->storeData($nu_data);
        }
        return $clone;
    }

    public function isFinished() : bool
    {
        $data = $this->getStoredData();
        return $this->getStepBuilder()->isComplete($data);
    }

    public function getStepFactory() : W\StepFactory
    {
        return $this->step_factory;
    }

    public function getStepBuilder() : W\StepBuilder
    {
        return $this->builder;
    }
}
