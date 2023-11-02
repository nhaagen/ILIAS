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
use ILIAS\UI\Implementation\Render\ResourceRegistry;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        $this->checkComponent($component);

        if ($component instanceof Filter\Standard) {
            return $this->renderStandard($component, $default_renderer);
        }

        throw new LogicException("Cannot render: " . get_class($component));
    }

    /**
     * Render standard filter
     */
    protected function renderStandard(Filter\Standard $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.standard_filter.html", true, true);

        $this->renderInputs($tpl, $default_renderer, $component);
        $this->renderExpandAndCollapse($tpl, $default_renderer, $component->isExpanded());

        //$component = $this->registerSignals($component);
        $is_expanded = $component->isExpanded() ? 'true' : 'false';
        $is_activated = $component->isActivated() ? 'true' : 'false';
        $component = $component->withAdditionalOnLoadCode(
            fn($id) => "il.UI.filter.init('$id', $is_expanded, $is_activated)"
        );
        $id = $this->bindJavaScript($component);
        $tpl->setVariable('ID_FILTER', $id);


        // render apply and reset buttons
        $this->renderApplyAndReset($tpl, $component, $default_renderer);

        // render toggle button
        $this->renderToggleButton($tpl, $component, $default_renderer);


        return $tpl->get();
    }

    protected function registerSignals(Filter\Filter $filter): Filter\Filter
    {
        $update = $filter->getUpdateSignal();
        return $filter->withAdditionalOnLoadCode(fn($id) => "$(document).on('$update', function(event, signalData) {
                il.UI.filter.onInputUpdate(event, signalData, '$id'); return false; 
            });");
    }

    protected function renderExpandAndCollapse(
        Template $tpl,
        RendererInterface $default_renderer,
        bool $is_expanded
    ): void {
        $f = $this->getUIFactory();
        $tpl->setVariable("OPENER", $default_renderer->render([
            $f->button()->bulky($f->symbol()->glyph()->expand(), $this->txt("filter"), ""),
            $f->button()->bulky($f->symbol()->glyph()->collapse(), $this->txt("filter"), "")
        ]));
        if ($is_expanded) {
            $tpl->setVariable("ARIA_EXPANDED", "'true'");
            $tpl->setVariable("INPUTS_EXPANDED", "in");
        } else {
            $tpl->setVariable("ARIA_EXPANDED", "'false'");
            $tpl->setVariable("INPUTS_ACTIVE_EXPANDED", "in");
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
        RendererInterface $default_renderer,
        Filter\Standard $component
    ): void {

        if ($component->isActivated()) {
            $tpl->touchBlock("enabled");
        } else {
            $tpl->touchBlock("disabled");
        }

        $renderer = $default_renderer->withAdditionalContext($component);

        $input_group = $component->getInputGroup()
            ->withOnUpdate(
                $component->getUpdateSignal()
            );

        $inputs_html = [];
        $user_fields = $component->getUserEditableInputs();
        foreach ($component->getInputGroup()->getInputs() as $input) {
            if(in_array($input, $user_fields)) {
                $inputs_html[] = $renderer->render($input);
            } else {
                $inputs_html[] = $default_renderer->render($input);
            }
        }

        //$tpl->setVariable("INPUTS", $renderer->render($input_group));
        $tpl->setVariable("INPUTS", implode('', $inputs_html));

        //active filter values representation for collapsed mode
        $active_filter_values = false;
        foreach($user_fields as $input) {
            $label = $input->getLabel();
            $value = $input->getValue();
            if($label && $value) {
                $active_filter_values = true;
                $tpl->setCurrentBlock("active_inputs");
                $tpl->setVariable("INPUT_ACTIVE_VALUE", $label . ': ' . $value);
                $tpl->parseCurrentBlock();
            }
        }
        if($active_filter_values) {
            $tpl->setCurrentBlock("active_inputs_section");
            $tpl->parseCurrentBlock();
        }

        // The remaining parameters for the filter controls need to be stuffed into
        // hidden fields, so the browser passes them as query parameters once the
        // form is submitted.
        $input_names = $component->getComponentInternalNames();

        if(! $request = $component->getRequest()) {
            throw new \LogicException('No request was passed to the filter');
        } else {
            $query_params = array_filter(
                $request->getQueryParams(),
                fn($k) => ! in_array($k, $input_names),
                ARRAY_FILTER_USE_KEY
            );

            foreach ($query_params as $k => $v) {
                if (is_array($v)) {
                    foreach (array_values($v) as $arrv) {
                        $tpl->setCurrentBlock('param');
                        $tpl->setVariable("PARAM_NAME", $k . '[]');
                        $tpl->setVariable("VALUE", $arrv);
                        $tpl->parseCurrentBlock();
                    }
                } else {
                    $tpl->setCurrentBlock('param');
                    $tpl->setVariable("PARAM_NAME", $k);
                    $tpl->setVariable("VALUE", $v);
                    $tpl->parseCurrentBlock();
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/Input/Container/dist/filter.min.js');
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName(): array
    {
        return array(Filter\Standard::class);
    }
}
