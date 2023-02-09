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

namespace ILIAS\UI\Component\Launcher;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Chart\ProgressMeter\ProgressMeter;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\UI\Component\MessageBox;

interface Launcher extends Component
{
    public const ALLOWED_STATUS_COMPONENTS = [
        Icon::class,
        ProgressMeter::class
    ];

    public function withDescription(string $description): self;
    public function withInputs(Group $fields, \Closure $evaluation, MessageBox\MessageBox $instruction = null): self;

    /**
     * @param Icon | ProgressMeter $status
     */
    public function withStatusIcon(?Component $status_icon): self;
    public function withStatusMessage(?MessageBox\MessageBox $status_message): self;
    public function withButtonLabel(string $label, bool $launchable = true): self;
}
