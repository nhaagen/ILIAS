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

use ILIAS\Component\Activities;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Text;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Implementation\Component\Menu\Drilldown;

final class ilObjActivitiesOverview extends ilObject2
{
    public const TYPE = 'acts';

    private readonly Activities\StaticRepository $repo;
    private readonly DataFactory $data_factory;
    private readonly UIFactory $ui_factory;

    public function __construct(int $a_id = 0, bool $a_reference = true)
    {
        parent::__construct($a_id, $a_reference);

        global $DIC;
        $this->repo = $DIC['activities.repository'];
        $this->ui_factory = $DIC['ui.factory'];
        $this->data_factory = $DIC[DataFactory::class];
        $this->lng = $DIC['lng'];
        $this->lng->loadLanguageModule('acts');
    }

    protected function initType(): void
    {
        $this->type = self::TYPE;
    }

    public function getReadme(): Text\Markdown
    {
        return $this->data_factory->text()->markdown(
            file_get_contents(__DIR__ . '/../../src/Activities/README.md')
        );
    }

    public function getActivitiesDrilldown(\Closure $link_builder): Drilldown
    {
        $items = $this->buildTree(
            iterator_to_array(
                $this->repo->getActivitiesByName("%.*%")
            )
        );

        return $this->ui_factory->menu()->drilldown(
            $this->lng->txt('activities'),
            $this->toDrilldown($items, $link_builder)
        );
    }

    private function buildTree(array $activities): array
    {
        $tree = [];
        foreach ($activities as $path => $activity) {
            $parts = array_slice(explode('\\', $path), 1);

            $current = &$tree;
            foreach ($parts as $index => $part) {
                if ($part === 'Activities') {
                    continue;
                }
                if ($index === count($parts) - 1) {
                    $current[$part] = $activity;
                } else {
                    if (!isset($current[$part]) || !is_array($current[$part])) {
                        $current[$part] = [];
                    }
                    $current = &$current[$part];
                }
            }
        }
        return $tree;
    }

    private function toDrilldown($node, \Closure $link_builder): array
    {
        $result = [];
        foreach ($node as $title => $value) {
            if (!is_array($value)) {
                $icon = $this->ui_factory->symbol()->icon()->standard('', '')
                    ->withSize('medium')
                    ->withAbbreviation(
                        $value->getType() === Activities\ActivityType::Command ? '!' : '?'
                    );
                $uri = $link_builder($value);
                $name_parts = explode('\\', (string) $value->getName());
                $label = array_pop($name_parts);
                $result[] = $this->ui_factory->link()->bulky($icon, $label, $uri);
                continue;
            }
            $children = $this->toDrilldown($value, $link_builder);
            $result[] = $this->ui_factory->menu()->sub($title, $children);
        }
        return $result;
    }

}
