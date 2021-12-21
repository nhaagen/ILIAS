<?php declare(strict_types=1);

/* Copyright (c) 2021 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Container\Wizard;

use \ILIAS\UI\Component\Input\Field\Group;

/**
 * This describes a Step in a Wizard.
 */
interface Step extends Group
{
    public function getTitle() : ?string;
    public function getDescription() : ?string;
    
    public function getSubmitCaption() : ?string;
    public function withSubmitCaption(string $submit_caption) : self;
}
