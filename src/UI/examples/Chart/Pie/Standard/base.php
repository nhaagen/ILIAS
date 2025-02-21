<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Chart\Pie\Standard;

/**
 * Example for rendering a fixed size Progress Meter with minimum configuration
 */
function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $sections = [
        25, 25, 50
    ];
    $pie = $f->chart()->pie()->standard($sections);

    // render
    return $renderer->render($pie);
}
