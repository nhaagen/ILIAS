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

$cookie_path = dirname(str_replace($_SERVER['PATH_INFO'], '', $_SERVER['PHP_SELF']));

$_GET['client_id'] = substr(rtrim($_SERVER['PATH_INFO'], '/'), strrpos($_SERVER['PATH_INFO'], '/') + 1);
$_SERVER['PATH_INFO'] = substr($_SERVER['PATH_INFO'], 0, strrpos(rtrim($_SERVER['PATH_INFO'], '/'), '/'));

chdir(__DIR__);

$ilias_main_directory = './';

$i = 0;
while (!is_file($ilias_main_directory . 'ilias.ini.php') && $i < 20) {
    $ilias_main_directory .= '../';
    ++$i;

    $cookie_path = dirname($cookie_path);
}
chdir($ilias_main_directory);

if (!is_file(getcwd() . '/ilias.ini.php')) {
    die('Please ensure ILIAS is installed!');
}

$cookie_path .= (!preg_match("/[\/|\\\\]$/", $cookie_path)) ? "/" : "";

if (isset($_GET["client_id"])) {
    if ($cookie_path === "\\") {
        $cookie_path = '/';
    }

    setcookie('ilClientId', $_GET['client_id'], 0, $cookie_path, '');
    $_COOKIE['ilClientId'] = $_GET['client_id'];
}
define('IL_COOKIE_PATH', $cookie_path);

ilContext::init(ilContext::CONTEXT_SAML);

ilInitialisation::initILIAS();

$factory = new ilSamlAuthFactory();
$auth = $factory->auth();

require_once 'vendor/composer/vendor/simplesamlphp/simplesamlphp/modules/saml/www/sp/saml2-acs.php';
