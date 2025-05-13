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

use ILIAS\UI\Component\Navigation\Sequence\SegmentRetrieval;
use ILIAS\UI\Component\Navigation\Sequence\SegmentBuilder;
use ILIAS\UI\Component\Navigation\Sequence\Segment;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Input\Container\Form\Standard as Form;
use ILIAS\UI\Component\Prompt\Prompt;
use ILIAS\UI\Component\Button\Standard as StdButton;
use ILIAS\UI\Component\Legacy\Content as LegacyContent;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use Psr\Http\Message\ServerRequestInterface;

class ConsecutiveScoringSequenceBinding implements SegmentRetrieval
{
    private const ACT_FORM_STATE = 'fs';
    private const ACT_STORE_STATE = 'ss';
    private const ACT_STORE = 'store';

    private Prompt $prompt;
    private ?array $filter_users = null;
    private ?array $filter_questions = null;
    private ?array $filter_answered = null;
    private ?array $filter_final = null;

    public function __construct(
        protected readonly UIFactory $ui_factory,
        protected readonly UIRenderer $ui_renderer,
        protected readonly Refinery $refinery,
        protected readonly DataFactory $data_factory,
        protected readonly \ilLanguage $lng,
        protected readonly ConsecutiveScoring $scoring,
        protected ConsecutiveScoringURLs $url_builder,
    ) {
        $this->prompt = $this->ui_factory->prompt()->standard($this->url_builder->buildURI());
    }


    public function withFilterValues(?array $filter_values): self
    {
        $clone = clone $this;
        if ($filter_values === null) {
            $clone->filter_users = null;
            $clone->filter_questions = null;
            $clone->filter_answered = null;
            $clone->filter_final = null;
        } else {
            $clone->filter_users = $filter_values[ConsecutiveScoringGUI::F_USERS];
            $clone->filter_questions = $filter_values[ConsecutiveScoringGUI::F_QUESTIONS];
            $clone->filter_answered = $filter_values[ConsecutiveScoringGUI::F_FANSWERED];
            $clone->filter_final = $filter_values[ConsecutiveScoringGUI::F_FINAL];
        }
        return $clone;
    }


    public function getAllPositions(
        mixed $viewcontrol_values, //ConsecutiveScoringMode
        mixed $filter_values,
        ServerRequestInterface $request
    ): array {
        $positions = [];
        $usr_active_ids = array_map('intval', array_keys($this->scoring->getTestParticipants()));
        $usr_active_ids = array_filter(
            $usr_active_ids,
            fn($id) => $this->filter_users === null || in_array($id, $this->filter_users)
        );
        $question_ids = array_map(
            fn($q) => $q->getQuestionId(),
            $this->scoring->getManuallyScorableQuestionsInTest()
        );

        $question_ids = array_filter(
            $question_ids,
            fn($id) => $this->filter_questions === null || in_array($id, $this->filter_questions)
        );


        if ($viewcontrol_values->isUserCentric()) {
            foreach ($usr_active_ids as $uid) {
                $u_ids = [$uid];
                if ($viewcontrol_values->isSingle()) {
                    foreach ($question_ids as $id) {
                        $q_ids = [$id];
                        $positions[] = [$u_ids, $q_ids];
                    }
                } else {
                    $q_ids = [...$question_ids];
                    $positions[] = [$u_ids, $q_ids];
                }
            }
        } else {
            foreach ($question_ids as $qid) {
                $q_ids = [$qid];
                if ($viewcontrol_values->isSingle()) {
                    foreach ($usr_active_ids as $id) {
                        $u_ids = [$id];
                        $positions[] = [$u_ids, $q_ids];
                    }
                } else {
                    $u_ids = [...$usr_active_ids];
                    $positions[] = [$u_ids, $q_ids];
                }
            }
        }
        return $positions;
    }

    public function getSegment(
        mixed $position_data,
        mixed $viewcontrol_values,
        mixed $filter_values,
        ServerRequestInterface $request
    ): Segment {

        list($usr_active_ids, $question_ids) = $position_data;

        $title = $viewcontrol_values->isUserCentric() ?
            $this->getUserRepresentation(current($usr_active_ids)) :
            $this->getQuestionRepresentation(current($question_ids));

        $form = null;
        $msg = null;

        $act = $this->url_builder->getAction() ?? ConsecutiveScoringGUI::CMD_VIEW;
        switch ($act) {
            case self::ACT_FORM_STATE:
                list($qid, $uid, $pid) = $this->url_builder->getIdParameters();
                $response = $this->ui_factory->prompt()->state()->show(
                    $this->getScoringForm(self::ACT_STORE_STATE, $qid, $uid, $pid)
                )->withTitle( //TODO: should not be on state, but on form
                    $this->ui_renderer->render(
                        $this->getUserRepresentation($uid),
                    )
                );
                echo($this->ui_renderer->renderAsync($response));
                exit();

            case self::ACT_STORE_STATE:
                list($qid, $uid, $pid) = $this->url_builder->getIdParameters();

                $form = $this->getScoringForm(self::ACT_STORE_STATE, $qid, $uid, $pid)
                    ->withRequest($request);
                $formdata = $form->getData();
                if ($formdata !== null) {
                    $this->store($formdata);
                    $msg = $this->ui_factory->messageBox()->success(
                        $this->lng->txt('tst_saved_manscoring_successfully')
                        //TODO: add user/pass info?
                    );
                    $url = $this->url_builder->withAction('view')->buildURI();
                    $response = $this->ui_factory->prompt()->state()->redirect($url);
                } else {
                    $response = $this->ui_factory->prompt()->state()->show($form);
                }
                echo($this->ui_renderer->renderAsync($response));
                exit();

            case self::ACT_STORE:
                $usr_active_id = current($usr_active_ids);
                $qid = current($question_ids);
                $pass_id = $this->scoring->getPassUsedForEvaluation($usr_active_id);

                $form = $this->getScoringForm(self::ACT_STORE, $qid, $usr_active_id, $pass_id)
                    ->withRequest($request);
                $formdata = $form->getData();
                if ($formdata !== null) {
                    $this->store($formdata);
                    $msg = $this->ui_factory->messageBox()->success(
                        $this->lng->txt('tst_saved_manscoring_successfully')
                        //TODO: add user/pass info?
                    );
                    $this->url_builder->withAction('view')->redirect();
                }

                // no break
            default:
                $usr_active_id = current($usr_active_ids);
                $qid = current($question_ids);

                if ($viewcontrol_values->isSingle()) {
                    $pass_id = $this->scoring->getPassUsedForEvaluation($usr_active_id);

                    $form = $form ?? $this->getScoringForm(self::ACT_STORE, $qid, $usr_active_id, $pass_id);
                    $representation = $viewcontrol_values->isUserCentric() ?
                        $this->getQuestionRepresentation(current($question_ids)) :
                        $this->getUserRepresentation(current($usr_active_ids));
                    $user_answer = $this->getUserAnswer($qid, $usr_active_id, $pass_id);

                    $out = array_filter([
                        $representation,
                        $user_answer,
                        $msg,
                        $form,
                    ]);

                } else {
                    $out = $viewcontrol_values->isUserCentric() ?
                        $this->collectForUser($usr_active_id, $question_ids) :
                        $this->collectForQuestion($qid, $usr_active_ids);
                    $out[] = $this->prompt;
                }

                $segment = $this->ui_factory->legacy()->segment(
                    $this->ui_renderer->render($title),
                    $this->ui_renderer->render($out),
                );
                if ($viewcontrol_values->isUserCentric()) {
                    $segment = $segment->withSegmentActions(
                        ...$this->getSegmentActionsForUser($usr_active_id)
                    );
                }
                return $segment;
        }
    }


    protected function collectForUser(int $usr_active_id, array $question_ids): array
    {
        $pass_id = $this->scoring->getPassUsedForEvaluation($usr_active_id);
        $entries = [];
        foreach ($question_ids as $qid) {

            $entries[] = $this->getQuestionRepresentation($qid);
            $entries[] = $this->getUserAnswer($qid, $usr_active_id, $pass_id);
            $entries[] = $this->getSingleFormButton($qid, $usr_active_id, $pass_id);
            $entries[] = $this->ui_factory->divider()->horizontal();
        }
        return $entries;
    }

    protected function collectForQuestion(int $qid, array $usr_active_ids): array
    {
        $entries = [];
        foreach ($usr_active_ids as $usr_active_id) {
            $pass_id = $this->scoring->getPassUsedForEvaluation($usr_active_id);

            $entries[] = $this->getUserRepresentation($usr_active_id);
            $entries[] = $this->getUserAnswer($qid, $usr_active_id, $pass_id);
            $entries[] = $this->getSingleFormButton($qid, $usr_active_id, $pass_id);
            $entries[] = $this->ui_factory->divider()->horizontal();
        }
        return $entries;
    }

    protected function getSingleFormButton(int $qid, int $usr_active_id, int $pass_id): StdButton
    {
        $url = $this->url_builder
            ->withAction(self::ACT_FORM_STATE)
            ->withIdParameters($qid, $usr_active_id, $pass_id)
            ->buildURI();

        return $this->ui_factory->button()->standard(
            $this->lng->txt('grade'),
            $this->prompt->getShowSignal($url)
        );
    }

    protected function getSegmentActionsForUser(int $usr_active_id): array
    {
        return [
            $this->ui_factory->button()->standard(
                $this->lng->txt('set_manscoring_done'),
                '#'
            ),
            $this->ui_factory->button()->standard(
                $this->lng->txt('tst_manscoring_user_notification'),
                '#'
            ),
        ];
    }


    /**
     * @return Component[]
     */
    public function getUserRepresentation(int $usr_active_id): array
    {
        $usr_fullname = $this->scoring->getUserFullName($usr_active_id) ?? $this->lng->txt('anonymous');

        $info = $this->ui_factory->listing()->property()
            ->withProperty(
                $this->lng->txt('name'),
                $usr_fullname
            );

        $pass_info = $this->ui_factory->listing()->property()
            ->withProperty(
                $this->lng->txt("scored_pass"),
                (string) $this->scoring->getPassUsedForEvaluation($usr_active_id)
            );

        return [$info, $pass_info];
    }

    /**
     * @return Component[]
     */
    protected function getQuestionRepresentation(int $qid): array
    {
        $question = $this->scoring->getQuestionObject($qid);

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
            $this->ui_factory->legacy()->content('<div style="border: 1px solid">'),
            $this->ui_factory->legacy()->content($question->getTitle()),
            $info,
            $this->ui_factory->legacy()->content($question->getQuestionForHTMLOutput()),
            $this->ui_factory->legacy()->content('</div><br>'),
        ];
    }


    protected function getUserAnswer(int $qid, int $usr_active_id, int $pass_id): LegacyContent
    {
        $question_gui = $this->scoring->getUserQuestionGUI($qid, $usr_active_id, $pass_id);
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

        return $this->ui_factory->legacy()->content($question_solution);
    }

    protected function getScoringForm(string $action, int $qid, int $usr_active_id, int $pass_id): Form
    {
        $action = $this->url_builder
            ->withAction($action)
            ->withIdParameters($qid, $usr_active_id, $pass_id)
            ->buildURI()->__toString();

        $question = $this->scoring->getQuestionObject($qid);
        $max_points = $question->getMaximumPoints();
        $score = $question->getReachedPoints($usr_active_id, $pass_id);
        $feedback = $this->scoring->getSingleManualFeedback($qid, $usr_active_id, $pass_id);
        $feedback_final = (bool) ($feedback['finalized_evaluation'] ?? false);
        $feedback_txt = $feedback['feedback'] ?? '';

        $inputs = [];
        $inputs[] = $this->ui_factory->input()->field()->numeric(
            $this->lng->txt('tst_change_points_for_question')
        )
        ->withAdditionalTransformation(
            $this->refinery->custom()->constraint(
                fn($v) => (float) $v <= $max_points,
                fn() => sprintf(
                    $this->lng->txt('tst_manscoring_maxpoints_exceeded_input_alert'),
                    $max_points
                )
            )
        )
        ->withAdditionalTransformation($this->refinery->kindlyTo()->float())
        ->withValue($score);

        $inputs[] = $this->ui_factory->input()->field()->markdown(
            new \ilUIMarkdownPreviewGUI(),
            $this->lng->txt('set_manual_feedback')
        )
        ->withAdditionalTransformation($this->refinery->to()->string())
        ->withValue($feedback_txt);

        $inputs[] = $this->ui_factory->input()->field()->checkbox(
            $this->lng->txt('finalized_evaluation')
        )
        ->withAdditionalTransformation($this->refinery->kindlyTo()->bool())
        ->withValue($feedback_final);

        $to_int = $this->refinery->kindlyTo()->int();
        $inputs[] = $this->ui_factory->input()->field()->group(
            [
                $this->ui_factory->input()->field()->hidden()
                ->withAdditionalTransformation($to_int)
                ->withValue($qid),
                $this->ui_factory->input()->field()->hidden()
                ->withAdditionalTransformation($to_int)
                ->withValue($usr_active_id),
                $this->ui_factory->input()->field()->hidden()
                ->withAdditionalTransformation($to_int)
                ->withValue($pass_id)
            ]
        )
        ->withAdditionalTransformation(
            $this->refinery->custom()->transformation(
                fn($values) => [
                        'qid' => $values[0],
                        'usr_active_id' => $values[1],
                        'pass_id' => $values[2],
                    ]
            )
        );

        return $this->ui_factory->input()->container()->form()->standard($action, $inputs)
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    fn($values) => [
                        'qid' => $values[3]['qid'],
                        'usr_active_id' => $values[3]['usr_active_id'],
                        'attempt' => $values[3]['pass_id'],
                        'score' => $values[0],
                        'final' => $values[2] ?? false,
                        'feedback' => $values[1],
                        'max_points' => $max_points,
                    ]
                )
            );
    }

    public function store(array $data)
    {
        $this->scoring->store(
            $data['qid'],
            $data['usr_active_id'],
            $data['attempt'],
            $data['score'],
            $data['final'],
            $data['feedback'],
            $data['max_points']
        );
    }

}
