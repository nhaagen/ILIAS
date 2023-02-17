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


require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Implementation\Component\Table\Column;

/**
 * Basic Tests for Table-Columns.
 */
class ColumnTest extends ILIAS_UI_TestBase
{
    public function testTitle(): Column\Column
    {
        $col = new Column\Text('col');
        $this->assertEquals('col', $col->getTitle());
        return $col;
    }
    /**
     * @depends testTitle
     */
    public function testAttributes(Column\Column $col)
    {
        $this->assertTrue($col->isSortable());
        $this->assertFalse($col->withIsSortable(false)->isSortable());
        $this->assertTrue($col->withIsSortable(true)->isSortable());

        $this->assertFalse($col->isOptional());
        $this->assertTrue($col->withIsOptional(true)->isOptional());
        $this->assertFalse($col->withIsOptional(false)->isOptional());


        $this->assertTrue($col->isInitiallyVisible());
        $this->assertFalse($col->withIsInitiallyVisible(false)->isInitiallyVisible());
        $this->assertTrue($col->withIsInitiallyVisible(true)->isInitiallyVisible());

        $this->assertEquals(12, $col->withIndex(12)->getIndex());
    }

    public function testTypes()
    {
        $df = new \ILIAS\Data\Factory();
        $cols = [
            new Column\Text('title'),
            new Column\Number('title'),
            new Column\Date('title', $df->dateFormat()->germanShort())
        ];
        $types = array_map(
            function ($c) {
                return $c->getType();
            },
            $cols
        );
        $this->assertEquals(
            ['Text', 'Number', 'Date'],
            $types
        );
    }
}
