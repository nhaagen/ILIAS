<?php declare(strict_types=1);

/* Copyright (c) 2022 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Wizard;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Component\Input\Container\Wizard;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use LogicException;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer) : string
    {
        $this->checkComponent($component);

        if ($component instanceof Wizard\Dynamic) {
            return $this->renderStandard($component, $default_renderer);
        }

        if ($component instanceof Wizard\StaticSequence) {
            return $this->renderStatic($component, $default_renderer);
        }

        throw new LogicException("Cannot render: " . get_class($component));
    }

    protected function renderStandard(Wizard\Wizard $component, RendererInterface $default_renderer) : string
    {
        $step_factory = $component->getStepFactory();
        $data = $component->getStoredData();

        $step = $component->getStepBuilder()
            ->build($step_factory, $data)
            ->withNameFrom($component->getNameSource());

        $submit_caption = $step->getSubmitCaption() ?? $this->txt("next");
        $submit_button = $this->getUIFactory()->button()->standard($submit_caption, "");

        $tpl = $this->getTemplate("tpl.wizard.html", true, true);
        $tpl->setVariable("URL", $component->getPostURL());
        $tpl->setVariable("WIZARD_TITLE", $component->getTitle());
        $tpl->setVariable("WIZARD_DESCRIPTION", $component->getDescription());
        $tpl->setVariable("STEP_TITLE", $step->getTitle());
        $tpl->setVariable("STEP_DESCRIPTION", $step->getDescription());
        $tpl->setVariable("BUTTONS_BOTTOM", $default_renderer->render($submit_button));
        $tpl->setVariable("INPUTS", $default_renderer->render($step->getInputs()));
        return $tpl->get();
    }

    protected function renderStatic(Wizard\StaticSequence $component, RendererInterface $default_renderer) : string
    {
        $step_factory = $component->getStepFactory();
        $data = $component->getStoredData();

        global $DIC;
        $query_wrapper = $DIC['http']->wrapper()->query();

        if ($query_wrapper->has($component::QUERY_PARAM_STEPNR_JUMP)) {
            $current_step = $query_wrapper->retrieve(
                $component::QUERY_PARAM_STEPNR_JUMP,
                $this->getRefinery()->kindlyTo()->int()
            );
            $component = $component->withCurrentStep($current_step);
        }

        $step = $component->getStepBuilder()
            ->withCurrentStep($component->getCurrentStep())
            ->build($step_factory, $data)
            ->withNameFrom($component->getNameSource());


        $url = $component->getPostURL();
        $param_name = $component::QUERY_PARAM_STEPNR;
        $param_value = $component->getCurrentStep();
        if (!strpos($url, '?')) {
            $url .= '?' . $param_name . '=' . $param_value;
        } else {
            $base = substr($url, 0, strpos($url, '?') + 1);
            $query = parse_url($url, PHP_URL_QUERY);
            parse_str($query, $params);
            $params[$param_name] = $param_value;
            unset($params[$component::QUERY_PARAM_STEPNR_JUMP]);
            $url = $base . http_build_query($params);
        }


        $f = $this->getUIFactory()->listing()->workflow();
        $listing_steps = [];
        foreach ($component->getStepBuilder()->getStepDescriptions() as $sd) {
            list($idx, $title, $description) = $sd;
            $action = $url . '&' . $component::QUERY_PARAM_STEPNR_JUMP . '=' . $idx;
            $listing_steps[] = $f->step($title, $description, $action);
        }
        $listing = $f->linear('', $listing_steps)
            ->withActive($component->getCurrentStep());

        $submit_caption = $step->getSubmitCaption() ?? $this->txt("next");
        $submit_button = $this->getUIFactory()->button()->standard($submit_caption, "");

        $tpl = $this->getTemplate("tpl.wizard.html", true, true);
        $tpl->setVariable("URL", $url);
        $tpl->setVariable("WIZARD_TITLE", $component->getTitle());
        $tpl->setVariable("WIZARD_DESCRIPTION", $component->getDescription());
        $tpl->setVariable("LISTING", $default_renderer->render($listing));
        $tpl->setVariable("BUTTONS_BOTTOM", $default_renderer->render($submit_button));
        $tpl->setVariable("INPUTS", $default_renderer->render($step->getInputs()));
        return $tpl->get();
    }


    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName() : array
    {
        return [
            Component\Input\Container\Wizard\Dynamic::class,
            Component\Input\Container\Wizard\StaticSequence::class
        ];
    }
}
