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

namespace ILIAS\MetaData\Copyright;

use ILIAS\Data\URI;

class CopyrightData implements CopyrightDataInterface
{
    protected string $full_name;
    protected ?URI $link;
    protected ?URI $image_link = null;
    protected string $image_file = '';
    protected string $alt_text;
    protected bool $fall_back_to_default_image;

    public function __construct(
        string $full_name,
        ?URI $link,
        ?URI $image_link,
        string $image_file,
        string $alt_text,
        bool $fall_back_to_default_image
    ) {
        $this->full_name = $full_name;
        $this->link = $link;
        $this->alt_text = $alt_text;
        $this->image_link = $image_link;
        $this->image_file = $image_file;
        $this->fall_back_to_default_image = $fall_back_to_default_image;
    }

    public function fullName(): string
    {
        return $this->full_name;
    }

    public function link(): ?URI
    {
        return $this->link;
    }

    public function hasImage(): bool
    {
        return $this->isImageLink() || $this->imageFile() !== '';
    }

    public function isImageLink(): bool
    {
        return !is_null($this->imageLink());
    }

    public function imageLink(): ?URI
    {
        return $this->image_link;
    }

    public function imageFile(): string
    {
        return $this->image_file;
    }

    public function altText(): string
    {
        return $this->alt_text;
    }

    public function fallBackToDefaultImage(): bool
    {
        return $this->fall_back_to_default_image;
    }
}
