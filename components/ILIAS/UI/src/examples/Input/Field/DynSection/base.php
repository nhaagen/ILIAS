<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\DynSection;

/**
 * Example showing how sections can be used to attach transformation and constraints on
 * multiple fields at once. Note that sections have a standard way of displaying
 * constraint violations to the user.
 */
function base()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $lng = $DIC->language();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();
    $data = new \ILIAS\Data\Factory();
    $refinery = new \ILIAS\Refinery\Factory($data, $lng);

    $number_input = $ui->input()->field()->numeric("number", "Put in a number.");


    $group = $ui->input()->field()->group(
        [
            $number_input->withLabel("first"),
            $number_input->withLabel("second")->withRequired(true)
        ]
    );
    $dynsection = $ui->input()->field()->dynSection(
        $group,
        "some dyn numbers",
        "you may add more..."
    );

    $dynsection = $dynsection->withValue(
        [
            [1,2],
            [3,4],
            [5,6],
        ]
    );

    $form = $ui->input()->container()->form()->standard('#', [$dynsection]);

    if ($request->getMethod() == "POST") {
        $form = $form->withRequest($request);
        $result = $form->getData()[0] ?? "";
    } else {
        $result = "No result yet.";
    }

    //Return the rendered form
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .

        $renderer->render($form);
}
