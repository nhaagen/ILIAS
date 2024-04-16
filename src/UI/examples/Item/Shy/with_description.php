<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Item\Shy;

/**
 * ---
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function with_description()
{
    global $DIC;

    return $DIC->ui()->renderer()->render(
        $DIC->ui()->factory()->item()->shy('Test shy Item')->withDescription('This is a test shy.')
    );
}
