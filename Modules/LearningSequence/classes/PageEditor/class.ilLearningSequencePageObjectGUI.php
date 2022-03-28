<?php declare(strict_types=1);

/* Copyright (c) 2022 - Nils Haagen <nils.haagen@concepts-and-training.de> - Extended GPL, see LICENSE */

/**
 * Class ilLearningSequencePageObjectGUI
 * There are two pages for Learning Sequences: intro and extro.
 * On both pages, the same objects should be available.
 */
abstract class ilLearningSequencePageObjectGUI extends ilPageObjectGUI
{
    public function getTabs($a_activate = "")
    {
        $this->tabs_gui->activateTab(ilObjLearningSequenceGUI::TAB_CONTENT_MAIN);
    }

    public function getPageConfig()
    {
        $this->page_config->setEnablePCType(ilPCCurriculum::PCELEMENT, true);
        $this->page_config->setEnablePCType(ilPCLauncher::PCELEMENT, true);
        return $this->page_config;
    }
}
