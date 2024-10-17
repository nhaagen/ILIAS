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

namespace ILIAS\Test\Scoring\Manual;

use ILIAS\UI\Component\Input\Container\Form\Standard as Form;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use Psr\Http\Message\ServerRequestInterface;

class ManualScoringGUI
{
    public const CMD_VIEW = 'view';
    public const CMD_STORE_SINGLE = 'store';

    private ServerRequestInterface $request;
    private RequestWrapper $query;

    public function __construct(
        private readonly \ilCtrlInterface $ctrl,
        private readonly \ilGlobalTemplateInterface $tpl,
        private readonly \ilTabsGUI $tabs,
        private readonly \ilLanguage $lng,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly Refinery $refinery,
        private HTTPServices $http,
        private readonly ManualScoring $scoring,
    ) {
        $this->request = $http->request();
        $this->query = $http->wrapper()->query();
        //$this->tpl->addCss(\ilUtil::getStyleSheetLocation("output", "test_print.css"), "print");
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            case "xxx":
                //$this->tabs->activateSubTab('manual_scoring');
                $this->ctrl->forwardCommand($gui);
                break;
            default:
                $this->tabs->activateTab(\ilTestTabsManager::TAB_ID_MANUAL_SCORING);
                switch ($cmd) {
                    case self::CMD_VIEW:
                        $this->tpl->addJavaScript('assets/js/manual_scoring.min.js');
                        $this->tpl->setContent(
                            $this->view()
                        );
                        break;
                    case self::CMD_STORE_SINGLE:
                        $params = $this->retrieveSingleParamters();
                        $action = $this->getScoringFormAction(...$params);
                        $form = $this->scoring->getScoringForm($action, ...$params)
                            ->withRequest($this->request);

                        $out = [];

                        $data = $form->getData();
                        if ($data) {
                            //$out[] = $this->ui_factory->legacy(print_r($data, true));
                            $out[] = $this->ui_factory->messageBox()->success($this->lng->txt('ok'));
                        }

                        $out[] = $form;
                        echo $this->ui_renderer->renderAsync($out);
                        exit();

                    default:
                        throw new \Exception("Unknown command " . $cmd);
                }
        }
    }

    private function retrieveSingleParamters(): array
    {
        return [
            $this->query->retrieve('qid', $this->refinery->kindlyTo()->int()),
            $this->query->retrieve('usr_active_id', $this->refinery->kindlyTo()->int()),
            $this->query->retrieve('pass_id', $this->refinery->kindlyTo()->int()),
        ];
    }
    private function getScoringFormAction(int $qid, int $usr_active_id, int $pass_id): string
    {
        $this->ctrl->setParameter($this, 'qid', $qid);
        $this->ctrl->setParameter($this, 'usr_active_id', $usr_active_id);
        $this->ctrl->setParameter($this, 'pass_id', $pass_id);
        $action = $this->ctrl->getFormAction($this, self::CMD_STORE_SINGLE);
        return $action;
    }


    protected function view(): string
    {
        $out = [];
        $out[] = $this->scoring->getQuestionRepresentation(1);
        $out[] = $this->scoringWidget(1, 3, 1);

        $out[] = $this->scoring->getQuestionRepresentation(2);
        $out[] = $this->scoringWidget(2, 3, 1);

        $out[] = $this->scoring->getQuestionRepresentation(3);
        $out[] = $this->scoringWidget(3, 4, 0);
        $out[] = $this->scoringWidget(3, 3, 1);
        $out[] = $this->scoringWidget(3, 4, 1);
        return  $this->ui_renderer->render($out);
    }


    /**
     * return a form to score a question
     */
    protected function scoringWidget(int $qid, int $usr_active_id, int $pass_id): array
    {
        //new ilTemplate("tpl.il_as_tst_print_body.html", true, true, "components/ILIAS/Test");

        /*
        $scoredPass = $this->object->_getResultPass($usr_active_id);
        $lastPass = ilObjTest::_getPass($usr_active_id);
        */
        //$question_output = $this->getQuestioInfo($qid);

        $user_answer = $this->scoring->getUserAnswer($qid, $usr_active_id, $pass_id);
        $form = $this->scoring->getScoringForm(
            $this->getScoringFormAction($qid, $usr_active_id, $pass_id),
            $qid,
            $usr_active_id,
            $pass_id
        );

        $layout = $this->ui_factory->layout()->alignment()->horizontal()->evenlyDistributed(
            $user_answer,
            $form
        );

        return [
            $layout,
            $this->ui_factory->divider()->horizontal()
        ];
    }

}
