<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Launcher\Inline;

function base()
{
    global $DIC;
    $ui_factory = $DIC->ui()->factory();
    $data_factory = new \ILIAS\Data\Factory();
    $renderer = $DIC->ui()->renderer();
    $spacer = $ui_factory->divider()->horizontal();

    $url = $data_factory->uri(
        ($_SERVER['REQUEST_SCHEME'] ?? "http") . '://'
        . ($_SERVER['SERVER_NAME'] ?? "localhost") . ':'
        . ($_SERVER['SERVER_PORT'] ?? "80")
        . $_SERVER['REQUEST_URI']
    );

    $target = $data_factory->link('label', $url);
    $launcher = $ui_factory->launcher()
        ->inline($target)
        ->withDescription('my launcher');

    $icon = $ui_factory->symbol()->icon()->standard('crs', '', 'large');
    $status_message =  $ui_factory->messageBox()->failure('not accessible');
    $launcher2 = $launcher
        ->withButtonLabel('not launchable', false)
        ->withStatus($icon, $status_message);

    $progressmeter = $ui_factory->chart()->progressMeter()->mini(100, 50, 75);
    $launcher3 = $launcher
        ->withStatus($progressmeter);

    $icon = $ui_factory->symbol()->icon()->standard('crs', '', 'large');
    $launcher4 = $launcher
        ->withStatus($icon);

    return $renderer->render([
            $launcher,
            $spacer,
            $launcher2,
            $spacer,
            $launcher3,
            $spacer,
            $launcher4
    ]);
}
