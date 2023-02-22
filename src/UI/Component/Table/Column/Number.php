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

namespace ILIAS\UI\Component\Table\Column;

interface Number extends Column
{
    public const UNIT_POSITION_FORE = 'FORE';
    public const UNIT_POSITION_AFT = 'AFT';

    public function withDecimals(int $number_of_decimals): self;
    public function withUnit(string $unit, $unit_position = self::UNIT_POSITION_AFT): self;
}
