<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Input\Container\Wizard;

use \ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use \ILIAS\Refinery\Factory as RefineryFactory;

interface StepBuilder
{
    public function build(
        FieldFactory $field_factory,
        RefineryFactory $refinery,
        Step $step,
        mixed $data
    ) : Step;
}
