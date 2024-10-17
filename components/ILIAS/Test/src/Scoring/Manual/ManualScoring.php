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
//use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\URI;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\Input\Container\Form\Standard as Form;
use ILIAS\UI\Component\Input\Container\Form\FormInput as FormInput;
use ILIAS\UI\Component\MainControls\ModeInfo;
use ILIAS\Refinery\Random\Transformation\ShuffleTransformation;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

class ManualScoring
{
    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly Refinery $refinery,
        private readonly \ilLanguage $lng,
        private readonly ManualScoringDB $scoring_db,
        private readonly \ilTesTShuffler $shuffler,
        private readonly \ilObjTest $object,
        private readonly GeneralQuestionPropertiesRepository $question_repo,
        //private readonly \ilTestPassesSelector $pass_selector,
    ) {

    }

    private function getShufflerTrafo(int $qid, int $usr_active_id, int $pass_id): ShuffleTransformation
    {
        return $this->shuffler->getAnswerShuffleFor($qid, $usr_active_id, $pass_id);
    }

    private function getQuestionGUI(int $qid): \assQuestionGUI
    {
        return $this->object->createQuestionGUI("", $qid);
    }

    /**
     * @return ILIAS\TestQuestionPool\Questions\GeneralQuestionProperties[]
     */
    protected function getManuallyScorableQuestionsInTest(): array
    {
        $qtypes = \ilObjTestFolder::_getManualScoring();
        $ret = [];
        foreach ($this->object->getQuestions() as $qid) {
            $qprops = $this->question_repo->getForQuestionId($qid);
            if (in_array($qprops->getTypeId(), $qtypes)) {
                $ret[] = $qprops;
            }
        }
        return $ret;
    }

    public function getModeControl(string $exit_url): ModeInfo
    {
        $exit_url = implode('/', [ILIAS_HTTP_PATH, $exit_url]);
        $exit_url = new URI($exit_url);
        return  $this->ui_factory->mainControls()->modeInfo($this->lng->txt('exit'), $exit_url);
    }

    protected function getQuestionSelector(): FormInput
    {
        $label = $this->lng->txt('questions');
        $options = [];
        foreach ($this->getManuallyScorableQuestionsInTest() as $q) {
            $options[$q->getQuestionId()] = $q->getTitle() . ' (' . $this->lng->txt($q->getClassName()) . ')';// .$q->getMaximumPoints();
        }
        return $this->ui_factory->input()->field()->multiSelect($label, $options);
    }

    protected function getUserSelector(): FormInput
    {
        $label = $this->lng->txt('participants');
        $options = [];
        foreach ($this->object->getTestParticipantsForManualScoring() as $usr_active_id => $participant) {
            $options[$usr_active_id] = $participant['login'];
        }
        return $this->ui_factory->input()->field()->multiSelect($label, $options);
    }


    public function getPlayerSettingsForm(string $action): Form
    {
        $inputs = [];

        $inputs[] = $this->ui_factory->input()->field()->section(
            [
                $this->ui_factory->input()->field()->radio($this->lng->txt('focus'))
                    ->withOption('user', $this->lng->txt('user'))
                    ->withOption('question', $this->lng->txt('question'))
            ],
            $this->lng->txt('focus_and_sorting')
        );

        $inputs[] = $this->ui_factory->input()->field()->section(
            [
                $this->getUserSelector(),
                $this->getQuestionSelector()
            ],
            $this->lng->txt('filters')
        );

        return $this->ui_factory->input()->container()->form()->standard($action, $inputs);
    }




    public function getPassUsedForEvaluation(int $usr_active_id): int
    {
        return $this->object->_getResultPass($usr_active_id);
    }

    /**
     * @return Component[]
     */
    public function getUserRepresentation(int $usr_active_id, $pass_id): array
    {

        $usr_fullname = $this->lng->txt('anonymous');
        if ($this->object->getAnonymity() !== 0) {
            $usr_id = $this->object->_getUserIdFromActiveId($usr_active_id);
            $usr_fullname = $this->object->userLookupFullName($usr_id, false, true);
        }

        $info = $this->ui_factory->listing()->property()
            ->withProperty(
                $this->lng->txt('name'),
                $usr_fullname
            )
            ->withProperty(
                $this->lng->txt('pass'),
                (string) $pass_id
            );

        $pass_info = $this->ui_factory->listing()->property()
            ->withProperty(
                $this->lng->txt("scored_pass"),
                (string) $this->getPassUsedForEvaluation($usr_active_id)
            );

        return [$info, $pass_info];
    }

    public function getPassSelector(int $qid, int $usr_active_id, \Closure $action)
    {
        $passes = [];
        foreach ($this->scoring_db->getPassesForUser($usr_active_id) as $pass) {
            $lnk = $action($qid, $usr_active_id, $pass);
            $passes[] = $this->ui_factory->button()->shy(
                (string) $pass,
                ''
            )->withOnLoadCode(
                fn($id) => "il.Test.ManualScoring.asynchPass(document.getElementById('$id'), '$lnk');"
            );
        }
        $pass_selection = $this->ui_factory->dropdown()->standard($passes);
        return $pass_selection;
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
            $this->ui_factory->legacy('</div><br>'),
        ];
    }

    public function getUserAnswer(int $qid, int $usr_active_id, int $pass_id): Legacy
    {
        $question_gui = $this->getQuestionGUI($qid);
        $shuffle_trafo = $this->getShufflerTrafo($qid, $usr_active_id, $pass_id);
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
        ->withAdditionalTransformation($this->refinery->kindlyTo()->float())
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

        return $this->ui_factory->input()->container()->form()->standard($action, $inputs)
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    fn($values) => [
                        'score' => $values[0],
                        'feedback' => $values[1],
                        'final' => $values[2] ?? false,
                    ]
                )
            );
    }

    public function store(array $data, int $qid, int $usr_active_id, int $pass_id): bool
    {
        $feedback_text = \ilUtil::stripSlashes(
            $data['feedback'],
            false,
            \ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")
        );

        $this->scoring_db->saveManualFeedback(
            $usr_active_id,
            $qid,
            $pass_id,
            $feedback_text,
            $data['final'],
            true
        );

        // fix #35543: save manual points only if they differ from the existing points
        // this prevents a question being set to "answered" if only feedback is entered
        $question = $this->getQuestionGUI($qid)->getObject();
        $old_points = $question->getReachedPoints($usr_active_id, $qid, $pass_id);
        $score = $data['score'];
        $max_points = $question->getMaximumPoints();
        if ($score != $old_points) {
            \assQuestion::_setReachedPoints(
                $usr_active_id,
                $qid,
                $score,
                $max_points,
                $pass_id,
                true,
                $this->object->areObligationsEnabled()
            );
        }

        /*
        $notificationData[$question_id] = [
            'points' => $reached_points, 'feedback' => $feedback_text
        ];
        */

        return true;
    }

    /**
     * finally
     *
     * \ilLPStatusWrapper::_updateStatus(
            $this->object->getId(),
            \ilObjTestAccess::_getParticipantId($active_id)
        );

        $manScoringDone = $form->getItemByPostVar("manscoring_done")->getChecked();
        \ilTestService::setManScoringDone($active_id, $manScoringDone);

        $manScoringNotify = $form->getItemByPostVar("manscoring_notify")->getChecked();
        if ($manScoringNotify) {
            $notification = new \ilTestManScoringParticipantNotification(
                $this->object->_getUserIdFromActiveId($active_id),
                $this->object->getRefId()
            );

            $notification->setAdditionalInformation([
                'test_title' => $this->object->getTitle(),
                'test_pass' => $pass + 1,
                'questions_gui_list' => $question_gui_list,
                'questions_scoring_data' => $notificationData
            ]);

            $notification->send();
        }

        $scorer = new TestScoring($this->object, $this->user, $this->db, $this->lng);
        $scorer->setPreserveManualScores(true);
        $scorer->recalculateSolutions();

     */

}
