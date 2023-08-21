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

use ILIAS\UI\Implementation\Component\Table\Presentation;
use ILIAS\UI\URLBuilder;

/**
 * @package Modules/Test
 * Results Overview Table
 */
class ilTestResultsOverviewTable
{
    private const ENV = 'e';
    private const URL_NAMESPACE = ['taresult', 'vc'];
    private const PARAM_MODE = 'm';
    private const MODE_OPT_ALL = "all";
    private const MODE_OPT_CORRECT = "ok";
    private const MODE_OPT_INCORRECT = "fail";
    private const PARAM_SORT = 's';
    private const SORT_OPT_ORDEROFAPPEARANCE = 'ooa';
    private const SORT_OPT_POSSIBLESCORE = 'ms';

    protected Presentation $table;

    public function __construct(
        ILIAS\UI\Factory $ui_factory,
        protected \ILIAS\UI\Renderer $ui_renderer,
        protected ILIAS\Refinery\Factory $refinery,
        protected ILIAS\HTTP\Services $http,
        ILIAS\Data\Factory $data_factory,
        ilLanguage $lng,
        string $title,
        ilTestResult $test_results,
        ilTestResultsSettings $environment
    ) {
        list($mode, $sortation) = $this->getViewControlsParameter();

        $results = $this->applyControls($mode, $sortation, $test_results->getQuestionResults());

        $target = new URLBuilder($data_factory->uri($http->request()->getUri()->__toString()));

        $this->table = $ui_factory->table()->presentation(
            $title,
            $this->getViewControls($ui_factory, $lng, $target, $mode, $sortation),
            $this->getMapping()
        )
        ->withEnvironment([self::ENV => $environment])
        ->withData($results);
    }

    public function render(): string
    {
        return $this->ui_renderer->render($this->table);
    }

    /**
     * @param ilQuestionResult[] $question_results
     */
    protected function applyControls(
        string $mode,
        string $sortation,
        array $question_results
    ) {
        switch($mode) {
            case self::MODE_OPT_CORRECT:
                $filter = static fn($qr) => $qr->getCorrect() === ilQuestionResult::CORRECT_FULL;
                break;
            case self::MODE_OPT_INCORRECT:
                $filter = static fn($qr) => $qr->getCorrect() !== ilQuestionResult::CORRECT_FULL;
                break;
            case self::MODE_OPT_ALL:
            default:
                $filter = static fn($qr) => true;
        }
        $question_results = array_filter($question_results, $filter);

        if ($sortation === self::SORT_OPT_POSSIBLESCORE) {
            usort(
                $question_results,
                static fn(ilQuestionResult $a, ilQuestionResult $b) => $a->getQuestionScore() <=> $b->getQuestionScore()
            );
            $question_results = array_reverse($question_results);
        }
        return $question_results;
    }

    protected function getViewControlsParameter(): array
    {
        $request = $this->http->wrapper()->query();
        $pre = implode(URLBuilder::SEPARATOR, self::URL_NAMESPACE) . URLBuilder::SEPARATOR;

        $mode = $request->has($pre . self::PARAM_MODE) ?
            $request->retrieve($pre . self::PARAM_MODE, $this->refinery->kindlyTo()->string()) : self::MODE_OPT_ALL;

        $sortation = $request->has($pre . self::PARAM_SORT) ?
            $request->retrieve($pre . self::PARAM_SORT, $this->refinery->kindlyTo()->string()) : self::SORT_OPT_ORDEROFAPPEARANCE;

        return [$mode, $sortation];
    }

    /**
     * return \ILIAS\UI\ViewControl\ViewControl[]
     */
    protected function getViewControls(
        \ILIAS\UI\Factory $ui_factory,
        \ilLanguage $lng,
        URLBuilder $target,
        string $mode,
        string $sortation
    ): array {
        $builder = $target->acquireParameter(self::URL_NAMESPACE, self::PARAM_MODE);
        [$target, $token] = $builder;

        $modes = [
            $lng->txt('resulttable_all') => $target->withParameter($token, self::MODE_OPT_ALL)->buildURI()->__toString(),
            $lng->txt('resulttable_correct') => $target->withParameter($token, self::MODE_OPT_CORRECT)->buildURI()->__toString(),
            $lng->txt('resulttable_incorrect') => $target->withParameter($token, self::MODE_OPT_INCORRECT)->buildURI()->__toString(),
        ];
        $check = [self::MODE_OPT_ALL, self::MODE_OPT_CORRECT, self::MODE_OPT_INCORRECT];
        $active = array_search($mode, $check);

        $vc_mode = $ui_factory->viewControl()->mode($modes, $lng->txt('ta_resulttable_vc_mode_aria'))
            ->withActive(array_keys($modes)[$active]);

        $options = [
            self::SORT_OPT_ORDEROFAPPEARANCE => $lng->txt('resulttable_vc_sort_iooa'),
            self::SORT_OPT_POSSIBLESCORE => $lng->txt('resulttable_vc_sort_posscore')
        ];

        $pre = implode(URLBuilder::SEPARATOR, self::URL_NAMESPACE) . URLBuilder::SEPARATOR;
        $vc_sort = $ui_factory->viewControl()->sortation($options)->withTargetURL(
            $target->buildURI()->__toString(),
            $pre . self::PARAM_SORT
        )
        ->withLabel($options[$sortation]);

        return [
            $vc_mode,
            $vc_sort
        ];
    }

    protected function getMapping(): \Closure
    {
        return function ($row, $question, $ui_factory, $environment) {
            $env = $environment[self::ENV];

            $title = sprintf(
                '%s [ID: %s]',
                $question->getTitle(),
                (string)$question->getId()
            );

            $important = [
                'ID:' => (string)$question->getId(),
                'type: ' => $question->getType(),
                'score: ' => sprintf(
                    '%s/%s (%s%%)',
                    (string)$question->getUserScore(),
                    (string)$question->getQuestionScore(),
                    (string)$question->getUserScorePercent()
                )
            ];
            $stats = $ui_factory->listing()->characteristicValue()->text($important);
            $user_answer = $question->getUserAnswer();
            $best_solution = $env->getShowBestSolution() ? $question->getBestSolution() : '';

            $answers = $ui_factory->layout()->alignment()->horizontal()->evenlyDistributed(
                $ui_factory->listing()->descriptive(['your answer' => $user_answer]),
                $ui_factory->listing()->descriptive(['best solution' => $best_solution])
            );

            $feedback = $ui_factory->listing()->descriptive([
                'feedback' => $question->getFeedback()
            ]);

            $contents = [];
            $contents[] = $stats;
            if ($env->getShowFeedback()) {
                $contents[] = $feedback;
            }
            $contents[] = $answers;

            $content = $ui_factory->layout()->alignment()->vertical(...$contents);

            switch($question->getCorrect()) {
                case ilQuestionResult::CORRECT_FULL:
                case ilQuestionResult::CORRECT_PARTIAL:
                case ilQuestionResult::CORRECT_NONE:
                    $correct_icon = $ui_factory->symbol()->icon()
                        ->standard('pequ', 'Question')
                        ->withSize('small');
                    break;
            }

            return $row
                ->withHeadline($title)
                ->withLeadingSymbol($correct_icon)
                ->withImportantFields($important)
                ->withContent($content);
        };
    }
}
