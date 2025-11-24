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

namespace ILIAS\UI\examples\Input\Field\Markdown;

use ilUIMarkdownPreviewGUI;
use ILIAS\UI\Implementation\Component\Input\Field\Markdown;
use ILIAS\Data\Description\Description;

/**
 * ---
 * description: >
 *   The example shows how to create and render a basic markdown field and attach it to a form.
 *
 * expected output: >
 *   On the very right of the markdown controls, there is an additional icon.
 *   When clicked, a dialog with a drilldown will open.
 *   Selecting an item from the menu will insert the respective Mustache variable
 *   into the textarea.
 * ---
 */
function with_placeholders()
{
    global $DIC;
    $md_renderer = new ilUIMarkdownPreviewGUI();
    $query_wrapper = $DIC->http()->wrapper()->query();
    $inputs = $DIC->ui()->factory()->input();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();
    $data_factory = new \ILIAS\Data\Factory();
    $uif = $DIC->ui()->factory();

    $d = new \ILIAS\Data\Description\Factory();
    $md = fn(string $markdown) => $data_factory->text()->markdown()->simpleDocument($markdown);

    /**
     * DataDescription of ILIAS Placeholders
     */
    $user = fn(string $user_description, array $other_fields = []) =>
            $d->object(
                $md("All information about $user_description."),
                array_merge(
                    [
                        "salutation" => $d->string($md("Salutation of $user_description.")),
                        "login" => $d->string($md("Login of $user_description.")),
                        "name" => $d->object(
                            $md("Name of $user_description."),
                            [
                                "title" => $d->string($md("Title of $user_description.")),
                                "first" => $d->string($md("Firstname of $user_description.")),
                                "last" => $d->string($md("Lastname of $user_description.")),
                            ]
                        ),
                        "email" => $d->string($md("eMail of $user_description.")),
                    ],
                    $other_fields
                )
            );

    $placeholders = $d->object(
        $md("All placeholders available at a certain course for a user."),
        [
            "installation" => $d->object(
                $md("All information about the ILIAS installation."),
                [
                    "name" => $d->string($md("The installation name.")),
                    "url" => $d->string($md("The URL of the installation."))
                ]
            ),
            "user" => $user(
                "the user that views the information.",
                [
                    "superior" => $d->list(
                        $md("All superiors of the user that views the information."),
                        $user("SUPERIOR of user that views the information.")
                    )
                ]
            ),
            "course" => $d->object(
                $md("All information about the course that this is about."),
                [
                    "title" => $d->string($md("The title of the course.")),
                    "link" => $d->string($md("The link to the course.")),
                    "start_date" => $d->string($md("Start date the course.")),
                    "end_date" => $d->string($md("End Date of the course.")),

                    'trainers' => $d->object(
                        $md("ALL Trainers of the course"),
                        [
                            "trainer0" => $d->list(
                                $md("The Trainer 0 of the course"),
                                $user("trainer of the course")
                            ),
                            'trainer1' => $d->list(
                                $md("The Trainer 1 of the course"),
                                $user("trainer 1 of the course")
                            ),
                        ]
                    ),

                    "admin" => $d->list(
                        $md("The Admin of the course"),
                        $user("course admin")
                    ),
                ]
            )
        ]
    );

    /**
     * This is the markdown input; its signal will be attached to the buttons of
     * the drilldown
     */
    $markdown_input = $inputs->field()->markdown(
        $md_renderer,
        'Markdown Input with Mustache Variables Selector',
        'Note the Selector-Icon on the very right.'
    );

    $signal = $markdown_input->getMustacheVaribaleSignal();

    $btn = fn(string $label, string $value) =>
        $uif->button()->standard($label, $signal->withOption(Markdown::MUSTACHE_SIGNAL_OPTION, $value));
    $item = fn(string $label, $description, $node_action, ...$items) =>
        $uif->menu()->sub($label, $items, $description, $btn($node_action, strtoupper($node_action)));

    /**
     * convert DataDescription to SubMenus
     */
    $recurse = function (
        \Closure $recurse,
        Description $obj,
        array $carry = []
    ) use ($item, $btn): array {

        if ($obj instanceof \ILIAS\Data\Description\DValue) {
            return [];
        }
        if ($obj instanceof \ILIAS\Data\Description\DList) {
            $carry[] = '0';
            return $recurse($recurse, $obj->getValueType(), $carry);
        }

        if ($obj instanceof \ILIAS\Data\Description\DObject) {
            $items = [];
            foreach ($obj->getFields() as $field) {
                $carry[] = $field->getName();
                $items[] = $item(
                    $field->getName(),
                    $field->getType()->getDescription(),
                    implode('.', $carry),
                    ...$recurse($recurse, $field->getType(), $carry)
                );
                array_pop($carry);
            }
            return $items;
        }
    };
    $data = $recurse($recurse, $placeholders);

    /**
     * Add drilldown to the markdown
     */
    $drilldown = $uif->menu()->drilldown(
        (string) $placeholders->getDescription()->toHTML(),
        $data
    );
    $markdown_input = $markdown_input
        ->withMustacheVariablesSelection($drilldown);

    return $renderer->render($markdown_input);
}
