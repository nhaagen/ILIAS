<?php declare(strict_types=1);

/* Copyright (c) 2022 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Wizard;

use ILIAS\UI\Component\Input\Container\Wizard as W;
use ILIAS\UI\Component\Listing as Listing;
use Psr\Http\Message\ServerRequestInterface;

class StaticSequence extends Wizard implements W\StaticSequence
{
    protected WorkflowListingFactory $listing;
    
    public function __construct(
        Listing\Factory $listing_factory,
        W\Storage $storage,
        array $steps,
        string $post_url,
        string $title,
        string $description
    ) {
        $this->listing = $lsiting_factory->workflow()->linear('', $steps);
        parent::__construct(
            $storage,
            $post_url,
            $title,
            $description
        );
    }
}
