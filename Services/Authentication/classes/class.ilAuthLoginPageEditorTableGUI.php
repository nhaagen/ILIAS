<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Description of class
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup Services/authentication
 */
class ilAuthLoginPageEditorTableGUI extends ilTable2GUI
{

    /**
     * Constructor
     * @param object $a_parent_obj
     * @param string $a_parent_cmd
     */
    public function __construct($a_parent_obj, $a_parent_cmd = "")
    {
        global $DIC;

        $lng = $DIC['lng'];

        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->lng = $lng;
        $this->lng->loadLanguageModule('meta');

        $this->initTable();
    }

    /**
     * Parse input data
     */
    public function parse()
    {
        $installed = $this->lng->getInstalledLanguages();

        $tbl_data = array();
        $counter = 0;
        foreach ($installed as $key => $langkey) {
            $tbl_data[$counter]['key'] = $langkey;
            $tbl_data[$counter]['id'] = ilLanguage::lookupId($langkey);
            $tbl_data[$counter]['status'] = ilAuthLoginPageEditorSettings::getInstance()->isIliasEditorEnabled($langkey);
            $tbl_data[$counter]['language'] = $this->lng->txt('meta_l_' . $langkey);


            ++$counter;
        }
        $this->setData($tbl_data);
    }

    /**
     * Fill table row template
     * @param array $a_set
     */
    protected function fillRow(array $a_set) : void
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $this->tpl->setVariable('LANGID', $a_set['key']);
        $this->tpl->setVariable('LANGKEY_CHECKED', $a_set['status'] ? 'checked="checked' : '');
        $this->tpl->setVariable('TXT_LANGUAGE', $a_set['language']);

        if ($this->lng->getDefaultLanguage() == $a_set['key']) {
            $this->tpl->setVariable('TXT_SYSTEM', $this->lng->txt('system_language'));
        }
        if ($a_set['status']) {
            $this->tpl->setVariable('STATUS_SRC', ilUtil::getImagePath('icon_ok.svg'));
            $this->tpl->setVariable('STATUS_ALT', $this->lng->txt('active'));
            $this->tpl->setVariable('CHECKED_LANGKEY', 'checked="checked"');
        } else {
            $this->tpl->setVariable('STATUS_SRC', ilUtil::getImagePath('icon_not_ok.svg'));
            $this->tpl->setVariable('STATUS_ALT', $this->lng->txt('inactive'));
        }
        $this->tpl->setVariable('LINK_TXT', $this->lng->txt('edit'));
        $ilCtrl->setParameter($this->getParentObject(), 'key', $a_set['id']);
        $this->tpl->setVariable('LINK_NAME', $ilCtrl->getLinkTargetByClass('illoginpagegui', 'edit'));
    }



    /**
     * Init table
     */
    protected function initTable()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
        $this->setRowTemplate('tpl.auth_login_page_editor_table_row.html', 'Services/Authentication');
        $this->setId('loginpageeditor');
        $this->setSelectAllCheckbox('languages');
        $this->setFormName('login_pages');
        $this->addColumn('', 'c', '1px');
        $this->addColumn($this->lng->txt('language'), 'language', '85%');
        $this->addColumn($this->lng->txt('active'), 'status', '5%');
        $this->addColumn($this->lng->txt('actions'), '', '10%');

        $this->addMultiCommand('activate', $this->lng->txt('login_page_activate'));
        
        $this->setDefaultOrderField('language');
        $this->enable('sort');
        $this->enable('header');
        $this->disable('numinfo');
        $this->enable('select_all');
    }
}
