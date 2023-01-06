<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\UI\Implementation\Component\Launcher;

use ILIAS\Data\Link;
use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\Input\Container\Form\Form;

class Inline extends Form implements C\Launcher\Inline
{
    protected Link $target;
    protected string $description;
    protected NameSource $name_source;

    public function __construct(NameSource $name_source, Link $target)
    {
        $this->name_source = $name_source;
        $this->target = $target;
    }

    public function withDescription(string $description): self
    {
        $clone = clone $this;
        $clone->description = $description;
        return $clone;
    }

    public function withInputs(Group $fields): self
    {
        $clone = clone $this;
        $clone->input_group = $fields->withNameFrom($this->name_source);
        return $clone;
    }


    /**
     * @param Icon | ProgressMeter $status
     */
    public function withStatus(C\Component $status): self
    {
    }

    public function withButtonLabel(string $label, bool $launchable = true): self
    {
    }

    public function getData()
    {
    }
}
