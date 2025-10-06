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
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\Action\Action;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Component\JavaScriptBindable as JSBindable;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component\Input\ViewControl;
use ILIAS\UI\Component\Input\Container\ViewControl as ViewControlContainer;
use ILIAS\Data\Range;
use ILIAS\Data\Order;

class Data extends AbstractTable implements T\Data
{
    use JavaScriptBindable;
    use TableViewControlFieldSelection;
    use TableViewControlPagination;
    use TableViewControlOrdering;

    public const STORAGE_ID_PREFIX = self::class . '_';
    public const VIEWCONTROL_KEY_PAGINATION = 'range';
    public const VIEWCONTROL_KEY_ORDERING = 'order';
    public const VIEWCONTROL_KEY_FIELDSELECTION = 'selected_optional';

    //public const VIEWCONTROL_KEY_HIGHLIGHTED_ROWS = 'selected_optional';
    public const KEY_HIGHLIGHTED_ROWS = 'hlrws';

    protected ?array $filter = null;
    protected ?array $additional_parameters = null;

    /**
     * @param array<string, Column> $columns
     */
    public function __construct(
        SignalGeneratorInterface $signal_generator,
        ViewControl\Factory $view_control_factory,
        ViewControlContainer\Factory $view_control_container_factory,
        protected DataFactory $data_factory,
        protected DataRowBuilder $data_row_builder,
        string $title,
        array $columns,
        protected T\DataRetrieval $data_retrieval,
        \ArrayAccess $storage
    ) {
        parent::__construct(
            $signal_generator,
            $view_control_factory,
            $view_control_container_factory,
            $storage,
            $title,
            $columns
        );
        $this->initViewControlFieldSelection($columns);
        $this->initViewControlOrdering();
        $this->initViewControlpagination();
    }

    public function getDataRetrieval(): T\DataRetrieval
    {
        return $this->data_retrieval;
    }

    public function withFilter(?array $filter): self
    {
        $clone = clone $this;
        $clone->filter = $filter;
        return $clone;
    }

    public function getFilter(): ?array
    {
        return $this->filter;
    }

    public function withAdditionalParameters(?array $additional_parameters): self
    {
        $clone = clone $this;
        $clone->additional_parameters = $additional_parameters;
        return $clone;
    }

    public function getAdditionalParameters(): ?array
    {
        return $this->additional_parameters;
    }

    public function getRowBuilder(): DataRowBuilder
    {
        return $this->data_row_builder
            ->withMultiActionsPresent($this->hasMultiActions())
            ->withSingleActions($this->getSingleActions())
            ->withVisibleColumns($this->getVisibleColumns());
    }

    /**
     * @return array<self, ViewControlContainer\ViewControl>
     */
    public function applyViewControls(
        array $filter_data,
        ?array $additional_parameters = []
    ): array {
        $table = $this;
        $total_count = $this->getDataRetrieval()->getTotalRowCount($filter_data, $additional_parameters);
        $view_controls = $this->getViewControls($total_count);

        if ($request = $this->getRequest()) {
            # This retrieves container data from the request
            $data = $this->applyValuesToViewcontrols($view_controls, $request)->getData();
            $range = $data[self::VIEWCONTROL_KEY_PAGINATION];
            $range = ($range instanceof Range) ? $range : null;
            $order = $data[self::VIEWCONTROL_KEY_ORDERING];
            $order = ($order instanceof Order) ? $order : null;

            if ($range instanceof Range) {
                $range = $range->withStart($range->getStart() < $total_count ? $range->getStart() : 0);
                $range = $range->croppedTo($total_count ?? PHP_INT_MAX);
            }

            $table = $table
                ->withRange($range)
                ->withOrder($order)
                ->withSelectedOptionalColumns($data[self::VIEWCONTROL_KEY_FIELDSELECTION] ?? null);

            # This retrieves the view controls that should be displayed
            $view_controls = $table->applyValuesToViewcontrols($table->getViewControls($total_count), $request);

            $table->data_row_builder = $table->data_row_builder->withHighlightedRows(
                $request->getQueryParams()[self::KEY_HIGHLIGHTED_ROWS] ?? []
            );
        }

        return [
            $table,
            $view_controls
        ];
    }

    protected function getViewControls(?int $total_count = null): ViewControlContainer\ViewControl
    {
        $view_controls = [
            self::VIEWCONTROL_KEY_PAGINATION => $this->getViewControlPagination($total_count),
            self::VIEWCONTROL_KEY_ORDERING => $this->getViewControlOrdering($total_count),
            self::VIEWCONTROL_KEY_FIELDSELECTION => $this->getViewControlFieldSelection(),
            /*self::KEY_highlighted_rows => $this->view_control_factory->fieldSelection(
                [
                    '123' => '123',
                    '8749' => '8749',
                    '8751' => '8751',
                    '867' => '867',
                ],
                '',
                'apply'
            )*/
        ];
        $view_controls = array_filter($view_controls);
        return $this->view_control_container_factory->standard($view_controls);
    }

    /*
        public function withHighlightedRows(array $highlighted_rows): self
        {
            $clone = clone $this;
            $clone->highlighted_rows = $highlighted_rows;
            return $clone;
        }

        public function getHighlightedRows(): array
        {
            return $this->highlighted_rows;
        }
    */
}
