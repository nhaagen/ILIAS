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

/**
 * Class ilAssClozeTestFeedbackIdMap
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package components\ILIAS/TestQuestionPool
 */
class ilAssSpecificFeedbackIdentifierList implements Iterator
{
    /**
     * @var ilAssSpecificFeedbackIdentifier[]
     */
    protected array $map = array();

    protected function add(ilAssSpecificFeedbackIdentifier $identifier): void
    {
        $this->map[] = $identifier;
    }

    public function load(int $questionId): void
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $res = $DIC->database()->queryF(
            "SELECT feedback_id, question, answer FROM {$this->getSpecificFeedbackTableName()} WHERE question_fi = %s",
            array('integer'),
            array($questionId)
        );

        while ($row = $DIC->database()->fetchAssoc($res)) {
            $identifier = new ilAssSpecificFeedbackIdentifier();

            $identifier->setQuestionId($questionId);

            $identifier->setQuestionIndex($row['question']);
            $identifier->setAnswerIndex($row['answer']);

            $identifier->setFeedbackId($row['feedback_id']);

            $this->add($identifier);
        }
    }

    public function current(): ?ilAssSpecificFeedbackIdentifier
    {
        return current($this->map);
    }

    public function next(): void
    {
        next($this->map);
    }

    public function key(): ?int
    {
        return key($this->map);
    }

    public function valid(): bool
    {
        return key($this->map) !== null;
    }

    public function rewind(): void
    {
        reset($this->map);
    }

    protected function getSpecificFeedbackTableName(): string
    {
        return ilAssClozeTestFeedback::TABLE_NAME_SPECIFIC_FEEDBACK;
    }
}
