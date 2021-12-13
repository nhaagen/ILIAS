<?php declare(strict_types=1);

/* Copyright (c) 2021 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Wizard;

use ILIAS\UI\Implementation\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\UI\Implementation\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Container\Wizard as W;

/**
 *
 */
class Step extends StandardForm implements W\Step
{
    protected $title;
    protected $description;
    
    protected FieldFactory $field_factory;
    
    public function __construct(FieldFactory $field_factory, array $inputs)
    {
        $this->field_factory = $field_factory;
        parent::__construct($field_factory, '', $inputs);
    }

    public function getTitle() : ?string
    {
        return $this->title;
    }

    public function withTitle(string $title) : self
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function getDescription() : ?string
    {
        return $this->description;
    }

    public function withDescription(string $description) : self
    {
        $clone = clone $this;
        $clone->description = $description;
        return $clone;
    }
 
    public function withInputs(array $inputs) : self
    {
        $clone = clone $this;
        $clone->input_group = $clone->field_factory
            ->group($inputs)
            ->withNameFrom($this);
        return $clone;
    }

    public function withPostURL(string $post_url) : self
    {
        $clone = clone $this;
        $clone->post_url = $post_url;
        return $clone;
    }
}
