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

namespace ILIAS\AccessControl;

interface RBACAccess
{
    /**
     * Checks access for the current user and permission at an object,
     * taking activation and path into account.
     * While the user might have the permission per se, she might not
     * be able to access the object due to missing permissions up the tree
     * or by a deactivated object or missing preconditions.
     */
    public function checkAccess(
        string $permission,
        int $ref_id
    ): bool;

    /**
     * Checks access for the given user and permission at an object,
     * taking activation and path into account.
     * While the user might have the permission per se, she might not
     * be able to access the object due to missing permissions up the tree
     * or by a deactivated object or missing preconditions.
     */
    public function checkAccessOfUser(
        int $usr_id,
        string $permission,
        int $ref_id
    ): bool;
}
