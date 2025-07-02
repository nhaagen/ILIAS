<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Container\Filter\Standard;

/**
 * ---
 * description: >
 *   Example shows how to create and render a basic filter.
 *
 * expected output: >
 *   If the filter with the toggle button on the right top is disabled "Filter Data" is empty.
 *   If the filter with the toggle button on the right top is enabled "Filter Data" includes the data entered into the filter.
 *   If the filter is minimized all inputs will be hidden and the values will be displayed minimized.
 *   If the filter inputs will be applied by clicking the button "Apply" the filter will be employed automatically. The data
 *   will be displayed accordingly.
 *   Clicking "Reset" will reset all filter.
 * ---
 */
function base()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $request = $DIC->http()->request();

    $optional_filters = [
        'f1' => $factory->input()->field()->text('an optional text filter'),
        'f2' => $factory->input()->field()->multiselect(
            "Take your picks",
            [
                "1" => "Pick 1",
                "2" => "Pick 2",
                "3" => "Pick 3",
            ],
            '(optionally)'
        )
    ];

    $fixed_filters = [
        $factory->input()->field()->dateTime('a dateTime filter'),
        'f3' => $factory->input()->field()->checkbox('a checkbox filter')->withValue(true),
    ];

    $container = $factory->input()->container()->filter()->standard($optional_filters, $fixed_filters)
        ->withRequest($request);

    return $renderer->render([
        $factory->legacy()->content('<pre>' . print_r($container->getData(), true) . '</pre>'),
        $factory->divider()->horizontal(),
        $container
    ]);
}
