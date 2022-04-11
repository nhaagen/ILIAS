<?php declare(strict_types=1);

class WizardFactoryTest extends AbstractFactoryTest
{
    public array $kitchensink_info_settings = [
        "dynamic" => [
            "context" => false
        ],
        "staticSequence" => [
            "context" => false
        ]
    ];

    public string $factory_title = 'ILIAS\\UI\\Component\\Input\\Container\\Wizard\\Factory';
}
