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
 ********************************************************************
 */

use ILIAS\components\OrgUnit\ARHelper\BaseCommands;

/**
 * Class ilOrgUnitPermissionGUI
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @ilCtrl_IsCalledBy ilOrgUnitPermissionGUI: ilOrgUnitPositionGUI
 */
class ilOrgUnitPermissionGUI extends BaseCommands
{
    protected function index(): void
    {
        new ilOrgUnitPermissionTableGUI($this, self::CMD_INDEX, $this->getParentRefId());
    }
}
