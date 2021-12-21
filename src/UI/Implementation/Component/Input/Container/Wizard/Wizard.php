<?php declare(strict_types=1);

/* Copyright (c) 2021 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Wizard;

use ILIAS\UI\Component\Input\Container\Wizard as W;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Input\Container\Form;
use Psr\Http\Message\ServerRequestInterface;

/**
 *
 */
abstract class Wizard extends Form\Standard implements W\Wizard
{
    use ComponentHelper;
    protected W\StepFactory $step_factory;
    protected W\Storage $storage;
    protected W\StepBuilder $builder;
    protected string $post_url;
    protected string $title;
    protected string $description;
    
    public function __construct(
        W\StepFactory $step_factory,
        W\Storage $storage,
        W\StepBuilder $builder,
        string $post_url,
        string $title,
        string $description
    ) {
        $this->step_factory = $step_factory;
        $this->storage = $storage;
        $this->builder = $builder;
        $this->post_url = $post_url;
        $this->title = $title;
        $this->description = $description;
    }

    public function getStepFactory() : W\StepFactory
    {
        return $this->step_factory;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getDescription() : string
    {
        return $this->description;
    }
    
    public function getStepBuilder() : W\StepBuilder
    {
        return $this->builder;
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

    public function getStoredData() : mixed
    {
        return $this->storage->get();
    }

    public function storeData(mixed $data) : void
    {
        $this->storage->set($data);
    }
}
