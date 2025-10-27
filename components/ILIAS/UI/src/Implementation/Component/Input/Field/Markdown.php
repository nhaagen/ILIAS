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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component\Input\Field\MarkdownRenderer;
use ILIAS\UI\Component\Input\Field\Markdown as MarkdownInterface;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
//use ILIAS\Data\Description\Description as DataDescription;
use ILIAS\UI\Implementation\Component\Menu\Menu;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\Signal;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Markdown extends Textarea implements MarkdownInterface
{
    public const MUSTACHE_SIGNAL_OPTION = 'mustache_variable';

    protected ?Menu $variables_selection = null;
    protected ?Signal $mustache_variable_signal = null;

    public function __construct(
        DataFactory $data_factory,
        Refinery $refinery,
        protected SignalGeneratorInterface $signal_generator,
        protected MarkdownRenderer $md_renderer,
        string $label,
        ?string $byline,
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);
        $this->initSignals();
    }

    public function getMarkdownRenderer(): MarkdownRenderer
    {
        return $this->md_renderer;
    }

    public function getMustacheVariablesSelection(): ?Menu
    {
        return $this->variables_selection;
    }

    public function withMustacheVariablesSelection(Menu $variables_selection): self
    {
        $clone = clone $this;
        $clone->variables_selection = $variables_selection;
        return $clone;
    }

    protected function initSignals(): void
    {
        $this->mustache_variable_signal = $this->signal_generator->create();
    }

    public function withResetSignals(): self
    {
        $clone = clone $this;
        $clone->initSignals();
        return $clone;
    }

    public function getMustacheVaribaleSignal(): Signal
    {
        return $this->mustache_variable_signal;
    }
}
