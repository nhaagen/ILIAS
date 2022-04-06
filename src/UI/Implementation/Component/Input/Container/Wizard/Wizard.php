<?php declare(strict_types=1);

/* Copyright (c) 2022 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Wizard;

use ILIAS\UI\Component\Input\Container\Wizard as W;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Input\Container\Form;
use ILIAS\UI\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\Input\Container\Wizard\WizardInputNameSource;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Baseclass for all wizards
 */
abstract class Wizard extends Form\Standard implements W\Wizard
{
    use ComponentHelper;
    protected W\Storage $storage;
    protected string $post_url;
    protected string $title;
    protected string $description;
    
    public function __construct(
        W\Storage $storage,
        WizardInputNameSource $name_source,
        string $post_url,
        string $title,
        string $description
    ) {
        $this->storage = $storage;
        $this->name_source = $name_source;
        $this->post_url = $post_url;
        $this->title = $title;
        $this->description = $description;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function getStoredData() : mixed
    {
        return $this->storage->get();
    }

    public function storeData(mixed $data) : void
    {
        $this->storage->set($data);
    }

    public function getNameSource() : WizardInputNameSource
    {
        return $this->name_source->withReset();
    }

    public function isFinished() : bool
    {
        $data = $this->getStoredData();
        return $this->getStepBuilder()->isComplete($data);
    }
    
    public function getStepFactory() : W\StepFactory
    {
        return $this->step_factory;
    }
}
