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

namespace ILIAS\Component\Resource;

class Plugin
{
    public function __construct(
        protected string $slot,
        protected string $name,
        protected string $id,
        protected string $version = '0.1',
        protected bool $supports_export = false,
        protected bool $supports_cli_setup = false,
        protected bool $supports_lp = false,
        protected string $il_min = '11.0',
        protected string $il_max = '11.999',
        protected string $responsible = '',
        protected string $responsible_mail = '',
    ) {
    }

    public function toArtifactData(array $component_info): array
    {

        $cinfo = [];
        foreach ($component_info as $id => $info) {
            list($_, $component_name, $slots) = $info;
            if ($slots === []) {
                continue;
            }
            foreach ($slots as $slot) {
                list($_, $slot_name) = $slot;
                $cinfo[$slot_name] = $component_name;
            }
        }
        $component = $cinfo[$this->slot];

        return [
            $this->id =>
             [
                $this->getType(),
                //$this->getComponent(),
                $component,
                $this->slot,
                $this->name,
                $this->version,
                $this->il_min,
                $this->il_max,
                $this->responsible,
                $this->responsible_mail,
                $this->supports_lp,
                $this->supports_export,
                $this->supports_cli_setup,
             ]
        ];
    }

    //for legacy reasons:
    public function getType(): string
    {
        switch ($this->slot) {
            case 'RepositoryObject':
            case 'PageComponent':
            case 'CronHook':
            case 'UserInterfaceHook':
            case 'UDFDefinition':
            case 'EventHook':
                return 'Services';
            default:
                return 'Component';
        }
    }
}
