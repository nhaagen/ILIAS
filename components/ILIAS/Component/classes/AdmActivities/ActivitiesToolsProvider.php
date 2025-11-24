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

namespace ILIAS\Component\Activities;

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\Tree\Tree;
use ILIAS\UI\Component\Tree\TreeRecursion;
use ilObjOrgUnit;
use ilObjOrgUnitGUI;
use ilOrgUnitExplorerGUI;
use ilOrgUnitExtension;
use ilTree;

class ActivitiesToolsProvider extends AbstractDynamicToolProvider
{
    public const SHOW_ACTIVITY_DRILLDOWN = 'show_activities_dd';
    public const URLBUILDER = 'show_activities_urlbuilder';

    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->main()->administration();
    }

    /**
     * @return \ILIAS\GlobalScreen\Scope\Tool\Factory\Tool[]
     */
    public function getToolsForContextStack(CalledContexts $called_contexts): array
    {
        if (! $called_contexts->current()->getAdditionalData()->is(self::SHOW_ACTIVITY_DRILLDOWN, true)) {
            return [];
        }

        $ref_id = $called_contexts->current()->getReferenceId()->toInt();
        $activities = \ilObjectFactory::getInstanceByRefId($ref_id);
        $indentifier = $this->identification_provider->contextAwareIdentifier('activities_dd');

        [
            $url_builder,
            $action_token,
            $row_id_token,
            $id_builder
        ] = $called_contexts->current()->getAdditionalData()->get(self::URLBUILDER);

        $link_builder = fn($activity) =>
            $url_builder
                ->withParameter($action_token, \ilObjActivitiesOverviewGUI::CMD_SINGLE)
                ->withParameter($row_id_token, $id_builder($activity->getName()))
                ->buildURI();


        $tool = $this->factory->drilldownTool($indentifier)
             ->withTitle($this->dic->language()->txt('activities'))
             ->withSymbol($this->dic->ui()->factory()->symbol()->icon()->standard(
                 'acts',
                 $this->dic->language()->txt('activities')
             ))
             ->withContent($activities->getActivitiesDrilldown($link_builder));

        return [$tool];
    }

}
