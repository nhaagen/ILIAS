<?php

declare(strict_types=1);

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

use PHPUnit\Framework\TestSuite;

class ilModulesIndividualAssessmentSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new ilModulesIndividualAssessmentSuite();

        require_once("./components/ILIAS/IndividualAssessment/tests/AccessControl/ilIndividualAssessmentAccessHandlerTest.php");
        require_once("./components/ILIAS/IndividualAssessment/tests/Members/ilIndividualAssessmentMemberTest.php");
        require_once("./components/ILIAS/IndividualAssessment/tests/Members/ilIndividualAssessmentMembersTest.php");
        require_once("./components/ILIAS/IndividualAssessment/tests/Members/ilIndividualAssessmentMembersStorageDBTest.php");
        require_once("./components/ILIAS/IndividualAssessment/tests/Settings/ilIndividualAssessmentSettingsTest.php");
        require_once("./components/ILIAS/IndividualAssessment/tests/Settings/ilIndividualAssessmentInfoSettingsTest.php");
        require_once("./components/ILIAS/IndividualAssessment/tests/Settings/ilIndividualAssessmentCommonSettingsGUITest.php");
        require_once("./components/ILIAS/IndividualAssessment/tests/Settings/ilIndividualAssessmentSettingsStorageDBTest.php");
        require_once("./components/ILIAS/IndividualAssessment/tests/ilIndividualAssessmentDataSetTest.php");
        require_once("./components/ILIAS/IndividualAssessment/tests/ilIndividualAssessmentExporterTest.php");
        require_once("./components/ILIAS/IndividualAssessment/tests/ilIndividualAssessmentUserGradingTest.php");

        $suite->addTestSuite('ilIndividualAssessmentAccessHandlerTest');
        $suite->addTestSuite('ilIndividualAssessmentMemberTest');
        $suite->addTestSuite('ilIndividualAssessmentMembersTest');
        $suite->addTestSuite('ilIndividualAssessmentMembersStorageDBTest');
        $suite->addTestSuite('ilIndividualAssessmentSettingsTest');
        $suite->addTestSuite('ilIndividualAssessmentInfoSettingsTest');
        $suite->addTestSuite('ilIndividualAssessmentCommonSettingsGUITest');
        $suite->addTestSuite('ilIndividualAssessmentSettingsStorageDBTest');
        $suite->addTestSuite('ilIndividualAssessmentDataSetTest');
        $suite->addTestSuite('ilIndividualAssessmentExporterTest');
        $suite->addTestSuite('ilIndividualAssessmentUserGradingTest');

        return $suite;
    }
}
