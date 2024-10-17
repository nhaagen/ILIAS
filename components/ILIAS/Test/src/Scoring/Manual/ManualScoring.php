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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
//use ILIAS\HTTP\Services as HTTPServices;
//use ILIAS\HTTP\Wrapper\RequestWrapper;
//use Psr\Http\Message\ServerRequestInterface;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\Input\Container\Form\Standard as Form;
use ILIAS\Refinery\Random\Transformation\ShuffleTransformation;

class ManualScoring
{
    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly Refinery $refinery,
        private readonly \ilLanguage $lng,
        private readonly \ilObjTest $object,
    ) {
        //$this->request = $http->request();
        //$this->query = $http->wrapper()->query();
        //$this->tpl->addCss(\ilUtil::getStyleSheetLocation("output", "test_print.css"), "print");
    }

    private function getShuffler(int $qid, int $usr_active_id, int $pass_id): ShuffleTransformation
    {
        $dic = $this->object->getLocalDIC();
        return $dic['shuffler']->getAnswerShuffleFor($qid, $usr_active_id, $pass_id);
    }

    private function getQuestionGUI(int $qid): \assQuestionGUI
    {
        return $this->object->createQuestionGUI("", $qid);
    }


    /**
     * @return Component[]
     */
    public function getQuestionRepresentation(int $qid): array
    {
        //$question = \assQuestion::instantiateQuestion($qid);
        $question = $this->getQuestionGUI($qid)->getObject();

        $info = $this->ui_factory->listing()->property()
            ->withProperty(
                $this->lng->txt('question_type'),
                $this->lng->txt($question->getQuestionType())
            )
            ->withProperty(
                $this->lng->txt('points'),
                (string) $question->getMaximumPoints()
            );

        return [
            $this->ui_factory->legacy('<div style="border: 1px solid">'),
            $this->ui_factory->legacy($question->getTitle()),
            $info,
            $this->ui_factory->legacy($question->getQuestionForHTMLOutput()),
            $this->ui_factory->legacy('</div>'),
        ];
    }

    public function getUserAnswer(int $qid, int $usr_active_id, int $pass_id): Legacy
    {
        $question_gui = $this->getQuestionGUI($qid);
        $shuffle_trafo = $this->getShuffler($qid, $usr_active_id, $pass_id);
        $question = $question_gui->getObject();
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

    public function getScoringForm(string $action, int $qid, int $usr_active_id, int $pass_id): Form
    {
        //$question = \assQuestion::instantiateQuestion($qid);
        $question = $this->getQuestionGUI($qid)->getObject();
        $score = $question->getReachedPoints($usr_active_id, $pass_id);
        $feedback = $this->object->getSingleManualFeedback($usr_active_id, $qid, $pass_id);
        $feedback_final = (bool) ($feedback['finalized_evaluation'] ?? false);
        $feedback_txt = $feedback['feedback'] ?? '';

        $inputs = [];
        $inputs[] = $this->ui_factory->input()->field()->numeric(
            $this->lng->txt('tst_change_points_for_question')
        )
        ->withAdditionalTransformation(
            $this->refinery->custom()->constraint(
                fn($v) => (float) $v <= $question->getMaximumPoints(),
                fn() => sprintf(
                    $this->lng->txt('tst_manscoring_maxpoints_exceeded_input_alert'),
                    $question->getMaximumPoints()
                )
            )
        )
        ->withValue($score);

        $inputs[] = $this->ui_factory->input()->field()->markdown(
            new \ilUIMarkdownPreviewGUI(),
            $this->lng->txt('set_manual_feedback')
        )
        ->withValue($feedback_txt);

        $inputs[] = $this->ui_factory->input()->field()->checkbox(
            $this->lng->txt('finalized_evaluation')
        )
        ->withValue($feedback_final);

        //asynch form
        $inputs[] = $this->ui_factory->input()->field()->hidden()
            ->withAdditionalOnLoadCode(
                static fn(string $id): string => "
                    il.Test.ManualScoring.asynchForm(document.getElementById('$id').closest('form'));
                "
            );

        return $this->ui_factory->input()->container()->form()->standard($action, $inputs);
    }
}
