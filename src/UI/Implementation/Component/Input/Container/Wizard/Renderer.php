<?php declare(strict_types=1);

/* Copyright (c) 2021 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

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

        if ($component instanceof Wizard\Wizard) {
            return $this->renderStandard($component, $default_renderer);
        }

        throw new LogicException("Cannot render: " . get_class($component));
    }

    protected function renderStandard(Wizard\Wizard $component, RendererInterface $default_renderer) : string
    {
        $factory = $this->getUIFactory();
        $refinery = $this->getRefinery();

        $field_factory = $factory->input()->field();
        $nullstep = $factory->input()->container()->wizard()->step(
            $field_factory
        );
        $data = $component->getData();

        $step = $component->getStepBuilder()->build(
            $field_factory,
            $refinery,
            $nullstep,
            $data
        );
    

        $submit_caption = $step->getSubmitCaption() ?? $this->txt("next");
        $submit_button = $factory->button()->standard($submit_caption, "");

        $tpl = $this->getTemplate("tpl.wizard.html", true, true);

        if ($step->getPostURL() != "") {
            $tpl->setCurrentBlock("action");
            $tpl->setVariable("URL", $step->getPostURL());
            $tpl->parseCurrentBlock();
        }
        /*
        */
        
        $tpl->setVariable("WIZARD_TITLE", $component->getTitle());
        $tpl->setVariable("WIZARD_DESCRIPTION", $component->getDescription());
        
        $tpl->setVariable("STEP_TITLE", $step->getTitle());
        $tpl->setVariable("STEP_DESCRIPTION", $step->getDescription());
        

        $tpl->setVariable("BUTTONS_BOTTOM", $default_renderer->render($submit_button));
        $tpl->setVariable("INPUTS", $default_renderer->render($step->getInputGroup()));
        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName() : array
    {
        return array(Component\Input\Container\Wizard\Dynamic::class);
    }
}
