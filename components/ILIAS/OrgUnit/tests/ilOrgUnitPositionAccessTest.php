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

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;
use ILIAS\OrgUnit\PositionAccessLegacyInitialisationAdapter;
use ILIAS\AccessControl\RBACAccess;

class ilOrgUnitPositionAccessTest extends TestCase
{
    protected $backupGlobals = false;


    public function testOrguPositionAccessComponentAdapter(): void
    {
        $dic = new Container();
        $dic['objDefinition'] = $this->createMock(\ilObjectDefinition::class);
        $dic['ilUser'] = $this->createMock(\ilObjUser::class);
        $dic['ilDB'] = $this->createMock(\ilDBInterface::class);
        $dic['ilAppEventHandler'] = $this->createMock(\ilAppEventHandler::class);
        $dic['ilObjDataCache'] = $this->createMock(\ilObjectDataCache::class);
        $GLOBALS['DIC'] = $dic;

        $access = $this->createMock(RBACAccess::class);
        $access
            ->expects($this->once())
            ->method('checkAccess')
            ->willReturn(false);

        $position_access = new PositionAccessLegacyInitialisationAdapter(
            $access
        );

        $position_access->checkRbacOrPositionPermissionAccess(
            'write',
            'orgu_write',
            765
        );
    }
}
