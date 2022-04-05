<?php declare(strict_types=1);

/* Copyright (c) 2022 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Wizard;

use \ILIAS\UI\Implementation\Component\Input\FormInputNameSource;

/**
 * WizardInputNameSource; both component->withRequest and renderer
 * are being called during one request; input names must be resetted.
 */
class WizardInputNameSource extends FormInputNameSource
{
    public function withReset() : self
    {
        $clone = clone $this;
        $clone->counter = 0;
        return $clone;
    }
}
