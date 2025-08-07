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

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Component\Table as T;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Text\WordOnlyMarkdown;

class SummaryRow implements T\SummaryRow
{
    use ComponentHelper;

    /**
     * @var array<string, bool>
     */
    protected array $disabled_actions = [];

    /**
     * The records's key is the column-id of the table.
     *
     * @param array<string, T\Column\Column> $columns
     * @param array<string, mixed> $record
     */
    public function __construct(
        protected bool $table_has_singleactions,
        protected bool $table_has_multiactions,
        protected array $columns,
        protected DataFactory $data_factory,
        protected array $record
    ) {
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function tableHasSingleActions(): bool
    {
        return $this->table_has_singleactions;
    }
    public function tableHasMultiActions(): bool
    {
        return $this->table_has_multiactions;
    }

    public function getCellContent(string $col_id): ?WordOnlyMarkdown
    {
        if (!array_key_exists($col_id, $this->record)) {
            return null;
        }
        return $this->data_factory->text()->markdown()->wordOnly(
            $this->record[$col_id]
        );
    }

    public function isHighlighted(): bool
    {
        return false;
    }
}
