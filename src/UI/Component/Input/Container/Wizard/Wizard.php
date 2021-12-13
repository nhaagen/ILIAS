<?php declare(strict_types=1);

/* Copyright (c) 2021 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Container\Wizard;

use ILIAS\UI\Component\Component;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This describes a Wizard
 */
interface Wizard extends Component
{
    public function getTitle() : string;
    public function withTitle(string $title) : self;
    
    public function getDescription() : string;
    public function withDescription(string $description) : self;

    public function withRequest(ServerRequestInterface $request) : self;
    public function withData(mixed $data) : self;
    public function getData() : mixed;
    
    public function isFinished() : bool;
}
