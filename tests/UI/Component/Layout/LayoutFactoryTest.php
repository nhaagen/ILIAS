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

require_once 'tests/UI/AbstractFactoryTest.php';

/**
 * Tests on factory implementation for layout
 *
 * @author Nils Haagen <nhaagen@concepts-and-training.de>
 */
class LayoutFactoryTest extends AbstractFactoryTest
{
    public array $kitchensink_info_settings = [
        "page" => [
                "context" => false,
                "rules" => false
        ],
        "alignment" => [
                "context" => false,
                "rules" => false
        ]
    ];
    public string $factory_title = 'ILIAS\\UI\\Component\\Layout\\Factory';
}
