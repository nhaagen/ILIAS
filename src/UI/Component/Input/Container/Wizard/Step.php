<?php declare(strict_types=1);

/* Copyright (c) 2021 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Container\Wizard;

use \ILIAS\UI\Component\Input\Container\Form;

//use \ILIAS\UI\Component\Input\Field\Input;


/**
 * This describes a Step in a Wizard.
 */
interface Step extends Form\Form
{
    public function getTitle() : ?string;
    public function withTitle(string $title) : self;

    public function getDescription() : ?string;
    public function withDescription(string $description) : self;
    
    public function withInputs(array $inputs) : self;
    public function withPostURL(string $url) : self;
}
