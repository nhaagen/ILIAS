<?php declare(strict_types=1);

/**
 * Curriculum for PageEditor, the GUI
 */
class ilPCCurriculumGUI extends ilPageContentGUI
{
    const CMD_INSERT = 'insert';
    const CMD_EDIT = 'edit';

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            default:
                $cmd = $this->ctrl->getCmd(self::CMD_EDIT);
                switch ($cmd) {
                    
                    case self::CMD_INSERT:
                        $this->insertNewContentObj();
                        // no break
                    case self::CMD_EDIT:
                        $this->returnToParent();
                        break;

                    default:
                        throw new Exception('unknown command: ' . $cmd);
                }
        }
    }

    protected function returnToParent() : void
    {
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    protected function createNewPageContent() : ilPCCurriculum
    {
        return new ilPCCurriculum(
            $this->getPage()
        );
    }

    public function insertNewContentObj() : void
    {
        $this->content_obj = $this->createNewPageContent();
        $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
        $this->pg_obj->update();
        $this->tpl->setContent($out);
    }
}
