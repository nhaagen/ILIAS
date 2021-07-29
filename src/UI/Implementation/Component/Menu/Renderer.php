<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Menu;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\Menu;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        $this->checkComponent($component);

        /**
         * @var $component Menu\Menu
         */
        $html = $this->renderMenu($component, $default_renderer);

        if ($component instanceof Menu\Drilldown) {
            $tpl_name = "tpl.drilldown.html";
            $tpl = $this->getTemplate($tpl_name, true, true);
            $tpl->setVariable('TITLE', $component->getLabel());
            $tpl->setVariable('BACKNAV', 'BACK BUTTON');
            $tpl->setVariable('DRILLDOWN', $html);

            $component = $component->withAdditionalOnLoadCode(function ($id) {
                return "il.UI.menu.drilldown.init('$id');";
            });
            $id = $this->bindJavaScript($component);
            $tpl->setVariable("ID", $id);

            return $tpl->get();
        }

        return $html;
    }

    /**
     * Render a Menu.
     */
    protected function renderMenu(
        Menu\Menu $component,
        RendererInterface $default_renderer
    ) : string {
        $tpl_menu = $this->getTemplate('tpl.menu.html', true, true);

        $label = $component->getLabel();
        if (!is_string($label)) {
            $label = $default_renderer->render($label);
        }
        $tpl_menu->setVariable('LABEL', $label);

        /*
                if ($component->isInitiallyActive()) {
                    $tpl->touchBlock('active');
                }
        */
  
        $html = '';
        foreach ($component->getItems() as $item) {
            $tpl_item = $this->getTemplate('tpl.menuitem.html', true, true);
            $tpl_item->setVariable('ITEM', $default_renderer->render($item));
            $html .= $tpl_item->get();
        }
        $tpl_menu->setVariable('ITEMS', $html);
        return $tpl_menu->get();
    }

    /**
     * @inheritdoc
     */
    public function registerResources(\ILIAS\UI\Implementation\Render\ResourceRegistry $registry)
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/Menu/drilldown.js');
    }
    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return array(
            Menu\Menu::class
        );
    }
}
