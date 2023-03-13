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

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilTestPlaceholderDescription implements ilCertificatePlaceholderDescription
{
    private readonly ilDefaultPlaceholderDescription $defaultPlaceHolderDescriptionObject;
    private readonly ilLanguage $language;
    private array $placeholder;

    public function __construct(
        ?ilDefaultPlaceholderDescription $defaultPlaceholderDescriptionObject = null,
        ?ilLanguage $language = null,
        ?ilUserDefinedFieldsPlaceholderDescription $userDefinedFieldPlaceHolderDescriptionObject = null
    ) {
        global $DIC;

        if (null === $language) {
            $language = $DIC->language();
            $language->loadLanguageModule('certificate');
        }
        $this->language = $language;

        if (null === $defaultPlaceholderDescriptionObject) {
            $defaultPlaceholderDescriptionObject = new ilDefaultPlaceholderDescription(
                $language,
                $userDefinedFieldPlaceHolderDescriptionObject
            );
        }
        $this->defaultPlaceHolderDescriptionObject = $defaultPlaceholderDescriptionObject;

        $this->placeholder = $this->defaultPlaceHolderDescriptionObject->getPlaceholderDescriptions();

        $this->placeholder['RESULT_PASSED'] = ilLegacyFormElementsUtil::prepareFormOutput(
            $this->language->txt('certificate_var_result_passed')
        );
        $this->placeholder['RESULT_POINTS'] = ilLegacyFormElementsUtil::prepareFormOutput(
            $this->language->txt('certificate_var_result_points')
        );
        $this->placeholder['RESULT_PERCENT'] = ilLegacyFormElementsUtil::prepareFormOutput(
            $this->language->txt('certificate_var_result_percent')
        );
        $this->placeholder['MAX_POINTS'] = ilLegacyFormElementsUtil::prepareFormOutput(
            $this->language->txt('certificate_var_max_points')
        );
        $this->placeholder['RESULT_MARK_SHORT'] = ilLegacyFormElementsUtil::prepareFormOutput(
            $this->language->txt('certificate_var_result_mark_short')
        );
        $this->placeholder['RESULT_MARK_LONG'] = ilLegacyFormElementsUtil::prepareFormOutput(
            $this->language->txt('certificate_var_result_mark_long')
        );
        $this->placeholder['TEST_TITLE'] = ilLegacyFormElementsUtil::prepareFormOutput(
            $this->language->txt('certificate_ph_testtitle')
        );
        $this->placeholder['DATE_COMPLETED'] = ilLegacyFormElementsUtil::prepareFormOutput(
            $language->txt('certificate_ph_date_completed')
        );
        $this->placeholder['DATETIME_COMPLETED'] = ilLegacyFormElementsUtil::prepareFormOutput(
            $language->txt('certificate_ph_datetime_completed')
        );
    }

    /**
     * This methods MUST return an array containing an array with
     * the the description as array value.
     */
    public function createPlaceholderHtmlDescription(?ilTemplate $template = null): string
    {
        if (null === $template) {
            $template = new ilTemplate('tpl.default_description.html', true, true, 'Services/Certificate');
        }

        $template->setVariable('PLACEHOLDER_INTRODUCTION', $this->language->txt('certificate_ph_introduction'));

        $template->setCurrentBlock('items');
        foreach ($this->placeholder as $id => $caption) {
            $template->setVariable('ID', $id);
            $template->setVariable('TXT', $caption);
            $template->parseCurrentBlock();
        }

        return $template->get();
    }

    /**
     * This method MUST return an array containing an array with
     * the the description as array value.
     * @return array - [PLACEHOLDER] => 'description'
     */
    public function getPlaceholderDescriptions(): array
    {
        return $this->placeholder;
    }
}
