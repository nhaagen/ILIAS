<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Launcher\Inline;

use ILIAS\Data\URI;

function with_fields()
{
    global $DIC;
    $ui_factory = $DIC['ui.factory'];
    $renderer = $DIC['ui.renderer'];
    $data_factory = new \ILIAS\Data\Factory();
    $request = $DIC->http()->request();
    $ctrl = $DIC['ilCtrl'];

    $url = $data_factory->uri(
        ($_SERVER['REQUEST_SCHEME'] ?? "http") . '://'
        . ($_SERVER['SERVER_NAME'] ?? "localhost") . ':'
        . ($_SERVER['SERVER_PORT'] ?? "80")
        . str_replace('launcher_redirect=', 'x=', $_SERVER['REQUEST_URI'])
    );

    $target = $data_factory->link('label', $url);
    $icon = $ui_factory->symbol()->icon()->standard('coms', '', 'large');
    $group = $ui_factory->input()->field()->group(
        [
            $ui_factory->input()->field()->password('pwd', 'Password')
        ]
    );
    $instruction = $ui_factory->messageBox()->info('Fill the form; use password "ilias" to pass');

    $evaluation = function (\ILIAS\Data\Result $result, URI $target) use ($ctrl) {
        if ($result->isOK() && $result->value()[0][0]->toString() === 'ilias') {
            $ctrl->redirectToURL((string)$target . '&launcher_redirect=password protected');
        }
    };

    $launcher = $ui_factory->launcher()
        ->inline($target)
        ->withDescription('a launcher with fields')
        ->withStatus($icon)
        ->withInputs($group, $evaluation, $instruction)
        ->withRequest($request);

    $group = $ui_factory->input()->field()->group(
        [
            $ui_factory->input()->field()->text('username', 'Username'),
            $ui_factory->input()->field()->checkbox('ok', 'Understood')
        ]
    );
    $evaluation = function (\ILIAS\Data\Result $result, URI $target) use ($ctrl) {
        if ($result->isOK() && $result->value()[0][1]) {
            $ctrl->redirectToURL((string)$target . '&launcher_redirect=' . $result->value()[0][0]);
        }
    };
    $launcher2 = $launcher
        ->withInputs($group, $evaluation)
        ->withDescription('2nd');

    $evaluation = function (\ILIAS\Data\Result $result, URI $target) use ($ctrl) {
        if ($result->isOK() && $result->value()[0][1]) {
            $ctrl->redirectToURL((string)$target . '&launcher_redirect=THIRD LAUNCHER');
        }
    };

    $launcher3 = $launcher
        ->withInputs($group, $evaluation)
        ->withDescription('3rd');

    $result = "not submitted or wrong pass";
    if (array_key_exists('launcher_redirect', $request->getQueryParams())
        && $v = $request->getQueryParams()['launcher_redirect']
    ) {
        $result = "<b>sucessfully redirected ($v)</b>";
    }
    return $result . "<hr/>" . $renderer->render([$launcher, $launcher2,$launcher3]);
}
