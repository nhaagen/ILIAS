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

namespace ILIAS\Data;

/**
 * Class DataSize
 *
 * This class provides the data size with additional information to
 * remove the work to calculate the size to different unit like GiB, GB usw.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 */
final class DataSize
{
    private const SIZE_FACTOR = 1000;
    public const Byte = 1;

    //binary
    public const KiB = 1024;
    public const MiB = 1048576;      //pow(1024, 2)
    public const GiB = 1073741824;
    public const TiB = 1099511627776;
    public const PiB = 1125899906842624;
    public const EiB = 1152921504606846976;
    public const ZiB = 1180591620717411303424;
    public const YiB = 1208925819614629174706176;

    //decimal
    public const KB = 1000;                            //kilobyte
    public const MB = 1000000;                         //megabyte
    public const GB = 1000000000;                      //gigabyte
    public const TB = 1000000000000;                   //terabyte
    public const PB = 1000000000000000;                //petabyte
    public const EB = 1000000000000000000;             //exabyte
    public const ZB = 1000000000000000000000;          //zettabyte
    public const YB = 1000000000000000000000000;       //yottabyte
    /**
     * @var array<int, string>
     */
    private static array $suffix_map = [
        self::Byte => 'B',

        self::KB => 'KB',
        self::KiB => 'KiB',

        self::MB => 'MB',
        self::MiB => 'MiB',

        self::GB => 'GB',
        self::GiB => 'GiB',

        self::TB => 'TB',
        self::TiB => 'TiB',

        self::PB => 'PB',
        self::PiB => 'PiB',

        self::EB => 'EB',
        self::EiB => 'EiB',

        self::ZB => 'ZB',
        self::ZiB => 'ZiB',

        self::YB => 'YB',
        self::YiB => 'YiB'
    ];

    /**
     * @var array<string, int>
     */
    public static array $abbreviations = [
        'B' => self::Byte,

        'KB' => self::KB,
        'K' => self::KiB,
        'k' => self::KiB,
        'KiB' => self::KiB,

        'MB' => self::MB,
        'M' => self::MiB,
        'm' => self::MiB,
        'MiB' => self::MiB,

        'GB' => self::GB,
        'G' => self::GiB,
        'g' => self::GiB,
        'GiB' => self::GiB,

        'TB' => self::TB,
        'TiB' => self::TiB,

        'PB' => self::PB,
        'PiB' => self::PiB,

        'EB' => self::EB,
        'EiB' => self::EiB,

        'ZB' => self::ZB,
        'ZiB' => self::ZiB,

        'YB' => self::YB,
        'YiB' => self::YiB
    ];

    private float $size;
    private int $unit;
    private string $suffix;

    /**
     * DataSize constructor.
     *
     * @param int $size The data size in bytes.
     * @param int $unit The unit which is used to calculate the data size.
     *
     * @throws \InvalidArgumentException If the given unit is not valid or the arguments are not of the type int.
     */
    public function __construct(int $size, int $unit)
    {
        if (!isset(self::$suffix_map[$unit])) {
            throw new \InvalidArgumentException('The given data size unit is not valid, please check the provided class constants of the DataSize class.');
        }

        $this->size = (float) $size / $unit; //the div operation can return int and float
        $this->unit = $unit;
        $this->suffix = self::$suffix_map[$unit];
    }

    /**
     * The calculated data size.
     *
     * @since 5.3
     */
    public function getSize(): float
    {
        return $this->size;
    }

    /**
     * The unit which equals the class constant used to calculate the data size. (self::GiB, ...)
     *
     * @since 5.3
     */
    public function getUnit(): int
    {
        return $this->unit;
    }

    /**
     * Get the size in bytes.
     */
    public function inBytes(): float
    {
        return $this->size * $this->unit;
    }

    /**
     * Returns the data size in a human readable manner.
     *
     * Example output:
     * 950 B
     * 3922 GB
     */
    public function __toString(): string
    {
        $size = $this->inBytes();
        // can be switched to match in ILIAS 9
        switch (true) {
            case $size > self::SIZE_FACTOR * self::SIZE_FACTOR * self::SIZE_FACTOR * self::SIZE_FACTOR:
                $unit = DataSize::TB;
                break;
            case $size > self::SIZE_FACTOR * self::SIZE_FACTOR * self::SIZE_FACTOR:
                $unit = DataSize::GB;
                break;
            case $size > self::SIZE_FACTOR * self::SIZE_FACTOR:
                $unit = DataSize::MB;
                break;
            case $size > self::SIZE_FACTOR:
                $unit = DataSize::KB;
                break;
            default:
                $unit = DataSize::Byte;
                break;
        }

        $size = round((float) $size / (float) $unit, 2);

        return "$size " . self::$suffix_map[$unit];
    }
}
