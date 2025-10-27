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

namespace ILIAS\UI\examples\Menu\Drilldown;

use ilUIMarkdownPreviewGUI;

/**
 * ---
 * description: >
 *   The example shows a drilldown wirh descriptions and an additional click-handler/button for nodes
 *
 * expected output: >
 *   ILIAS shows a box titled "Animal of the year" and two drilldowns including a hover effect.
 *   Clicking the drilldowns opens up more sub-entries or links pointing to the ILIAS page or open up a modal.
 *   Clicking the heading will lead one level back.
 * ---
 */
function description()
{
    global $DIC;
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();
    $query_wrapper = $DIC->http()->wrapper()->query();
    $md_renderer = new ilUIMarkdownPreviewGUI();
    $data_factory = new \ILIAS\Data\Factory();

    $uif = $DIC->ui()->factory();

    $md = fn($t) => $data_factory->text()->markdown()->simpleDocument($t);
    $btn = fn($t) => $uif->button()->standard($t, '');
    $item = fn(string $label, string $description, $node_action, ...$items) =>
        $uif->menu()->sub($label, $items, $md($description), $btn($node_action));

    $data = [
        $item(
            'User',
            'Info **about** the current user; **this will include quite a long text...**',
            'current_user',
            $item(
                'Name',
                'current user\'s name',
                'current_user.name',
                $item('Firstname', 'current user\'s first name', 'current_user.name.first'),
                $item('Lastname', 'current user\'s last name', 'current_user.name.last'),
            ),
            $item('Mail', 'current user\'s mail address', 'current_user.mail'),
        ),
        $item(
            'Course',
            'Info about a course',
            'course',
            $item(
                'Venue',
                'a Hotel,, e.g.',
                'course.venue',
                $item('Phone', 'contact info of venue', 'course.venue.phone'),
                $item('Mail', 'contact info of venue', 'course.venue.mail'),
            ),
            $item('Location', 'course location, URL', 'course.location'),
            $item(
                'Places',
                'course location, URL',
                'course.places',
                $item('Free', 'free places in course', 'course.places.free'),
                $item('Waitinglist', 'available places on waitinglinst', 'course.places.waiting')
            )
        )
    ];

    return $renderer->render(
        $uif->menu()->drilldown('Mustache Values', $data)
    );
}
