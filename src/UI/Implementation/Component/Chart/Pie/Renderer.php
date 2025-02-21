<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Component\Chart\Pie;

use ILIAS\UI\Component;
use ILIAS\UI\Component\Chart\Pie;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Renderer as RendererInterface;

//use ILIAS\Data\Dimension\Dimension;
//use stdClass;
//use LogicException;

class Renderer extends AbstractComponentRenderer
{
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        $this->checkComponent($component);

        if ($component instanceof Pie\Standard) {
            return $this->renderPie($component, $default_renderer);
        }

        throw new LogicException("Cannot render: " . get_class($component));
    }

    protected function renderPie(
        Pie\Standard $component,
        RendererInterface $default_renderer
    ): string {
        $tpl = $this->getTemplate("tpl.pie.html", true, true);

        foreach ($component->getSections() as $val) {
            $tpl->setCurrentBlock('chart_section');
            $tpl->setVariable('VALUE', (string) $val);
            $tpl->parseCurrentBlock();
        }
        return $tpl->get();
    }

    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        //$registry->register('./src/UI/templates/js/Chart/Bar/dist/bar.js');
    }

    protected function getComponentInterfaceName(): array
    {
        return [Pie\Standard::class];
    }
}
