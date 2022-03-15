<?php declare(strict_types=1);

/**
 * LSO Startbutton for PageEditor
 */
class ilPCLauncher extends ilPageContent
{
    const PCTYPE = 'lsolauncher';
    const PCELEMENT = 'Launcher';
    const PLACEHOLDER = '[[[LAUNCHER]]]';
    const PROVIDING_TYPES = ['lso'];

    public function init() : void
    {
        $this->setType(self::PCTYPE);
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) : void {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->cach_node = $this->dom->create_element(self::PCELEMENT);
        $this->cach_node = $this->node->append_child($this->cach_node);
    }

    /**
     * @inheritdoc
     */
    public function modifyPageContentPostXsl($a_html, $a_mode, $a_abstract_only = false)
    {
        if ($a_mode == 'edit') {
            return $a_html;
        }

        $parent_obj_id = (int) $this->getPage()->getParentId();
        if ($this->supportsLauncher($parent_obj_id)) {
            $a_html = $this->replaceWithRenderedButtons($parent_obj_id, $a_html);
        }

        return $a_html;
    }

    protected function supportsLauncher(int $parent_obj_id) : bool
    {
        $parent_obj_type = \ilObject::_lookupType($parent_obj_id);
        return in_array($parent_obj_type, self::PROVIDING_TYPES);
    }

    protected function replaceWithRenderedButtons(int $obj_id, $a_html) : string
    {
        $lso = \ilObjectFactory::getInstanceByObjId($obj_id);
        $rendered_buttons = $lso->getCurrentUserLaunchButtons();
        return str_replace(self::PLACEHOLDER, $rendered_buttons, $a_html);
    }
}
