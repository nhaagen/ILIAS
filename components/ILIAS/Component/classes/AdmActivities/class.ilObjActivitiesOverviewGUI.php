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

use ILIAS\DI\Container;
use ILIAS\Component\Activities;
use ILIAS\UI\Implementation\Component as UIComponent;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Component\Dependencies\Name;
use ILIAS\GlobalScreen\ScreenContext\BasicScreenContext;

/**
 * @ilCtrl_isCalledBy ilObjActivitiesOverviewGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjActivitiesOverviewGUI: ilFooterGroupsGUI
 */
final class ilObjActivitiesOverviewGUI extends ilObject2GUI implements DataRetrieval
{
    public const CMD_DEFAULT = 'view';
    public const CMD_README = 'readme';
    public const CMD_TABLE = 'table';
    public const CMD_SINGLE = 'single';
    public const CMD_DETAILS = 'details';

    private readonly URLBuilder $url_builder;
    private readonly URLBuilderToken $action_token;
    private readonly URLBuilderToken $row_id_token;
    private readonly DataFactory $data_factory;
    private readonly Activities\StaticRepository $repo;

    public function __construct()
    {
        $this->type = ilObjActivitiesOverview::TYPE;

        global $DIC;
        $this->refinery = $DIC['refinery'];
        $this->request_wrapper = $DIC['http']->wrapper()->query();

        $ref_id = $this->queryRefId();
        parent::__construct($ref_id);

        $this->data_factory = $DIC[DataFactory::class];
        $this->repo = $DIC['activities.repository'];
        $this->object = \ilObjectFactory::getInstanceByRefId($ref_id);

        [
            $this->url_builder,
            $this->action_token,
            $this->row_id_token,
        ] = $this->initURLBuilder();

        $this->setGlobalScreenContext(
            $DIC->globalScreen()->tool()->context()->current()
        );
    }

    #[\Override]
    public function executeCommand(): void
    {
        $cmd = $this->queryCmd();
        switch ($cmd) {
            case self::CMD_DEFAULT:
            case self::CMD_README:
                $this->tabs_gui->activateTab($cmd);
                $content = $this->readme();
                break;

            case self::CMD_TABLE:
                $this->tabs_gui->activateTab($cmd);
                $content = $this->overviewTable();
                break;

            case self::CMD_SINGLE:
                $activity = $this->queryActitivity();
                $content = $this->activityPanel($activity);
                break;

            case self::CMD_DETAILS:
                $activity = $this->queryActitivity();
                echo($this->ui_renderer->renderAsync(
                    $this->activityPromptState($activity)
                ));
                exit();

            default:
                throw new Exception(__METHOD__ . " :: Unknown command " . $cmd);
        }

        $this->prepareOutput();
        $this->tpl->setContent($this->ui_renderer->render($content));
    }


    private function queryRefId(): ?int
    {
        return $this->request_wrapper->retrieve(
            'ref_id',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->int(),
                $this->refinery->always(null)
            ])
        );
    }

    private function queryCmd(): string
    {
        return $this->request_wrapper->retrieve(
            $this->action_token->getName(),
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always($this->ctrl->getCmd(self::CMD_DEFAULT))
            ])
        );
    }

    private function queryActitivity(): Activities\Activity
    {
        $activity_name = $this->request_wrapper->retrieve(
            $this->row_id_token->getName(),
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->string()
                ),
                $this->refinery->always(null)
            ])
        );

        if ($activity_name === null) {
            throw new \InvalidArgumentException('This call requires an activity name.');
        }

        $pattern = '/' . preg_quote(urldecode(array_shift($activity_name)), '/') . '/';
        return $this->getActivities($pattern)->current();
    }

    private function initURLBuilder(): array
    {
        $here_uri = $this->data_factory->uri($this->request->getUri()->__toString());
        $url_builder = new URLBuilder($here_uri);
        $namespace = ['adm', 'acts', 'overview'];
        return $url_builder->acquireParameters(
            $namespace,
            'action',
            'act_name'
        );
    }

    private function setGlobalScreenContext(BasicScreenContext $current_context): void
    {
        $current_context->addAdditionalData(
            Activities\ActivitiesToolsProvider::SHOW_ACTIVITY_DRILLDOWN,
            true
        );

        $current_context->addAdditionalData(
            Activities\ActivitiesToolsProvider::URLBUILDER,
            [
                $this->url_builder,
                $this->action_token,
                $this->row_id_token,
                fn(Name $name) => $this->toRowId($name)
            ]
        );
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAdminTabs(): void
    {
        $this->tabs_gui->addTab(
            self::CMD_README,
            $this->lng->txt('readme'),
            $this->ctrl->getLinkTarget($this, self::CMD_README)
        );
        $this->tabs_gui->addTab(
            self::CMD_TABLE,
            $this->lng->txt('table'),
            $this->ctrl->getLinkTarget($this, self::CMD_TABLE)
        );
    }

    private function readme(): UIComponent\Legacy\Content
    {
        return  $this->ui_factory->legacy()->content(
            (string) $this->object->getReadme()->toHTML()
        );
    }

    private function activityPanel(Activities\Activity $activity): array //UIComponent\Panel\Panel
    {
        $name_parts = explode('\\', (string) $activity->getName());
        $title = sprintf(
            '%s (%s)',
            array_pop($name_parts),
            $activity->getType()->value
        );
        $description = $this->ui_factory->legacy()->content(
            (string) $activity->getDescription()->toHTML()
        );
        $prompt = $this->activityPrompt($activity);

        $tryit = $this->ui_factory->button()->shy(
            $this->lng->txt(self::CMD_DETAILS),
            $prompt->getShowSignal()
        );

        $panel = $this->ui_factory->panel()->standard(
            $title,
            $description
        )->withActions(
            $this->ui_factory->dropdown()->standard([$tryit])
        );
        return [$panel, $prompt];
    }

    private function overviewTable(): UIComponent\Table\Data
    {
        return $this->ui_factory->table()->data(
            $this,
            $this->lng->txt('activities_overview'),
            $this->getTableColumns()
        )
        ->withId('adm_act_overview')
        ->withActions($this->getTableActions())
        ->withRequest($this->request);
    }

    private function getActivities(?string $pattern = "%.*%"): Generator
    {
        return $this->repo->getActivitiesByName($pattern);
    }

    private function splitActivityName(string $canonical_name): array
    {
        $parts = explode('\\', $canonical_name);
        $component = $parts[1];
        $name = implode('\\', array_slice($parts, 3));
        return [$component, $name];
    }

    private function toRowId(Name $name): string
    {
        return urlencode((string) $name);
    }

    private function getTableColumns(): array
    {
        $f = $this->ui_factory->table()->column();
        return [
           'component' => $f->text($this->lng->txt('component')),
           'type' => $f->text($this->lng->txt('type')),
           'name' => $f->text($this->lng->txt('name')),
           'description' => $f->text($this->lng->txt('description')),
        ];
    }

    private function getTableActions(): array
    {
        return [
            self::CMD_DETAILS => $this->ui_factory->table()->action()->single(
                $this->lng->txt(self::CMD_DETAILS),
                $this->ui_factory->prompt()->standard(
                    $this->url_builder->withParameter($this->action_token, self::CMD_DETAILS)
                ),
                $this->row_id_token
            ),
        ];
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): \Generator {
        foreach ($this->getActivities() as $activity) {
            $row_id = $this->toRowId($activity->getName());
            $name_parts = explode('\\', (string) $activity->getName());
            $name = array_pop($name_parts);
            $component = $name_parts[1];
            $record = [
                'component' => $component,
                'type' => $activity->getType()->value,
                'name' => $name,
                'description' => (string) $activity->getDescription()->toPlainText(), //TODO: shorten to some max value...
            ];
            yield $row_builder->buildDataRow($row_id, $record);
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count(iterator_to_array($this->getActivities()));
    }

    private function activityPrompt(Activities\Activity $activity): UIComponent\Prompt\Prompt
    {
        return $this->ui_factory->prompt()->standard(
            $this->url_builder
                ->withParameter($this->action_token, self::CMD_DETAILS)
                ->withParameter($this->row_id_token, $this->toRowId($activity->getName()))
        );
    }

    private function activityPromptState(Activities\Activity $activity): UIComponent\Prompt\State\State
    {
        $uri = (string) $this->url_builder
            ->withParameter($this->action_token, self::CMD_DETAILS)
            ->withParameter($this->row_id_token, $this->toRowId($activity->getName()))
            ->buildURI();

        $shift_transform = $this->refinery->custom()->transformation(
            fn($v) => array_shift($v)
        );
        $inputs = [
            $this->ui_factory->input()->field()->section(
                [$activity->getInputDescription()],
                sprintf('%s (%s)', (string) $activity->getName(), $activity->getType()->value),
                (string) $activity->getDescription()->toHTML()
            )
            ->withAdditionalTransformation($shift_transform),
            $this->ui_factory->input()->field()->section(
                [
                    $this->ui_factory->input()->field()->checkbox(
                        $this->lng->txt('actually_perform'),
                        $this->lng->txt('actually_perform_desc')
                    )
                ],
                $this->lng->txt('section_actually_perform'),
                $this->lng->txt('section_actually_perform_desc')
            )
            ->withAdditionalTransformation($shift_transform),

        ];
        $form = $this->ui_factory->input()->container()->form()->standard($uri, $inputs);

        if ($this->request->getMethod() === "POST") {
            $form = $form
                ->withAdditionalTransformation(
                    $this->refinery->custom()->transformation(
                        fn($v) => array_combine(['act', 'perform'], $v)
                    )
                )
                ->withRequest($this->request);
            $data = $form->getData();

            if ($data !== null) {
                $inputs[] = $this->ui_factory->input()->field()->section(
                    [$this->ui_factory->input()->field()->hidden()],
                    $this->lng->txt('output'),
                    sprintf(
                        '<pre>%s</pre>',
                        print_r(
                            $data['perform'] === true
                                ? $this->perform($activity, $data['act'])
                                : $data['act'],
                            true
                        )
                    )
                );
            }
            $form = $this->ui_factory->input()->container()->form()->standard($uri, $inputs);

            $form = $form->withRequest($this->request);
        }

        return $this->ui_factory->prompt()->state()->show($form);
    }

    private function perform(Activities\Activity $activity, mixed $parameters)
    {
        try {
            return $activity->perform($parameters);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}
