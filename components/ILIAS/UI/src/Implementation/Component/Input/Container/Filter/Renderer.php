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

namespace ILIAS\UI\Implementation\Component\Input\Container\Filter;

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\Button\Toggle;
use ILIAS\UI\Implementation\Component\Input\Container\Filter;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Renderer as RendererInterface;
use LogicException;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        $this->checkComponent($component);

        if ($component instanceof Filter\Standard) {
            if (!$component->getRequest()) {
                throw new LogicException("No request was passed to the container. Please call 'withRequest' on the Container.");
            }
            return $this->renderStandard($component, $default_renderer);
        }

        throw new LogicException("Cannot render: " . get_class($component));
    }

    /**
     * Render standard filter
     */
    protected function renderStandard(Filter\Standard $component, RendererInterface $default_renderer): string
    {
        //$tpl = $this->getTemplate("tpl.standard_filter.html", true, true);
        $tpl = $this->getTemplate("tpl.filter_container.html", true, true);
        $f = $this->getUIFactory();

        $signal_collapse = '#';
        $signal_expand = '#';
        $signal_toggle_on = '#';
        $signal_toggle_off = '#';
        $signal_apply = '#';
        $signal_reset = '#';

        $collapse = $f->button()->bulky($f->symbol()->glyph()->collapse(), $this->txt("filter"), $signal_collapse);
        $expand = $f->button()->bulky($f->symbol()->glyph()->expand(), $this->txt("filter"), $signal_expand);
        $toggle = $f->button()->toggle("", $signal_toggle_on, $signal_toggle_off, $component->isActivated());
        $apply = $f->button()->bulky($f->symbol()->glyph()->apply(), $this->txt("apply"), $signal_apply);
        $reset = $f->button()->bulky($f->symbol()->glyph()->reset(), $this->txt("reset"), $signal_reset);

        $tpl->setVariable("COLLAPSE", $default_renderer->render($collapse));
        $tpl->setVariable("EXPAND", $default_renderer->render($expand));
        $tpl->setVariable("TOGGLE", $default_renderer->render($toggle));
        $tpl->setVariable("APPLY", $default_renderer->render($apply));
        $tpl->setVariable("RESET", $default_renderer->render($reset));


        $tpl->setVariable(
            "INPUTS",
            $default_renderer->withAdditionalContext($component)
            ->render($component->getInputs())
        );


        $submission_signal = $component->getUpdateSignal();
        $component = $component->withAdditionalOnLoadCode(
            fn($id) => "$(document).on('{$submission_signal}',
                function(event, signalData) { 
                    document.getElementById('{$id}').submit();
                    return false;
                });"
        );
        $tpl->setVariable("ID", $this->bindJavaScript($component));

        $input_names = array_keys($component->getComponentInternalValues());
        $query_params = array_filter(
            $component->getRequest()?->getQueryParams(),
            fn($k) => !in_array($k, $input_names),
            ARRAY_FILTER_USE_KEY
        );


        // JavaScript
        //$component = $this->registerSignals($component);
        //$id = $this->bindJavaScript($component);
        //$tpl->setVariable('ID_FILTER', $id);

        // render expand and collapse
        //$this->renderExpandAndCollapse($tpl, $component, $default_renderer);

        // render apply and reset buttons
        //$this->renderApplyAndReset($tpl, $component, $default_renderer);

        // render toggle button
        //$this->renderToggleButton($tpl, $component, $default_renderer);

        // render inputs
        //$this->renderInputs($tpl, $component, $default_renderer);

        return $tpl->get();
    }

    protected function registerSignals(Filter\Filter $filter): Filter\Filter
    {
        $update = $filter->getUpdateSignal();
        return $filter->withAdditionalOnLoadCode(fn($id) => "$(document).on('$update', function(event, signalData) {
                il.UI.filter.onInputUpdate(event, signalData, '$id'); return false; 
            });");
    }

    /**
     * Render expand/collapse section
     *
     * @param Template $tpl
     * @param Filter\Standard $component
     * @param RendererInterface $default_renderer
     */
    protected function renderExpandAndCollapse(
        Template $tpl,
        Filter\Standard $component,
        RendererInterface $default_renderer
    ): void {
        $f = $this->getUIFactory();

        $tpl->setCurrentBlock("action");
        $tpl->setVariable("ACTION_NAME", "expand");
        $tpl->setVariable("ACTION", $component->getExpandAction());
        $tpl->parseCurrentBlock();

        $opener_expand = $f->button()->bulky($f->symbol()->glyph()->expand(), $this->txt("filter"), "")
            ->withAdditionalOnLoadCode(fn($id) => "$('#$id').on('click', function(event) {
					il.UI.filter.onAjaxCmd(event, '$id', 'expand');
					event.preventDefault();
			    });");

        $tpl->setCurrentBlock("action");
        $tpl->setVariable("ACTION_NAME", "collapse");
        $tpl->setVariable("ACTION", $component->getCollapseAction());
        $tpl->parseCurrentBlock();

        $opener_collapse = $f->button()->bulky($f->symbol()->glyph()->collapse(), $this->txt("filter"), "")
            ->withAdditionalOnLoadCode(fn($id) => "$('#$id').on('click', function(event) {
					il.UI.filter.onAjaxCmd(event, '$id', 'collapse');
					event.preventDefault();
			    });");

        if ($component->isExpanded() == false) {
            $opener = [$opener_collapse, $opener_expand];
            $tpl->setVariable("OPENER", $default_renderer->render($opener));
            $tpl->setVariable("ARIA_EXPANDED", "'false'");
            $tpl->setVariable("INPUTS_ACTIVE_EXPANDED", "in");
        } else {
            $opener = [$opener_expand, $opener_collapse];
            $tpl->setVariable("OPENER", $default_renderer->render($opener));
            $tpl->setVariable("ARIA_EXPANDED", "'true'");
            $tpl->setVariable("INPUTS_EXPANDED", "in");
        }
    }

    /**
     * Render apply and reset
     */
    protected function renderApplyAndReset(
        Template $tpl,
        Filter\Standard $component,
        RendererInterface $default_renderer
    ): void {
        $f = $this->getUIFactory();

        $tpl->setCurrentBlock("action");
        $tpl->setVariable("ACTION_NAME", "apply");
        $tpl->setVariable("ACTION", $component->getApplyAction());
        $tpl->parseCurrentBlock();

        // render apply and reset buttons
        $apply = $f->button()->bulky($f->symbol()->glyph()->apply(), $this->txt("apply"), "")
            ->withOnLoadCode(fn($id) => "$('#$id').on('click', function(event) {
                        il.UI.filter.onCmd(event, '$id', 'apply');
                        return false; // stop event propagation
                });
                $('#$id').closest('.il-filter').find(':text').on('keypress', function(ev) {
                    if (typeof ev != 'undefined' && typeof ev.keyCode != 'undefined' && ev.keyCode == 13) {
                        il.UI.filter.onCmd(event, '$id', 'apply');
                        return false; // stop event propagation
                    }
                });
                ");
        $reset = $f->button()->bulky($f->symbol()->glyph()->reset(), $this->txt("reset"), $component->getResetAction());

        $tpl->setVariable("APPLY", $default_renderer->render($apply));
        $tpl->setVariable("RESET", $default_renderer->render($reset));
    }

    /**
     * Render toggle button
     */
    protected function renderToggleButton(
        Template $tpl,
        Filter\Standard $component,
        RendererInterface $default_renderer
    ): void {
        $f = $this->getUIFactory();

        $tpl->setCurrentBlock("action");
        $tpl->setVariable("ACTION_NAME", "toggleOn");
        $tpl->setVariable("ACTION", $component->getToggleOnAction());
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("action");
        $tpl->setVariable("ACTION_NAME", "toggleOff");
        $tpl->setVariable("ACTION", $component->getToggleOffAction());
        $tpl->parseCurrentBlock();

        $signal_generator = new SignalGenerator();
        $toggle_on_signal = $signal_generator->create();
        $toggle_off_signal = $signal_generator->create();
        /**
         * @var $toggle Toggle
         */
        $toggle = $f->button()->toggle("", $toggle_on_signal, $toggle_off_signal, $component->isActivated());
        $toggle = $toggle->withAdditionalOnLoadCode(fn($id) => "$(document).on('$toggle_on_signal',function(event) {
                        il.UI.filter.onCmd(event, '$id', 'toggleOn');
                        return false; // stop event propagation
            });");
        $toggle = $toggle->withAdditionalOnLoadCode(fn($id) => "$(document).on('$toggle_off_signal',function(event) {
                        il.UI.filter.onCmd(event, '$id', 'toggleOff');
                        return false; // stop event propagation
            });");

        $tpl->setVariable("TOGGLE", $default_renderer->render($toggle));
    }

    /**
     * Render inputs
     */
    protected function renderInputs(
        Template $tpl,
        Filter\Standard $component,
        RendererInterface $default_renderer
    ): void {
        // pass information on what inputs should be initially rendered
        //$is_input_rendered = $component->isInputRendered();
        foreach ($component->getInputs() as $k => $input) {
            $is_rendered = true; //current($is_input_rendered);
            $tpl->setCurrentBlock("status");
            $tpl->setVariable("FIELD", $k);
            $tpl->setVariable("VALUE", (int) $is_rendered);
            $tpl->parseCurrentBlock();
            //next($is_input_rendered);
        }

        // render inputs
        $input_group = $component->getInputGroup();
        if ($component->isActivated()) {
            $tpl->touchBlock("enabled");
        } else {
            $tpl->touchBlock("disabled");
        }
        for ($i = 1; $i <= count($component->getInputs()); $i++) {
            $tpl->setCurrentBlock("active_inputs");
            $tpl->setVariable("ID_INPUT_ACTIVE", $i);
            $tpl->parseCurrentBlock();
        }
        if (count($component->getInputs()) > 0) {
            $tpl->setCurrentBlock("active_inputs_section");
            $tpl->parseCurrentBlock();
        }

        $input_group = $input_group->withOnUpdate($component->getUpdateSignal());

        $renderer = $default_renderer->withAdditionalContext($component);
        $tpl->setVariable("INPUTS", $renderer->render($input_group));
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName(): array
    {
        return array(Filter\Standard::class);
    }

    protected function getComponentInternalNames(Component\Input\Group $component, array $names = []): array
    {
        foreach ($component->getInputs() as $input) {
            if ($input instanceof Component\Input\Group) {
                $names = $this->getComponentInternalNames($input, $names);
            }
            if ($input instanceof HasInputGroup) {
                $names = $this->getComponentInternalNames($input->getInputGroup(), $names);
            }
            $names[] = $input->getName();
        }

        return $names;
    }
}
