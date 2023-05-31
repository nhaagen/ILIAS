<?php

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

declare(strict_types=1);

namespace ILIAS\UI\Component\Input\ViewControl;

use ILIAS\UI\Component\Input\ViewControl\ViewControl as IViewControl;

/**
 * This describes a Pagination View Control
 */
interface Pagination extends IViewControl
{
    public function withLimitOptions(array $options): self;
    public function withTotalCount(int $total_count = null): self;

    //public function withLabelLimit(string $label): self;
}
