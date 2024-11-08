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

namespace ILIAS\UI\Implementation\Component\Input\Container\Filter;

use ILIAS\UI\Component\Input\Container\Filter as I;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Implementation\Component\Input;

class Standard extends Filter implements I\Standard
{
    /**
    * @inheritDoc
    */
    protected function extractRequestData(ServerRequestInterface $request): Input\InputData
    {
        $internal_input_data = new Input\ArrayInputData($this->getComponentInternalValues());

        return new StackedInputData(
            new Input\QueryParamsFromServerRequest($request),
            $this->stored_input,
            $internal_input_data,
        );
    }
}
