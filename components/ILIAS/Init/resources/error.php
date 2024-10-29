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

namespace ILIAS\Init;

use Throwable;
use ILIAS\HTTP\StatusCode;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Init\ErrorHandling\Http\ErrorPageResponder;
use ILIAS\Init\ErrorHandling\Http\PlainTextFallbackResponder;

try {
    require_once '../vendor/composer/vendor/autoload.php';
    require_once __DIR__ . '/../artifacts/bootstrap_default.php';
    entry_point('ILIAS Legacy Initialisation Adapter');
    $DIC->globalScreen()->tool()->context()->claim()->external();
    $local_tpl = new ilGlobalTemplate("tpl.main.html", true, true);
    $local_tpl->addBlockFile("CONTENT", "content", "tpl.error.html");
    $lng->loadLanguageModule("error");
    // #13515 - link back to "system" [see ilWebAccessChecker::sendError()]
    $nd = $tree->getNodeData(ROOT_FOLDER_ID);
    $txt = $lng->txt('error_back_to_repository');
    $local_tpl->SetCurrentBlock("ErrorLink");
    $local_tpl->SetVariable("TXT_LINK", $txt);
    $local_tpl->SetVariable("LINK", ilUtil::secureUrl(ILIAS_HTTP_PATH . '/ilias.php?baseClass=ilRepositoryGUI&amp;client_id=' . CLIENT_ID));
    $local_tpl->ParseCurrentBlock();

    /** @var \ILIAS\DI\Container $DIC */
    global $DIC;

    \ilSession::clear('referer');
    \ilSession::clear('message');

    $DIC->language()->loadLanguageModule('error');

    $message = \ilSession::get('failure') ?? $DIC->language()->txt('http_500_internal_server_error');
    \ilSession::clear('failure');

    $df = new DataFactory();
    $back_target = $df->link(
        $DIC->language()->txt('error_back_to_repository'),
        $df->uri(ILIAS_HTTP_PATH . '/ilias.php?baseClass=ilRepositoryGUI')
    );

    (new ErrorPageResponder(
        $DIC->globalScreen(),
        $DIC->language(),
        $DIC->ui(),
        $DIC->http()
    ))->respond(
        $message,
        StatusCode::HTTP_INTERNAL_SERVER_ERROR,
        $back_target
    );
} catch (Throwable $e) {
    (new PlainTextFallbackResponder())->respond($e);
}
