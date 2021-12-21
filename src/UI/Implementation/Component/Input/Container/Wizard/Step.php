<?php declare(strict_types=1);

/* Copyright (c) 2021 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Wizard;

use ILIAS\UI\Component\Input\Container\Wizard as W;
use ILIAS\UI\Implementation\Component\Input\Field\Group;

/**
 *
 */
class Step extends Group implements
    W\Step
//class Step implements W\Step
{
    protected string $title = '';
    //protected string $description = '';
    protected string $submit_caption = 'next';
    
    protected FieldFactory $field_factory;
    
    /*
    public function __construct(array $inputs)
    {
        $this->inputs = $inputs;
        parent::__construct($field_factory, '', $inputs);
    }
    */
    

    public function getTitle() : ?string
    {
        return $this->label;
        //return $this->title;
    }

    public function getDescription() : ?string
    {
        return $this->byline;
        //return $this->description;
    }


    public function getSubmitCaption() : ?string
    {
        return $this->submit_caption;
    }

    public function withSubmitCaption(string $submit_caption) : self
    {
        $clone = clone $this;
        $clone->submit_caption = $submit_caption;
        return $clone;
    }
 
    /*
    public function withInputs(array $inputs) : self
     {
         $clone = clone $this;
         $clone->input_group = $clone->field_factory
             ->group($inputs)
             ->withNameFrom($this);
         return $clone;
     }

    */
}
