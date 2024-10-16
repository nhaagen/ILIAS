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
use Psr\Http\Message\ServerRequestInterface;

class ManualScoringGUI
{
    public const CMD_VIEW = 'view';
    //public const CMD_VIEW = 'view';

    public function __construct(
        private readonly \ilCtrlInterface $ctrl,
        private readonly \ilGlobalTemplateInterface $tpl,
        private readonly \ilTabsGUI $tabs,
        private readonly \ilLanguage $lng,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly Refinery $refinery,
        private ServerRequestInterface $request,
        private readonly \ilObjTest $object,
    ) {

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
                        $this->tpl->setContent(
                            $this->view()
                        );
                        break;
                    default:
                        throw new \Exception("Unknown command " . $cmd);
                }
        }
    }


    protected function view(): string
    {


        $out = [];
        $out[] = $this->scoringWidget(1, 3, 1);
        $out[] = $this->scoringWidget(2, 3, 1);
        $out[] = $this->scoringWidget(3, 4, 0);
        $out[] = $this->scoringWidget(3, 3, 1);
        return implode('', $out);
    }


    /**
     * return a form to score a question
     */
    protected function scoringWidget(int $qid, int $usr_active_id, int $pass_id): string
    {
        //new ilTemplate("tpl.il_as_tst_print_body.html", true, true, "components/ILIAS/Test");

        $question_output = $this->getQuestionOutput($qid);
        $user_answer = $this->getUserAnswer($qid, $usr_active_id, $pass_id);
        $form = $this->getScoringForm();

        $layout = $this->ui_factory->layout()->alignment()->vertical(
            $question_output,
            $this->ui_factory->layout()->alignment()->horizontal()->evenlyDistributed(
                $user_answer,
                $form
            )
        );

        return $this->ui_renderer->render([
            $layout,
            $this->ui_factory->divider()->horizontal()
        ]);
    }

    protected function getUserAnswer(int $qid, int $usr_active_id, int $pass_id): Legacy
    {
        $dic = $this->object->getLocalDIC();

        $question_gui = $this->object->createQuestionGUI("", $qid);
        $question = $question_gui->getObject();
        $shuffle_trafo = $dic['shuffler']->getAnswerShuffleFor($qid, $usr_active_id, $pass_id);
        $question->setShuffler($shuffle_trafo);
        $question_gui->setObject($question);
        $question_solution = $question_gui->getSolutionOutput(
            $usr_active_id,
            $pass_id,
            $graphical_output = true,
            $result_output = true,
            $show_question_only = false,
            $show_feedback = false,
            $show_correct_solution = false,
            $show_manual_scoring = true,
            $show_question_text = false,
            $show_inline_feedback = false
        );

        return $this->ui_factory->legacy($question_solution);
    }

    protected function getQuestionOutput(int $qid): Legacy
    {
        return $this->ui_factory->legacy(
            \assQuestion::instantiateQuestion($qid)->getQuestionForHTMLOutput()
        );
    }

    protected function getScoringForm(): Form
    {
        $inputs = [];
        $inputs[] = $this->ui_factory->input()->field()->numeric(
            $this->lng->txt('tst_change_points_for_question')
        );
        $inputs[] = $this->ui_factory->input()->field()->markdown(
            new \ilUIMarkdownPreviewGUI(),
            $this->lng->txt('set_manual_feedback')
        );
        $inputs[] = $this->ui_factory->input()->field()->checkbox(
            $this->lng->txt('finalized_evaluation')
        );

        //turn into async:
        $inputs[] = $this->ui_factory->input()->field()->hidden()
            ->withAdditionalOnLoadCode(
                static fn(string $id): string => "
                    console.log('$id');
                    const f = document.getElementById('$id');
                    const form = f.closest('form');

                    console.log(form.action);
                    console.log(form.method);
                    form.addEventListener('submit', (e) => {
                       e.preventDefault();
                       console.log(form.action);
                       console.log(form.method);
                       console.log(new FormData(form));
                       return false;
                    });
                "
            );

        $action = '#';
        return $this->ui_factory->input()->container()->form()->standard($action, $inputs);
    }
}
