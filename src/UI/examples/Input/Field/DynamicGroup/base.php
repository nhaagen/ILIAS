<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\DynamicGroup;

/**
 * ---
 * expected output: >
 *   ILIAS shows a form without any inputs; instead, there is an add-glyph.
 *   When clicking the glyph, a group of inputs appears - this is reapeatable.
 *   Next to each group of inputs is a close-glyph to remove the input group.
 *   Saving the form will show all values from all groups, and the form itself
 *   will display the originally added fields.
 *   Since the input is set to required, the form will show an error if submitted
 *   without added fields.
 * ---
 */
function base()
{
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    $template = $ui->input()->field()->group([
        $ui->input()->field()->numeric('a number'),
        $ui->input()->field()->text('a string'),
        $ui->input()->field()->markdown(new \ilUIMarkdownPreviewGUI(), 'a markdown'),
    ]);

    $dyn = $ui->input()->field()->dynamicgroup(
        $template,
        "dyn group",
        "add or remove fields"
    )
    ->withRequired(true);

    $form = $ui->input()->container()->form()->standard('#', ['dyn' => $dyn]);

    $result = '';
    if ($request->getMethod() == "POST") {
        $form = $form->withRequest($request);
        $result = $form->getData();
    }

    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
