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

/*use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Container\ViewControl\ViewControl as ViewControlContainer;
use ILIAS\UI\Component\Input\Container\Filter\Standard as FilterContainer;
*/

use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\Test\Logging\TestLogger;

class ConsecutiveScoring
{
    public function __construct(
        protected readonly \ilObjTest $object,
        protected readonly GeneralQuestionPropertiesRepository $question_repo,
        protected readonly \ilTesTShuffler $shuffler,
        protected readonly TestLogger $logger,
    ) {
    }

    /**
     * @return ILIAS\TestQuestionPool\Questions\GeneralQuestionProperties[]
     */
    public function getManuallyScorableQuestionsInTest(): array
    {
        $qtypes = $this->object->getGlobalSettings()->getDisabledQuestionTypes();
        $ret = [];
        foreach ($this->object->getQuestions() as $qid) {
            $qprops = $this->question_repo->getForQuestionId($qid);
            if (!in_array($qprops->getTypeId(), $qtypes)) {
                $ret[] = $qprops;
            }
        }
        return $ret;
    }

    public function getTestParticipants(): array
    {
        return $this->object->getTestParticipants();
    }

    protected function getQuestionGUI(int $qid): \assQuestionGUI
    {
        return $this->object->createQuestionGUI("", $qid);
    }

    public function getQuestionObject(int $qid): \assQuestion
    {
        return $this->getQuestionGUI($qid)->getObject();
    }

    public function getPassUsedForEvaluation(int $usr_active_id): int
    {
        return $this->object->_getResultPass($usr_active_id);
    }

    public function getUserFullName(int $usr_active_id): ?string
    {
        return ($this->object->getAnonymity() === 0) ?
            null :
            $this->object->userLookupFullName(
                $this->object->_getUserIdFromActiveId($usr_active_id),
                false,
                true
            );
    }

    public function getSingleManualFeedback(int $qid, int $usr_active_id, int $pass_id): array
    {
        return $this->object->getSingleManualFeedback($usr_active_id, $qid, $pass_id);
    }

    public function getUserQuestionGUI(int $qid, int $usr_active_id, int $pass_id): \assQuestionGUI
    {
        $question_gui = $this->getQuestionGUI($qid);
        $shuffle_trafo = $this->shuffler->getAnswerShuffleFor($qid, $usr_active_id, $pass_id);
        $question = $question_gui->getObject();
        $question->setShuffler($shuffle_trafo);
        $question_gui->setObject($question);
        return $question_gui;
    }


    public function store(
        int $qid,
        int $usr_active_id,
        int $pass_id,
        float $score,
        bool $final,
        string $feedback,
        float $max_points
    ) {
        $feedback = \ilUtil::stripSlashes(
            $feedback,
            false,
            \ilObjAdvancedEditing::_getUsedHTMLTagsAsString('assessment')
        );

        $this->object->saveManualFeedback(
            $usr_active_id,
            $qid,
            $pass_id,
            $feedback,
            $final
        );

        $previously_reached_points = $this->getQuestionObject($qid)
            ->getReachedPoints($usr_active_id, $pass_id);
        if ($score !== $previously_reached_points) {
            \assQuestion::_setReachedPoints(
                $usr_active_id,
                $qid,
                $score,
                $max_points,
                $pass_id,
                true
            );
            \ilLPStatusWrapper::_updateStatus(
                $this->object->getId(),
                \ilObjTestAccess::_getParticipantId($usr_active_id)
            );
        }

        if ($this->logger->isLoggingEnabled()) {
            $this->logger->logScoringInteraction(
                $this->logger->getInteractionFactory()->buildScoringInteraction(
                    $this->getObject()->getRefId(),
                    $question_id,
                    $this->user->getId(),
                    \ilObjTestAccess::_getParticipantId($active_id),
                    TestScoringInteractionTypes::QUESTION_GRADED,
                    [
                        AdditionalInformationGenerator::KEY_REACHED_POINTS => $new_reached_points,
                        AdditionalInformationGenerator::KEY_FEEDBACK => $feedback_text,
                        AdditionalInformationGenerator::KEY_EVAL_FINALIZED => $this->logger
                            ->getAdditionalInformationGenerator()->getTrueFalseTagForBool($finalized)
                    ]
                )
            );
        }
    }

}
