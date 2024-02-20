<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Container\Filter\Standard;

/**
 * Example show how to create and render a basic filter.
 */
function base()
{
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    $text = $ui->input()->field()->text("text");

    $filter = $ui->input()->container()->filter()->standard([
        "text" => $text,
    ])
    ->withRequest($request);

    return $renderer->render($filter);
}
