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
use ILIAS\GlobalScreen\ScreenContext\ScreenContext;

class ManualScoringGUI
{
    public const CMD_VIEW = 'view';
    public const CMD_VIEW_SINGLE = 'sview';
    public const CMD_STORE_SINGLE = 'sstore';

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
        private ScreenContext $gs_current_context
    ) {
        $this->request = $http->request();
        $this->query = $http->wrapper()->query();
        //$this->tpl->addCss(\ilUtil::getStyleSheetLocation("output", "test_print.css"), "print");
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass();

        /*print '<pre>';
        foreach($this->scoring->getManuallyScorableQuestionsInTest() as $q) {
            //$type = $q->getTypeName($this->lng);
            print $q->getTitle() . ' - ' . $this->lng->txt($q->getClassName()) . ' - ' .$q->getMaximumPoints();
            print '<br>';
        }
        die();
        */


        switch ($next_class) {
            case "xxx":
                //$this->tabs->activateSubTab('manual_scoring');
                $this->ctrl->forwardCommand($gui);
                break;
            default:
                $this->tabs->activateTab(\ilTestTabsManager::TAB_ID_MANUAL_SCORING);
                switch ($cmd) {
                    case self::CMD_VIEW:

                        $action = $this->ctrl->getFormAction($this, self::CMD_VIEW);
                        $playersettings = $this->scoring->getPlayerSettingsForm($action)
                            ->withRequest($this->request);

                        $gs_controls = [
                            'modeinfo' => $this->scoring->getModeControl(
                                $this->ctrl->getLinkTargetByClass('ILIAS\Test\Scoring\Manual\TestScoringByQuestionGUI', 'showManScoringByQuestionParticipantsTable')
                            ),
                            'playersettings' => $playersettings
                        ];

                        $this->gs_current_context->addAdditionalData(
                            ManualScoringPlayer::GS_DATA_SCORING_CONTROLS,
                            $gs_controls
                        );
                        $this->gs_current_context->addAdditionalData(
                            ManualScoringPlayer::GS_DATA_SCORING_MODE,
                            true
                        );


                        $math_jax_setting = new \ilSetting('MathJax');
                        if ($math_jax_setting->get("enable")) {
                            $this->tpl->addJavaScript($math_jax_setting->get("path_to_mathjax"));
                        }
                        $this->tpl->addJavaScript('assets/js/manual_scoring.min.js');
                        $this->tpl->setContent($this->view());
                        break;
                    case self::CMD_VIEW_SINGLE:
                        echo $this->ui_renderer->renderAsync(
                            $this->scoringWidget(...$this->retrieveSingleParameters())
                        );
                        exit();
                        break;
                    case self::CMD_STORE_SINGLE:
                        echo $this->ui_renderer->renderAsync($this->store());
                        exit();

                    default:
                        throw new \Exception("Unknown command " . $cmd);
                }
        }
    }

    private function retrieveSingleParameters(): array
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

    private function getUsrPassAction(): \Closure
    {
        return function (int $qid, int $usr_active_id, int $pass_id) {
            $this->ctrl->setParameter($this, 'qid', $qid);
            $this->ctrl->setParameter($this, 'usr_active_id', $usr_active_id);
            $this->ctrl->setParameter($this, 'pass_id', $pass_id);
            $action = $this->ctrl->getFormAction($this, self::CMD_VIEW_SINGLE);
            return $action;
        };
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


    protected function store(): array
    {
        $params = $this->retrieveSingleParameters();
        $action = $this->getScoringFormAction(...$params);
        $form = $this->scoring->getScoringForm($action, ...$params)
            ->withRequest($this->request);
        $data = $form->getData();

        $out = [];
        if ($data && $this->scoring->store($data, ...$params)) {
            $out[] = $this->ui_factory->messageBox()->success($this->lng->txt('tst_saved_manscoring_successfully'));
        }
        $out[] = $form;
        return $out;
    }



    /**
     * return a form to score a question
     */
    protected function scoringWidget(int $qid, int $usr_active_id, int $pass_id): array
    {
        //new ilTemplate("tpl.il_as_tst_print_body.html", true, true, "components/ILIAS/Test");

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
            $this->scoring->getUserRepresentation($usr_active_id, $pass_id),
            $this->scoring->getPassSelector($qid, $usr_active_id, $this->getUsrPassAction()),
            $layout,
            $this->ui_factory->divider()->horizontal()
        ];
    }

}
