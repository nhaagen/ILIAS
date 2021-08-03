<?php declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Menu;

use ILIAS\UI\Component\Menu as IMenu;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Signal;

/**
 * Drilldown Menu Control
 */
class Drilldown extends LabeledMenu implements IMenu\Drilldown
{
    /**
     * @var Signal
     */
    protected $signal;

    /**
     * @param \ILIAS\UI\Component\Clickable | string $label
     * @param array <Sub | Component\Clickable | Component\Divider\Horizontal> $items
     */
    public function __construct(
        SignalGeneratorInterface $signal_generator,
        $label,
        array $items
    ) {
        $this->checkLabelParameter($label);
        $this->checkItemParameter($items);
        $this->label = $label;
        $this->items = $items;
        $this->signal = $signal_generator->create();
    }

    public function getBacklinkSignal() : Signal
    {
        return $this->signal;
    }
}
