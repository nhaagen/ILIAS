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

namespace ILIAS\UI\Implementation\Component\Popover;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component\Popover
 */
class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        if (!$component instanceof Component\Popover\Popover) {
            $this->cannotHandleComponent($component);
        }

        $options = array(
            'title' => $this->escape($component->getTitle()),
            'placement' => $component->getPosition(),
            'multi' => true,
            'url' => $component->getAsyncContentUrl() ?? 'null'
        );

        if ($component->isFixedPosition()) {
            $options['style'] = "fixed";
        }

        $show = $component->getShowSignal();
        $replace = $component->getReplaceContentSignal();

        $component = $component->withAdditionalOnLoadCode(function ($id) use ($options, $show, $replace) {
            $options = json_encode($options);

            return
                "il.UI.popover.init('$id', JSON.parse('$options'));" .
                "$(document).on('$show', function(event, signalData) {
                    il.UI.popover.get('$id').showPopover(signalData, JSON.parse('$options'));
				});" .
                "$(document).on('$replace', function(event, signalData) {
					il.UI.popover.get('$id').replaceContentFromSignal(signalData);
				});"
            ;
        });


        $tpl = $this->getTemplate('tpl.popover.html', true, true);
        $id = $this->bindJavaScript($component);
        $tpl->setVariable('ID', $id);
        $tpl->setVariable('TITLE', $component->getTitle());
        $tpl->setVariable('CONTENT', $default_renderer->render($component->getContent()));


        return $tpl->get();

    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('assets/js/popover.min.js');
    }

    protected function renderStandardPopover(
        Component\Popover\Standard $popover,
        RendererInterface $default_renderer,
        string $id
    ): string {
        $tpl = $this->getTemplate('tpl.standard-popover-content.html', true, true);
        $tpl->setVariable('ID', $id);
        $tpl->setVariable('CONTENT', $default_renderer->render($popover->getContent()));

        return $tpl->get();
    }

    protected function renderListingPopover(
        Component\Popover\Listing $popover,
        RendererInterface $default_renderer,
        string $id
    ): string {
        $tpl = $this->getTemplate('tpl.listing-popover-content.html', true, true);
        $tpl->setVariable('ID', $id);
        foreach ($popover->getItems() as $item) {
            $tpl->setCurrentBlock('item');
            $tpl->setVariable('ITEM', $default_renderer->render($item));
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    protected function escape(string $str): string
    {
        return strip_tags(htmlentities($str, ENT_QUOTES, 'UTF-8'));
    }
}
