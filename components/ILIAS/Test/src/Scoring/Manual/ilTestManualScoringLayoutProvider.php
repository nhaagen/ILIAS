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

use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MainBarModification;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MetaBarModification;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\GlobalScreen\Scope\Layout\Factory\BreadCrumbsModification;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\GlobalScreen\Scope\Layout\Factory\ContentModification;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\GlobalScreen\ScreenContext\AdditionalData\Collection;
use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart\PagePartProvider;
use ILIAS\GlobalScreen\Scope\Layout\Builder\StandardPageBuilder;
use ILIAS\GlobalScreen\Scope\Layout\Factory\PageBuilderModification;
use ILIAS\UI\Component\Layout\Page\Page;
use ILIAS\Data\URI;
use ILIAS\Test\Scoring\Manual\ManualScoringPlayer;

class ilTestManualScoringLayoutProvider extends AbstractModificationProvider implements ModificationProvider
{
    protected ?Collection $data_collection = null;

    /**
     * @inheritdoc
     */
    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->main();
    }

    protected function isScoringModeEnabled(CalledContexts $screen_context_stack): bool
    {
        $this->data_collection = $screen_context_stack->current()->getAdditionalData();
        return $this->data_collection->is(ManualScoringPlayer::GS_DATA_SCORING_MODE, true);
    }

    public function getMainBarModification(CalledContexts $screen_context_stack): ?MainBarModification
    {
        if (!$this->isScoringModeEnabled($screen_context_stack)) {
            return null;
        }

        $controls = $this->data_collection->get(ManualScoringPlayer::GS_DATA_SCORING_CONTROLS);

        return $this->globalScreen()->layout()->factory()->mainbar()
            ->withModification(
                function (?MainBar $mainbar) use ($controls): ?MainBar {
                    $ui = $this->dic->ui();
                    if ($mainbar === null) {
                        $mainbar = $ui->factory()->mainControls()->mainbar();
                    }

                    $symbol = $ui->factory()->symbol()->icon()->standard('ques', 'Scoring', 'medium');
                    $slate = $ui->factory()->mainControls()->slate()->legacy(
                        'manual_scoring',
                        $symbol,
                        $ui->factory()->legacy(
                            $ui->renderer()->render([
                                $controls['playersettings']
                            ])
                        )
                    )
                    ->withEngaged(true);

                    $mainbar = $mainbar->withClearedEntries()
                        ->withAdditionalEntry('manual_scoring', $slate);

                    return $mainbar;
                }
            )
            ->withHighPriority();
    }


    public function getPageBuilderDecorator(CalledContexts $screen_context_stack): ?PageBuilderModification
    {
        if (!$this->isScoringModeEnabled($screen_context_stack)) {
            return null;
        }

        $controls = $this->data_collection->get(ManualScoringPlayer::GS_DATA_SCORING_CONTROLS);
        $modeinfo = $controls['modeinfo'];

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->factory->page()->withModification(
            function (PagePartProvider $parts) use ($modeinfo): Page {
                $p = new StandardPageBuilder();
                $page = $p->build($parts);
                return $page->withModeInfo($modeinfo);
            }
        )->withHighPriority();
    }

}
