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
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Implementation\Component\Input\ArrayInputData;
use ILIAS\UI\Implementation\Component\Input\StackedInputData;
use ILIAS\UI\Component\Input\Container\Form\Standard as Form;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Data\URI;

class ManualScoringPlayer
{
    public const GS_DATA_SCORING_MODE = 'manualscoring_gsmode';
    public const GS_DATA_SCORING_CONTROLS = 'manualscoring_controls';

    protected string $focus = 'user'; //TODO: enum
    protected array $participant_ids = [];
    protected array $question_ids = [];
    protected int $position = 0;
    protected URLBuilder $url_builder;
    protected URLBuilderToken $token_focus;
    protected URLBuilderToken $token_participants;
    protected URLBuilderToken $token_questions;
    protected URLBuilderToken $token_position;

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly \ilLanguage $lng,
        private readonly ManualScoring $scoring,
        private readonly Refinery $refinery,
        private readonly ServerRequestInterface $request,
        private readonly RequestWrapper $query,
        protected readonly URI $base_uri
    ) {

        $url_builder = new URLBuilder($base_uri);
        $query_params_namespace = ['manscor'];

        list(
            $this->url_builder,
            $this->token_focus,
            $this->token_participants,
            $this->token_questions,
            $this->token_position,
        ) = $url_builder->acquireParameters(
            $query_params_namespace,
            "focus",
            "users",
            "questions",
            "position"
        );

        list(
            $this->focus,
            $this->participant_ids,
            $this->question_ids,
            $this->position
        ) = $this->applySettingsFromRequest();

        $this->url_builder = $this->url_builder
            ->withParameter($this->token_focus, $this->focus)
            ->withParameter($this->token_participants, $this->participant_ids)
            ->withParameter($this->token_questions, $this->question_ids)
            ->withParameter($this->token_position, (string) $this->position);

    }

    protected function applySettingsFromRequest(): array
    {

        $player_form_data = new ArrayInputData(
            $this->getSettingsForm()->withRequest($this->request)->getData()
        );

        $query_data = $this->getQueryData();
        $player_query_data = [];
        foreach ($query_data as $key => $value) {
            if ($value !== null) {
                $player_query_data[$key] = $value;
            }
        }

        $player_request_data = new ArrayInputData($player_query_data);

        $stack = new StackedInputData(
            $player_form_data,
            $player_request_data,
        );

        return [
            $stack->getOr('focus', 'user'),
            $stack->getOr('users', []),
            $stack->getOr('questions', []),
            $stack->getOr('position', 0),
        ];
    }


    public function isUserCentric(): bool
    {
        return $this->focus === 'user';
    }

    public function isQuestionCentric(): bool
    {
        return $this->focus === 'question';
    }


    public function getSequence(): array
    {
        $sequence = [];

        if ($this->isUserCentric()) {
            $participant_id = $this->participant_ids[$this->position] ?? null;
            $pass_id = $this->scoring->getPassUsedForEvaluation($participant_id);
            foreach ($this->question_ids as $qid) {
                $sequence[] = [$qid, $participant_id, $pass_id];
            }
        }

        if ($this->isQuestionCentric()) {
            $qid = $this->question_ids[$this->position] ?? null;
            foreach ($this->participant_ids as $participant_id) {
                $pass_id = $this->scoring->getPassUsedForEvaluation($participant_id);
                $sequence[] = [$qid, $participant_id, $pass_id];
            }
        }

        return $sequence;
    }


    /**
     * @return Component[]
     */
    public function view(): array
    {
        if (count($this->participant_ids) === 0 ||
            count($this->question_ids) === 0) {
            return [];
        }

        $out = $this->getCurrentRepresentation();

        $glyph_back = $this->ui_factory->symbol()->glyph()->back();
        $glyph_next = $this->ui_factory->symbol()->glyph()->next();
        $backlink = $this->getBackLink();
        $back = $this->ui_factory->button()->standard(
            $this->lng->txt('back'),
            $backlink ?? '#'
        )
        ->withSymbol($glyph_next)
        ->withUnavailableAction($backlink === null);

        $nextlink = $this->getnextLink();
        $next = $this->ui_factory->button()->standard(
            $this->lng->txt('next'),
            $nextlink ?? '#'
        )
        ->withSymbol($glyph_back)
        ->withUnavailableAction($nextlink === null);

        array_unshift($out, $back);
        $out[] = $next;


        $out[] = $this->ui_factory->divider()->horizontal();
        $out[] = $this->ui_factory->divider()->horizontal();
        return $out;
    }


    protected function getFocusLength(): int
    {
        if ($this->isUserCentric()) {
            return count($this->participant_ids);
        }
        if ($this->isQuestionCentric()) {
            return count($this->question_ids);
        }
        return 0;
    }

    protected function getBackLink(): ?string
    {
        if ($this->position - 1 < 0) {
            return null;
        }
        return $this->url_builder->withParameter(
            $this->token_position,
            (string) ($this->position - 1)
        )->buildURI()->__toString();
    }

    protected function getNextLink(): ?string
    {
        if ($this->position + 1 === $this->getFocusLength()) {
            return null;
        }
        return $this->url_builder->withParameter(
            $this->token_position,
            (string) ($this->position + 1)
        )->buildURI()->__toString();
    }

    protected function getCurrentRepresentation(): array
    {
        if ($this->isUserCentric()) {
            $participant_id = $this->participant_ids[$this->position];
            return $this->scoring->getUserRepresentation(
                $participant_id,
                $this->scoring->getPassUsedForEvaluation($participant_id)
            );
        }
        if ($this->isQuestionCentric()) {
            $qid = $this->question_ids[$this->position];
            return $this->scoring->getQuestionRepresentation(
                $qid
            );
        }

        throw new \Exception('no such mode: ' . $this->mode);
    }

    protected function getQueryData(): array
    {
        return [
            'focus' => $this->query->retrieve(
                $this->token_focus->getName(),
                $this->refinery->byTrying(
                    [
                    $this->refinery->kindlyTo()->string(),
                    $this->refinery->always(null)]
                )
            ),
            'users' => $this->query->retrieve(
                $this->token_participants->getName(),
                $this->refinery->byTrying(
                    [
                    $this->refinery->kindlyTo()->listOf(
                        $this->refinery->kindlyTo()->int()
                    ),
                    $this->refinery->always(null)]
                )
            ),
            'questions' => $this->query->retrieve(
                $this->token_questions->getName(),
                $this->refinery->byTrying(
                    [
                    $this->refinery->kindlyTo()->listOf(
                        $this->refinery->kindlyTo()->int()
                    ),
                    $this->refinery->always(null)]
                )
            ),
            'position' => $this->query->retrieve(
                $this->token_position->getName(),
                $this->refinery->byTrying(
                    [
                    $this->refinery->kindlyTo()->int(),
                    $this->refinery->always(null)]
                )
            ),
        ];
    }

    public function getSettingsForm(): Form
    {
        $inputs = [];
        $inputs[] = $this->ui_factory->input()->field()->section(
            [
                $this->ui_factory->input()->field()->radio($this->lng->txt('focus'))
                    ->withOption('user', $this->lng->txt('user'))
                    ->withOption('question', $this->lng->txt('question'))
                    ->withValue($this->focus)
            ],
            $this->lng->txt('focus_and_sorting')
        )
        ->withAdditionalTransformation(
            $this->refinery->custom()->transformation(fn($v) => array_shift($v))
        );

        $inputs[] = $this->ui_factory->input()->field()->section(
            [
                $this->scoring->getUserSelector()->withValue($this->participant_ids),
                $this->scoring->getQuestionSelector()->withValue($this->question_ids)
            ],
            $this->lng->txt('filters')
        );

        return $this->ui_factory->input()->container()->form()->standard(
            $this->base_uri->__toString(),
            $inputs
        )
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    function ($v) {
                        $ret = [];
                        $focus = array_shift($v);
                        if ($focus) {
                            $ret['focus'] = $focus;
                        }
                        list($users, $questions) = array_shift($v);
                        if ($users) {
                            $ret['users'] = array_map('intval', $users);
                        }
                        if ($questions) {
                            $ret['questions'] = array_map('intval', $questions);
                        }
                        return $ret;
                    }
                )
            );
    }
}
