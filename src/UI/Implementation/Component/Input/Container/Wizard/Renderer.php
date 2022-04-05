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

        if ($component instanceof Wizard\Standard) {
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

        $name_source = new \ILIAS\UI\Implementation\Component\Input\FormInputNameSource();
        
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

    protected function renderStatic(Wizard\Wizard $component, RendererInterface $default_renderer) : string
    {
        $step_factory = $component->getStepFactory();
        $data = $component->getStoredData();

        $step = $component->getStepBuilder()
            ->withCurrentStep($component->getCurrentStep())
            ->build($step_factory, $data)
            ->withNameFrom($component->getNameSource());


        $submit_caption = $step->getSubmitCaption() ?? $this->txt("next");
        $submit_button = $this->getUIFactory()->button()->standard($submit_caption, "");

        $tpl = $this->getTemplate("tpl.wizard.html", true, true);

        $url = $component->getPostURL() . '&stepnr=' . $component->getCurrentStep();

        $tpl->setVariable("URL", $url);
        $tpl->setVariable("WIZARD_TITLE", $component->getTitle());
        $tpl->setVariable("WIZARD_DESCRIPTION", $component->getDescription());
        
        $tpl->setVariable("STEP_TITLE", $step->getTitle());
        $tpl->setVariable("STEP_DESCRIPTION", $step->getDescription());
        

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
