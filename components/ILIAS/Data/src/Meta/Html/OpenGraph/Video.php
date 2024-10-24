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
 */

declare(strict_types=1);

namespace ILIAS\Data\Meta\Html\OpenGraph;

use ILIAS\Data\Meta\Html\TagCollection;
use ILIAS\Data\Meta\Html\NullTag;
use ILIAS\Data\URI;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Video extends Resource
{
    public function __construct(
        URI $video_url,
        string $mime_type,
        ?int $width = null,
        ?int $height = null,
    ) {
        parent::__construct(
            $video_url,
            $mime_type,
            (null !== $width) ? new Text('og:video:width', (string) $width) : new NullTag(),
            (null !== $height) ? new Text('og:video:height', (string) $height) : new NullTag(),
        );
    }

    protected function getPropertyName(): string
    {
        return 'og:video';
    }
}
