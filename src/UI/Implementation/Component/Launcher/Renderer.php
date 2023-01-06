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

namespace ILIAS\UI\Implementation\Component\Launcher;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

//use ILIAS\UI\Implementation\Render\ResourceRegistry;
//use ILIAS\UI\Implementation\Render\Template;

class Renderer extends AbstractComponentRenderer
{
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        $this->checkComponent($component);
        if ($component instanceof Inline) {
            return $this->renderInline($component, $default_renderer);
        }
    }

    public function renderInline(Component\Component $component, RendererInterface $default_renderer): string
    {
        $ui_factory = $this->getUIFactory();
        return 'rendered, here';
    }

    protected function getComponentInterfaceName(): array
    {
        return [
            Inline::class
        ];
    }
}
