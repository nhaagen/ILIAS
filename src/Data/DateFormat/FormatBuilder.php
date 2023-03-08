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

namespace ILIAS\Data\DateFormat;

/**
 * Builds a Date Format with split up elements to ease conversion.
 * Internal constants are based on options for php date format.
 */
class FormatBuilder
{
    /** @var string[] */
    private array $format = [];

    /**
     * Get the configured DateFormat and reset format.
     */
    public function get(): DateFormat
    {
        $df = new DateFormat($this->format);
        $this->format = [];
        return $df;
    }

    public function initWithFormat(DateFormat $format): self
    {
        $this->format = $format->toArray();
        return $this;
    }

    /**
     * Append tokens to format.
     */
    public function dot(): self
    {
        $this->format[] = DateFormat::DOT;
        return $this;
    }

    public function comma(): self
    {
        $this->format[] = DateFormat::COMMA;
        return $this;
    }

    public function dash(): self
    {
        $this->format[] = DateFormat::DASH;
        return $this;
    }

    public function slash(): self
    {
        $this->format[] = DateFormat::SLASH;
        return $this;
    }

    public function space(): self
    {
        $this->format[] = DateFormat::SPACE;
        return $this;
    }

    public function day(): self
    {
        $this->format[] = DateFormat::DAY;
        return $this;
    }

    public function dayOrdinal(): self
    {
        $this->format[] = DateFormat::DAY_ORDINAL;
        return $this;
    }

    public function weekday(): self
    {
        $this->format[] = DateFormat::WEEKDAY;
        return $this;
    }

    public function weekdayShort(): self
    {
        $this->format[] = DateFormat::WEEKDAY_SHORT;
        return $this;
    }

    public function week(): self
    {
        $this->format[] = DateFormat::WEEK;
        return $this;
    }

    public function month(): self
    {
        $this->format[] = DateFormat::MONTH;
        return $this;
    }

    public function monthSpelled(): self
    {
        $this->format[] = DateFormat::MONTH_SPELLED;
        return $this;
    }

    public function monthSpelledShort(): self
    {
        $this->format[] = DateFormat::MONTH_SPELLED_SHORT;
        return $this;
    }

    public function year(): self
    {
        $this->format[] = DateFormat::YEAR;
        return $this;
    }

    public function twoDigitYear(): self
    {
        $this->format[] = DateFormat::YEAR_TWO_DIG;
        return $this;
    }
}
