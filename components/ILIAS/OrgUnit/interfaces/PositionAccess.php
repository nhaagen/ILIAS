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

namespace ILIAS\OrgUnit;

interface PositionAccess
{
    /**
     * Checks access for the current user and permission at an object;
     * first, check for RBAC permission. Then, if not granted,
     * check Position Activation of the object and permission of
     * the current user granted by her OrgUnit Position.
     */
    public function checkRbacOrPositionPermissionAccess(
        string $rbac_permission,
        string $position_permimmsion,
        int $ref_id
    ): bool;
}
