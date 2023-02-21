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

namespace ILIAS\UI\Implementation\Component\Input\Container\ViewControl;

use ILIAS\UI\Component\Input\Container\ViewControl as I;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Signal;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Implementation\Component\Input;
use ILIAS\UI\Implementation\Component\Input\Container\QueryParamsFromServerRequest;

use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation;

abstract class ViewControl implements I\ViewControl
{
    use ComponentHelper;
    use JavaScriptBindable;

    protected array $controls;
    protected Signal $submit_signal;
    /**
     * @var Transformation[]
     */
    protected array $post_operations = [];

    public function __construct(
        SignalGeneratorInterface $signal_generator,
        Input\NameSource $name_source,
        array $controls
    ) {
        global $DIC;
        $this->data_factory = new \ILIAS\Data\Factory();

        $this->controls = array_map(
            fn ($input) => $input->withNameFrom($name_source),
            $controls
        );
        $this->submit_signal = $signal_generator->create();
    }

    public function getSubmissionSignal(): Signal
    {
        return $this->submit_signal;
    }

    public function getInputs(): array
    {
        return $this->controls;
    }

    public function withAdditionalTransformation(Transformation $trafo): self
    {
        $clone = clone $this;
        $clone->post_operations[] = $trafo;
        return $clone;
    }

    protected function applyOperations($res, $ops): Result
    {
        if ($res === null) {
            return $this->data_factory->ok($res);
        }

        $res = $this->data_factory->ok($res);
        foreach ($ops as $op) {
            if ($res->isError()) {
                return $res;
            }
            $res = $op->applyTo($res);
        }
        return $res;
    }

    public function withRequest(ServerRequestInterface $request): self
    {
        $data = new QueryParamsFromServerRequest($request);
        $set = [];
        foreach ($this->controls as $control) {
            $control = $control->withInput($data);
            $set[] = $control;
        }
        $clone = clone $this;
        $clone->controls = $set;
        return $clone;
    }

    public function getData()
    {
        $data = array_merge(
            ...array_map(
                fn ($c) => [$c->getName() => $c->getContent()->value()],
                $this->getInputs()
            )
        );

        $content = $this->applyOperations($data, $this->post_operations);

        if (!$content->isOK()) {
            return null;
        }
        return $content->value();
    }
}
