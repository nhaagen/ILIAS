<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\ViewControl\Mode;

/**
 * Base example performing a page reload if active view is changed.
 */
function with_bulkies()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();

    //Some Target magic to get a behaviour closer to some real use case
    $target = $DIC->http()->request()->getRequestTarget();
    $param = "xmode";

    $glyph = $f->symbol()->glyph()->briefcase();
    $button1 = $f->button()->bulky($glyph, '', "$target&$param=1");

    $glyph = $f->symbol()->glyph()->love();
    $button2 = $f->button()->bulky($glyph, '', "$target&$param=2");

    $glyph = $f->symbol()->glyph()->user();
    $button3 = $f->button()->bulky($glyph, '', "$target&$param=3")->withAriaLabel('useless');


    //Here the real magic to draw the controls
    $actions = [
        $button1,
        $button2,
        $button3,
    ];


    $active = 0;
    if ($request_wrapper->has($param) && $request_wrapper->retrieve($param, $refinery->kindlyTo()->int())) {
        $active = $request_wrapper->retrieve($param, $refinery->kindlyTo()->int()) - 1;
    }



    $aria_label = "change_the_currently_displayed_mode";
    $view_control = $f->viewControl()->mode($actions, $aria_label)->withActive($active);
    $html = $renderer->render($view_control);

    return $html;
}
