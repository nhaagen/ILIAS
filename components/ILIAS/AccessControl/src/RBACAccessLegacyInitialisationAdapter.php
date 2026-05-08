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
 */

declare(strict_types=1);

namespace ILIAS\AccessControl;

class RBACAccessLegacyInitialisationAdapter implements RBACAccess
{
    private function getLegacyIlAccess(): \ilAccess
    {
        global $DIC;
        return $DIC['ilAccess'];
    }

    public function checkAccess(
        string $permission,
        int $ref_id
    ): bool {
        return $this->getLegacyIlAccess()->checkAccess($permission, '', $ref_id);
    }

    public function checkAccessOfUser(
        int $usr_id,
        string $permission,
        int $ref_id
    ): bool {
        return $this->getLegacyIlAccess()->checkAccessOfUser(
            $usr_id,
            $permission,
            '',
            $ref_id
        );
    }
}
