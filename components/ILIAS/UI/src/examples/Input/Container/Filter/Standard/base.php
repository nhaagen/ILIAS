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

    $filters = [
        'f1' => $factory->input()->field()->text('an optional text filter'),
        'f2' => $factory->input()->field()->multiselect(
            "Take your picks",
            [
                "1" => "Pick 1",
                "2" => "Pick 2",
                "3" => "Pick 3",
            ],
            '(optionally)'
        ),
        $factory->input()->field()->dateTime('a dateTime filter')
            ->withRequired(true),
        'f3' => $factory->input()->field()->checkbox('a checkbox filter')
            ->withValue(true)
            ->withRequired(true),

        $factory->input()->field()->numeric('numeric'),
        $factory->input()->field()->checkbox('checkbox'),
        $factory->input()->field()->select('select', ['o1', 'o2', 'o3']),
        $factory->input()->field()->radio('radio')
            ->withOption('value1', 'label1', 'byline1')
            ->withOption('value2', 'label2', 'byline2')
            ->withOption('value3', 'label3', 'byline3'),
        $factory->input()->field()->duration('duration'),
        $factory->input()->field()->tag('tag', ['t1', 't2']),
        $factory->input()->field()->optionalGroup(
            [
                'opt1' => $factory->input()->field()->text(''),
                'opt2' => $factory->input()->field()->text(''),
            ],
            'optional group',
        ),
        $factory->input()->field()->switchableGroup(
            [
                'switch 1' => $factory->input()->field()->group([$factory->input()->field()->text('')]),
                'switch 2' => $factory->input()->field()->group([$factory->input()->field()->text('')])
            ],
            'switchable'
        ),
        $factory->input()->field()->section(
            [$factory->input()->field()->text('')],
            'section'
        ),
    ];

    $container = $factory->input()->container()->filter()->standard($filters)
        ->withRequest($request);

    return $renderer->render([
        $factory->legacy()->content('<pre>' . print_r($container->getData(), true) . '</pre>'),
        $factory->divider()->horizontal(),
        $container
    ]);
}
