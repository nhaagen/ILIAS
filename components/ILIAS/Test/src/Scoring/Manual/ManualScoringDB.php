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

use ILIAS\Test\Logging\TestLogger;
use ILIAS\Test\Logging\TestScoringInteraction;
use ILIAS\Test\Logging\TestScoringInteractionTypes;
use ILIAS\Test\Logging\AdditionalInformationGenerator;

class ManualScoringDB
{
    public function __construct(
        private readonly  \ilDBInterface $db,
        protected readonly TestLogger $logger,
        protected readonly int $current_user_id,
    ) {
    }

    public function getPassesForUser(int $usr_active_id): array
    {
        $usr_active_id = $this->db->quote($usr_active_id, 'integer');
        $query = "
            SELECT DISTINCT tst_pass_result.* FROM tst_pass_result
            LEFT JOIN tst_test_result
            ON tst_pass_result.pass = tst_test_result.pass
            AND tst_pass_result.active_fi = tst_test_result.active_fi
            WHERE tst_pass_result.active_fi = $usr_active_id
            ORDER BY tst_pass_result.pass
        ";

        $ret = [];
        $res = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[] = (int) $row['pass'];
        }
        return $ret;

    }

    public function saveManualFeedback(
        int $active_id,
        int $question_id,
        int $pass,
        ?string $feedback,
        bool $finalized = false,
        bool $is_single_feedback = false
    ): bool {
        $feedback_old = \ilObjTest::getSingleManualFeedback($active_id, $question_id, $pass);

        $finalized_record = (int) ($feedback_old['finalized_evaluation'] ?? 0);
        if ($finalized_record === 0 || ($is_single_feedback && $finalized_record === 1)) {
            $this->db->manipulateF(
                'DELETE FROM tst_manual_fb WHERE active_fi = %s AND question_fi = %s AND pass = %s',
                ['integer', 'integer', 'integer'],
                [$active_id, $question_id, $pass]
            );

            $this->insertManualFeedback($active_id, $question_id, $pass, $feedback, $finalized, $feedback_old);
        }

        return true;
    }

    private function insertManualFeedback(
        int $active_id,
        int $question_id,
        int $pass,
        ?string $feedback,
        bool $finalized,
        array $feedback_old
    ): void {
        $next_id = $this->db->nextId('tst_manual_fb');
        $user_id = $this->current_user_id;
        $finalized_time = time();

        $update_default = [
            'manual_feedback_id' => [ 'integer', $next_id],
            'active_fi' => [ 'integer', $active_id],
            'question_fi' => [ 'integer', $question_id],
            'pass' => [ 'integer', $pass],
            'feedback' => [ 'clob', $feedback ? \ilRTE::_replaceMediaObjectImageSrc($feedback, 0) : null],
            'tstamp' => [ 'integer', time()]
        ];

        if ($feedback_old !== [] && (int) $feedback_old['finalized_evaluation'] === 1) {
            $user_id = $feedback_old['finalized_by_usr_id'];
            $finalized_time = $feedback_old['finalized_tstamp'];
        }

        if ($finalized === false) {
            $update_default['finalized_evaluation'] = ['integer', 0];
            $update_default['finalized_by_usr_id'] = ['integer', 0];
            $update_default['finalized_tstamp'] = ['integer', 0];
        } elseif ($finalized === true) {
            $update_default['finalized_evaluation'] = ['integer', 1];
            $update_default['finalized_by_usr_id'] = ['integer', $user_id];
            $update_default['finalized_tstamp'] = ['integer', $finalized_time];
        }

        $this->db->insert('tst_manual_fb', $update_default);

        if ($this->logger->isLoggingEnabled()) {
            $this->logger->logScoringInteraction(
                $this->logger->getInteractionFactory()->buildScoringInteraction(
                    $this->getRefId(),
                    $question_id,
                    $this->user->getId(),
                    \ilObjTest::_getUserIdFromActiveId($active_id),
                    \TestScoringInteractionTypes::QUESTION_GRADED,
                    [
                        AdditionalInformationGenerator::KEY_EVAL_FINALIZED => $this->logger
                            ->getAdditionalInformationGenerator()->getTrueFalseTagForBool($finalized),
                        AdditionalInformationGenerator::KEY_FEEDBACK => $feedback ? ilRTE::_replaceMediaObjectImageSrc($feedback, 0) : ''
                    ]
                )
            );
        }
    }


    //NLZ TODO: TestManScoringDoneHelper

}
