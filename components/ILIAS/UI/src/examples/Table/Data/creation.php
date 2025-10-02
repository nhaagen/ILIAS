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

namespace ILIAS\UI\examples\Table\Data;

use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use Generator;
use ILIAS\UI\URLBuilder;

/**
 * ---
 * description: >
 *   Example showing a data table with a button to create a new entry.
 *
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function creation(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();
    $df = new \ILIAS\Data\Factory();

    $here_uri = $df->uri($DIC->http()->request()->getUri()->__toString());
    $url_builder = new URLBuilder($here_uri);
    $namespace = ['dt', 'creation'];
    list($url_builder, $id_token, $action_token) = $url_builder->acquireParameters(
        $namespace,
        "row_id",
        "action"
    );

    $prompt = $factory->prompt()->standard(
        $url_builder->withParameter($action_token, "create")
    );

    $records = [
        ['col1' => 1, 'col2' => 'a'],
        ['col1' => 2, 'col2' => 'b'],
    ];

    $data_retrieval = new class ($records) implements DataRetrieval {
        public function __construct(
            protected array $records
        ) {
        }

        public function getRows(
            DataRowBuilder $row_builder,
            array $visible_column_ids,
            Range $range,
            Order $order,
            ?array $filter_data,
            ?array $additional_parameters
        ): \Generator {
            foreach ($this->records as $idx => $record) {
                yield $row_builder->buildDataRow('_' . $idx, $record);
            }
        }

        public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
        {
            return count($this->records);
        }
    };

    $table = $factory->table()->data(
        $data_retrieval,
        'Data Table with create button',
        [
            'col1' => $factory->table()->column()->number('Column 1')
                ->withIsSortable(false),
            'col2' => $factory->table()->column()->text('Column 2')
                ->withIsSortable(false),
        ],
    );

    return $renderer->render(
        $table
        ->withEntryCreation($prompt)
        ->withRequest($request)
    );
}
