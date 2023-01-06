<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Launcher\Inline;

function base()
{
    global $DIC;
    $ui_factory = $DIC->ui()->factory();
    $data_factory = new \ILIAS\Data\Factory();
    $renderer = $DIC->ui()->renderer();


    $url = $data_factory->uri(
        ($_SERVER['REQUEST_SCHEME'] ?? "http") . '://'
        . ($_SERVER['SERVER_NAME'] ?? "localhost") . ':'
        . ($_SERVER['SERVER_PORT'] ?? "80")
        . ($_SERVER['SCRIPT_NAME'] ?? "")
    );
    $target = $data_factory->link('label', $url);
    $launcher = $ui_factory->launcher()->inline($target);


    return $renderer->render($launcher);
}
