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

use ILIAS\LearningModule\ReadingTime\ReadingTimeManager;

/**
 * Importer class for files
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilLearningModuleImporter extends ilXmlImporter
{
    protected ReadingTimeManager $reading_time_manager;
    protected array $qtis;
    protected ilLearningModuleDataSet $ds;
    protected ilImportConfig $config;
    protected ilLogger $log;

    public function init(): void
    {
        $this->ds = new ilLearningModuleDataSet();
        $this->ds->setDSPrefix("ds");

        $this->log = ilLoggerFactory::getLogger('lm');

        $this->config = $this->getImport()->getConfig("components/ILIAS/LearningModule");
        if ($this->config->getTranslationImportMode()) {
            $this->ds->setTranslationImportMode(
                $this->config->getTranslationLM(),
                $this->config->getTranslationLang()
            );
            $cop_config = $this->getImport()->getConfig("components/ILIAS/COPage");
            $cop_config->setUpdateIfExists(true);
            $cop_config->setForceLanguage($this->config->getTranslationLang());
            $cop_config->setReuseOriginallyExportedMedia(true);
            $cop_config->setSkipInternalLinkResolve(true);

            $mob_config = $this->getImport()->getConfig("components/ILIAS/MediaObjects");
            $mob_config->setUsePreviousImportIds(true);
        }
        $this->reading_time_manager = new ReadingTimeManager();
    }

    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        $this->log->debug("import XML Representation");

        // case i container
        if ($new_id = $a_mapping->getMapping('components/ILIAS/Container', 'objs', $a_id)) {
            $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
            $newObj->createLMTree();
            $this->log->debug("got mapping, new id is: " . $new_id);
        }

        // in the new version (5.1)  we are also here, but the following file should not exist
        // if being exported with 5.1 or higher
        $xml_file = $this->getImportDirectory() . '/' . basename($this->getImportDirectory()) . '.xml';

        // old school import
        // currently this means we got a container and mapping, too, since
        // for single lms the processing in ilObjContentObjectGUI->importFileObject is used
        // (this should be streamlined, see glossary)
        if (file_exists($xml_file)) {
            throw new ilLMOldExportFileException("This file seems to be from ILIAS version 5.0.x or lower. Import is not supported anymore.");
        } else {	// new import version (does mapping, too)
            $this->log->debug("create ilDataSetIportParser instance");
            $parser = new ilDataSetImportParser(
                $a_entity,
                $this->getSchemaVersion(),
                $a_xml,
                $this->ds,
                $a_mapping
            );
        }

        // import qti stuff
        $this->log->debug("import qti data");
        $qti_file = $this->getImportDirectory() . '/qti.xml';
        $this->qtis = array();
        if (is_file($qti_file)) {
            $qtiParser = new ilQTIParser(
                $qti_file,
                ilQTIParser::IL_MO_VERIFY_QTI,
                0,
                ""
            );
            $result = $qtiParser->startParsing();
            $founditems = &$qtiParser->getFoundItems();
            $testObj = new ilObjTest(0, true);
            if (count($founditems) > 0) {
                $qtiParser = new ilQTIParser($qti_file, ilQTIParser::IL_MO_PARSE_QTI, 0, "");
                $qtiParser->setTestObject($testObj);
                $result = $qtiParser->startParsing();
                $this->qtis = array_merge($this->qtis, $qtiParser->getImportMapping());
            }
        }
    }

    public function finalProcessing(ilImportMapping $a_mapping): void
    {
        $pg_map = $a_mapping->getMappingsOfEntity("components/ILIAS/LearningModule", "pg");

        $this->log->debug("pg map entries: " . count($pg_map));
        foreach ($pg_map as $pg_id) {
            $lm_id = ilLMPageObject::_lookupContObjID($pg_id);
            ilLMPage::_writeParentId("lm", $pg_id, $lm_id);
            $this->log->debug("write parent id, pg id: " . $pg_id . ", lm id: " . $lm_id);
        }

        // header footer page
        foreach ($a_mapping->getMappingsOfEntity("components/ILIAS/LearningModule", "lm_header_page") as $old_id => $dummy) {
            $new_page_id = (int) $a_mapping->getMapping("components/ILIAS/LearningModule", "pg", $old_id);
            if ($new_page_id > 0) {
                $lm_id = ilLMPageObject::_lookupContObjID($new_page_id);
                ilObjLearningModule::writeHeaderPage($lm_id, $new_page_id);
            }
        }
        foreach ($a_mapping->getMappingsOfEntity("components/ILIAS/LearningModule", "lm_footer_page") as $old_id => $dummy) {
            $new_page_id = (int) $a_mapping->getMapping("components/ILIAS/LearningModule", "pg", $old_id);
            if ($new_page_id > 0) {
                $lm_id = ilLMPageObject::_lookupContObjID($new_page_id);
                ilObjLearningModule::writeFooterPage($lm_id, $new_page_id);
            }
        }


        $link_map = $a_mapping->getMappingsOfEntity("components/ILIAS/LearningModule", "link");
        $pages = $a_mapping->getMappingsOfEntity("components/ILIAS/COPage", "pgl");
        foreach ($pages as $p) {
            $id = explode(":", $p);
            if (count($id) == 3) {
                if (ilPageObject::_exists($id[0], $id[1], $id[2], true)) {
                    $new_page = ilPageObjectFactory::getInstance($id[0], $id[1], 0, $id[2]);
                    $new_page->buildDom();

                    // fix question references
                    $updated = $new_page->resolveQuestionReferences($this->qtis);

                    // in translation mode use link mapping to fix internal links
                    //$a_mapping->addMapping("components/ILIAS/LearningModule", "link",
                    if ($this->config->getTranslationImportMode()) {
                        $il = $new_page->resolveIntLinks($link_map);
                        if ($il) {
                            $updated = true;
                        }
                    }

                    if ($updated) {
                        $new_page->update(false, true);
                    }
                }
            }
        }

        // assign style
        /*
        $alls_map = $a_mapping->getMappingsOfEntity("components/ILIAS/LearningModule", "lm_style");
        foreach ($alls_map as $new_lm_id => $old_style_id) {
            $new_style_id = (int) $a_mapping->getMapping("components/ILIAS/Style", "sty", $old_style_id);
            if ($new_lm_id > 0 && $new_style_id > 0) {
                $lm = new ilObjLearningModule($new_lm_id, false);
                $lm->writeStyleSheetId($new_style_id);
            }
        }*/

        // menu item ref ids
        $ref_mapping = $a_mapping->getMappingsOfEntity('components/ILIAS/Container', 'refs');
        $lm_map = $a_mapping->getMappingsOfEntity("components/ILIAS/LearningModule", "lm");
        foreach ($lm_map as $old_lm_id => $new_lm_id) {
            ilLMMenuEditor::fixImportMenuItems($new_lm_id, $ref_mapping);
        }

        // typical reading time
        $lm_map = $a_mapping->getMappingsOfEntity("components/ILIAS/LearningModule", "lm");
        foreach ($lm_map as $old_lm_id => $new_lm_id) {
            $this->reading_time_manager->updateReadingTime($new_lm_id);
        }
    }
}
