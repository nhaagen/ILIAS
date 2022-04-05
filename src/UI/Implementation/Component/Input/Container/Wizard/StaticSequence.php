<?php declare(strict_types=1);

/* Copyright (c) 2022 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Wizard;

use ILIAS\UI\Component\Input\Container\Wizard as W;
use ILIAS\UI\Component\Listing as Listing;
use ILIAS\UI\Implementation\Component\Input\Container\Wizard\WizardInputNameSource;
use Psr\Http\Message\ServerRequestInterface;

class StaticSequence extends Wizard implements W\StaticSequence
{
    protected Listing\Factory $listing_factory;
    protected W\StepFactory $step_factory;
    protected W\StepBuilder $builder;
    
    protected int $current_step = 0;
    
    public function __construct(
        Listing\Factory $listing_factory,
        W\StepFactory $step_factory,
        WizardInputNameSource $name_source,
        W\Storage $storage,
        W\StepBuilder $builder,
        string $post_url,
        string $title,
        string $description
    ) {
        $this->listing_factory = $listing_factory; //->workflow()->linear('', $steps);
        
        $this->step_factory = $step_factory;
        $this->builder = $builder;

        parent::__construct(
            $storage,
            $name_source,
            $post_url,
            $title,
            $description
        );
    }

    public function withRequest(ServerRequestInterface $request) : self
    {
        //TODO: get-wrapper!!!
        $current_step_from_get = 0;
        if ($_GET['stepnr']) {
            $current_step_from_get = (int) $_GET['stepnr'];
        }
        
        $post_data = $this->extractPostData($request);
        $data = $this->getStoredData();
        $step_factory = $this->getStepFactory();

        $clone = clone $this;
        $clone->input_group = $this->getStepBuilder()
            ->withCurrentStep($current_step_from_get)
            ->build($step_factory, $data)
            ->withNameFrom($this->getNameSource())
            ->withInput($post_data);
    

        $nu_data = $clone->getData();
        $nu_step_nr = $current_step_from_get + 1;

        $clone = $clone->withCurrentStep($nu_step_nr);
        $clone->storeData($nu_data);
        return $clone;
    }

 
    public function withCurrentStep(int $step) : self
    {
        $clone = clone $this;
        $clone->current_step = $step;
        return $clone;
    }
    public function getCurrentStep() : int
    {
        return $this->current_step;
    }

    public function getStepBuilder() : W\StepBuilder
    {
        return $this->builder->withCurrentStep($this->getCurrentStep());
    }
}
