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

namespace ILIAS\UI\Implementation\Component\Menu;

use ILIAS\UI\Component;
use ILIAS\UI\Component\Menu as IMenu;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\Data\Text\SimpleDocumentMarkdown;
use ILIAS\UI\Component\Clickable;

/**
 * Basic Menu Control
 */
abstract class Menu implements IMenu\Menu
{
    use ComponentHelper;

    /**
     * @var string
     */
    protected $label;
    protected ?SimpleDocumentMarkdown $description = null;
    protected ?Clickable $node_action = null;

    /**
     * @var array<Component\Menu\Sub, Component\Clickable, Component\Link\Link, Component\Divider\Horizontal, Component\Input\Field\Node\Node>
     */
    protected array $items = [];

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return $this->label;
    }


    /**
     * @inheritdoc
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getDescription(): ?SimpleDocumentMarkdown
    {
        return $this->description;
    }

    public function getNodeaction(): ?Clickable
    {
        return $this->node_action;
    }

    protected function checkItemParameter(array $items): void
    {
        $classes = [
            Component\Menu\Sub::class,
            Component\Clickable::class,
            Component\Link\Link::class,
            Component\Divider\Horizontal::class,
            Component\Input\Field\Node\Node::class,
        ];
        $this->checkArgListElements("items", $items, $classes);
    }
}
