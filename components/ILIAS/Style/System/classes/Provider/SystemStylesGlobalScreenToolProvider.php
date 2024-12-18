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

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\Scope\Tool\Factory\Tool;
use ILIAS\UI\Component\Tree\Tree;
use ILIAS\UI\Implementation\Crawler\Entry\ComponentEntries as Entries;
use ILIAS\Data\URI;
use ILIAS\UI\Component\Menu\Drilldown as DrilldownMenu;
use ILIAS\UI\Component\Link\Bulky as BulkyLink;
use ILIAS\UI\Component\Menu\Sub as SubMenu;

/**
 * Provider for the Tree in the Main Bar Slate showing the UI Components
 */
class SystemStylesGlobalScreenToolProvider extends AbstractDynamicToolProvider
{
    /**
     * @inheritDoc
     */
    public function isInterestedInContexts(): \ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection
    {
        return $this->context_collection->administration();
    }

    /**
     * @inheritDoc
     */
    public function getToolsForContextStack(
        \ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts $called_contexts
    ): array {
        $last_context = $called_contexts->getLast();

        if ($last_context) {
            $additional_data = $last_context->getAdditionalData();
            if ($additional_data->is(ilSystemStyleDocumentationGUI::SHOW_TREE, true)) {
                return [
                    $this->buildTreeAsTool(),
                    $this->buildDDSlate()
                ];
            }
        }

        return [];
    }

    protected function buildDDSlate(): Tool
    {
        $id_generator = function ($id) {
            return $this->identification_provider->contextAwareIdentifier($id);
        };

        $title = $this->dic->language()->txt('documentation');
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard('stys', $title);

        return $this->factory
            ->tool($id_generator('system_styles_dd'))
            ->withTitle($title)
            ->withSymbol($icon)
            ->withContent(
                $this->dic->ui()->factory()->maincontrols()->slate()->drilldown(
                    $title,
                    $icon,
                    $this->getUIDrilldown()
                )
            );
    }

    protected function buildTreeAsTool(): Tool
    {
        $id_generator = function ($id) {
            return $this->identification_provider->contextAwareIdentifier($id);
        };

        $title = $this->dic->language()->txt('documentation');
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard('stys', $title);

        /**
         * @Todo, replace this with a proper Tree Slate
         */
        return $this->factory
            ->tool($id_generator('system_styles_tree'))
            ->withTitle($title)
            ->withSymbol($icon)
            ->withContent(
                $this->dic->ui()->factory()->legacy(
                    $this->dic->ui()->renderer()->render(
                        $this->getUITree()
                    )
                )
            );
    }


    protected function recurse($entries, $node): BulkyLink|SubMenu
    {
        if ($node->getChildren() === []) {
            $parent_class_hierarchy = [
                'ilAdministrationGUI',
                'ilObjStyleSettingsGUI',
                'ilSystemStyleMainGUI',
                'ilSystemStyleDocumentationGUI'
            ];
            $parent_link = $this->dic->ctrl()->getLinkTargetByClass($parent_class_hierarchy, 'entries');
            $parent_uri = new URI(ILIAS_HTTP_PATH . '/' . $parent_link);
            $uri = $parent_uri->withParameter('node_id', $node->getId());

            return $this->dic->ui()->factory()->link()->bulky(
                $this->dic->ui()->factory()->symbol()->icon()->standard('component', $node->getTitle()),
                $node->getTitle(),
                $uri
            );
        } else {
            $children = [];
            foreach ($node->getChildren() as $child) {
                $child_node = $entries->getEntryById($child);
                $children[] = $this->recurse($entries, $child_node);
            }
            sort($children);
            return $this->dic->ui()->factory()->menu()->sub(
                $node->getTitle(),
                $children
            );
        }
    }

    protected function getUIDrilldown(): DrilldownMenu
    {
        $entries = new Entries();
        $entries->addEntriesFromArray(require ilKitchenSinkDataCollectedObjective::PATH());
        $entry = $entries->getRootEntry();

        $items = $this->recurse($entries, $entry);
        $items = $items->getItems();

        return $this->dic->ui()->factory()->menu()->drilldown('UIComponent', $items);
    }

    protected function getUITree(): Tree
    {
        $entries = new Entries();
        $entries->addEntriesFromArray(require ilKitchenSinkDataCollectedObjective::PATH());

        $parent_class_hierarchy = ['ilAdministrationGUI',
                                   'ilObjStyleSettingsGUI',
                                   'ilSystemStyleMainGUI',
                                   'ilSystemStyleDocumentationGUI'
        ];

        $parent_link = $this->dic->ctrl()->getLinkTargetByClass($parent_class_hierarchy, 'entries');
        $parent_uri = new URI(ILIAS_HTTP_PATH . '/' . $parent_link);

        $refinery = $this->dic->refinery();
        $request_wrapper = $this->dic->http()->wrapper()->query();
        $current_opened_node_id = '';
        if ($request_wrapper->has('node_id')) {
            $current_opened_node_id = $request_wrapper->retrieve('node_id', $refinery->kindlyTo()->string());
        }

        $recursion = new KSDocumentationTreeRecursion($entries, $parent_uri, $current_opened_node_id);
        $f = $this->dic->ui()->factory();

        return $f->tree()->expandable('Label', $recursion)
                 ->withData([$entries->getRootEntry()])
                 ->withHighlightOnNodeClick(true);
    }
}
