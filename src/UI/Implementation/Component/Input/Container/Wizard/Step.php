<?php declare(strict_types=1);

/* Copyright (c) 2022 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Wizard;

use ILIAS\UI\Component\Input\Container\Wizard as W;
use ILIAS\UI\Implementation\Component\Input\Field\Group;

/**
 * A Step is one displayable "stop" in a Wizard
 */
class Step extends Group implements W\Step
{
    protected string $submit_caption = 'next';
    
    protected FieldFactory $field_factory;

    public function getTitle() : ?string
    {
        return $this->label;
    }

    public function getDescription() : ?string
    {
        return $this->byline;
    }

    public function withInputs(array $inputs) : self
    {
        $clone = clone $this;
        $clone->input_group = $clone->field_factory->group($inputs);
        return $clone;
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
}
