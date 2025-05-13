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
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Container\ViewControl\ViewControl as ViewControlContainer;
use ILIAS\UI\Component\Input\Container\Filter\Standard as FilterContainer;
use ILIAS\Test\Presentation\TabsManager;

class ConsecutiveScoringGUI
{
    public const CMD_VIEW = 'view';
    public const DEFAULT_COMMAND = 'view';

    public const F_USERS = 'fusers';
    public const F_QUESTIONS = 'fquestions';
    public const F_FANSWERED = 'fanswerd';
    public const F_FINAL = 'ffinal';

    public function __construct(
        protected readonly \ilCtrlInterface $ctrl,
        protected readonly \ilGlobalTemplateInterface $tpl,
        protected readonly \ilTabsGUI $tabs,
        protected readonly \ilLanguage $lng,
        protected readonly \ilObjTest $object,
        protected readonly \ilTestAccess $test_access,
        protected readonly UIFactory $ui_factory,
        protected UIRenderer $ui_renderer,
        protected readonly Refinery $refinery,
        protected readonly ServerRequestInterface $request,
        protected readonly ConsecutiveScoring $scoring,
        protected readonly ConsecutiveScoringSequenceBinding $binding,
        protected readonly \ilUIFilterService $filter_service,
        protected readonly ConsecutiveScoringURLs $url_builder,
    ) {
    }

    public function executeCommand(): void
    {
        if (!$this->test_access->checkScoreParticipantsAccess()) {
            \ilObjTestGUI::accessViolationRedirect();
        }

        if (!$this->object->getGlobalSettings()->isManualScoringEnabled()) {
            // allow only if at least one question type is marked for manual scoring
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("manscoring_not_allowed"), true);
            $this->ctrl->redirectByClass([ilRepositoryGUI::class, ilObjTestGUI::class, ilInfoScreenGUI::class]);
        }

        $this->tabs->activateTab(TabsManager::TAB_ID_MANUAL_SCORING);


        $act = $this->url_builder->getAction() ?? self::DEFAULT_COMMAND;
        switch ($act) {

        }

        $this->tpl->setContent($this->view());
    }

    protected function view(): string
    {
        $filter = $this->getFilter();
        $filter_data = $this->filter_service->getData($filter);
        $binding = $this->binding->withFilterValues($filter_data);

        $sequence = $this->ui_factory->navigation()->sequence($binding)
            ->withId('cs_' . (string) $this->object->getRefId())
            ->withViewControls($this->getViewControls())
            //->withFilter($this->getFilter())
            //->withActions($global_actions)
            ->withRequest($this->request);

        return $this->ui_renderer->render([
            $filter,
            $sequence
        ]);
    }

    protected function getViewControls(): ViewControlContainer
    {
        $vcs = [
            $this->ui_factory->input()->viewControl()->mode(
                [
                    ConsecutiveScoringMode::MODE_USER => $this->lng->txt('mode_user'),
                    ConsecutiveScoringMode::MODE_QUESTION => $this->lng->txt('mode_question'),
                ]
            ),
            $this->ui_factory->input()->viewControl()->mode(
                [
                    ConsecutiveScoringMode::MODE_ALL => $this->lng->txt('mode_allatonce'),
                    ConsecutiveScoringMode::MODE_ONE => $this->lng->txt('mode_onebyone'),
                ]
            )
        ];

        return $this->ui_factory->input()->container()->viewControl()->standard($vcs)
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    fn($v) => [new ConsecutiveScoringMode(...$v)]
                )
            );
    }

    protected function getFilter(): FilterContainer
    {
        $user_options = [];
        foreach ($this->scoring->getTestParticipants() as $usr_active_id => $u) {
            $question_options[$usr_active_id] = $this->scoring->getUserFullName($usr_active_id) ?? $usr_active_id;
        }

        $question_options = [];
        foreach ($this->scoring->getManuallyScorableQuestionsInTest() as $q) {
            $question_options[$q->getQuestionId()] = sprintf(
                '%s (%s)',
                $q->getTitle(),
                $q->getTypeName($this->lng)
            );
        }

        $answered_options = [
            0 => $this->lng->txt('no'),
            1 => $this->lng->txt('yes')
        ];
        $final_options = [
            0 => $this->lng->txt('all_users'),
            1 => $this->lng->txt('evaluated_users'),
            2 => $this->lng->txt('not_evaluated_users')
        ];

        $filter = [
            self::F_USERS => $this->ui_factory->input()->field()->multiselect(
                $this->lng->txt('tst_man_scoring_userselection'),
                $user_options
            ),
            self::F_QUESTIONS => $this->ui_factory->input()->field()->multiselect(
                $this->lng->txt('tst_man_scoring_questionselection'),
                $question_options
            ),
            self::F_FANSWERED => $this->ui_factory->input()->field()->select(
                $this->lng->txt('tst_man_scoring_only_answered'),
                $answered_options
            ),
            self::F_FINAL => $this->ui_factory->input()->field()->select(
                $this->lng->txt('finalized_evaluation'),
                $final_options
            ),
        ];
        return $this->filter_service->standard(
            'csfilter_' . (string) $this->object->getRefId(),
            //$this->request->getUri()->__toString(),
            $this->ctrl->getLinkTarget($this, self::CMD_VIEW),
            $filter,
            array_map(fn() => true, $filter),
            true,
            true
        );
    }
}
