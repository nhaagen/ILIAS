<?php declare(strict_types=1);

/* Copyright (c) 2022 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Container\Wizard;

/**
 * Storage used (internally) by Wizards
 */
interface Storage
{
    public function set(mixed $data) : void;
    public function get() : mixed;
}
