<?php declare(strict_types=1);

/* Copyright (c) 2021 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Container\Wizard;

use ILIAS\UI\Component\Input\Container\Form;

/**
 * This describes a Wizard
 */
interface Wizard extends Form\Standard
{
    public function getTitle() : string;
    public function getDescription() : string;
}
