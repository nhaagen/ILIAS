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
use ILIAS\Data\UserId;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Component\Dependencies\Name;
use ILIAS\GlobalScreen\ScreenContext\BasicScreenContext;
use ILIAS\Data\Description;

/**
 * @ilCtrl_isCalledBy ilObjActivitiesOverviewGUI: ilAdministrationGUI
 */
final class ilObjActivitiesOverviewGUI extends ilObject2GUI implements DataRetrieval
{
    public const CMD_DEFAULT = 'view';
    public const CMD_README = 'readme';
    public const CMD_TABLE = 'table';
    public const CMD_SINGLE = 'single';

    private readonly URLBuilder $url_builder;
    private readonly URLBuilderToken $action_token;
    private readonly URLBuilderToken $row_id_token;
    private readonly DataFactory $data_factory;
    private readonly Activities\StaticRepository $repo;
    private readonly UserId $current_usr_id;

    public function __construct()
    {
        $this->type = ilObjActivitiesOverview::TYPE;

        global $DIC;
        $this->refinery = $DIC['refinery'];
        $this->request_wrapper = $DIC['http']->wrapper()->query();

        $ref_id = $this->queryRefId();
        parent::__construct($ref_id);

        $this->data_factory = $DIC[DataFactory::class];
        $this->current_usr_id = $this->data_factory->userId($DIC['ilUser']->getId());

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
                $this->addSingleActivityTab();
                $content = $this->activityPanel($activity);
                break;

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

    private function addSingleActivityTab(): void
    {
        $this->tabs_gui->addTab(
            self::CMD_SINGLE,
            $this->lng->txt('act'),
            '#'
        );
        $this->tabs_gui->activateTab(self::CMD_SINGLE);
    }

    private function readme(): UIComponent\Legacy\Legacy
    {
        return  $this->ui_factory->legacy(
            (string) $this->object->getReadme()->toHTML()
        );
    }

    private function activityPanel(Activities\Activity $activity): UIComponent\Panel\Panel
    {
        $title = sprintf(
            '%s (%s)',
            (string) $activity->getName(),
            $activity->getType()->value
        );

        $form = $this->getInputForm($activity);

        $data = null;
        if ($this->request->getMethod() === "POST") {
            $form = $form->withRequest($this->request);
            $data = $form->getData();
        }

        $legacy = fn(ILIAS\Data\Text\HTML $html): UIComponent\Legacy\Legacy
            => $this->ui_factory->legacy((string) $html);

        $content = [
            $this->ui_factory->panel()->sub(
                $this->lng->txt('description'),
                $legacy($activity->getDescription()->toHTML())
            ),
            $this->ui_factory->panel()->sub(
                $this->lng->txt('output_description'),
                $this->descriptionToTree(
                    $activity->getOutputDescription($this->data_factory->description())
                )
            ),
            $this->ui_factory->panel()->sub(
                $this->lng->txt('input_description'),
                $form
            ),
        ];

        if ($data !== null) {
            $content[] = $this->ui_factory->panel()->sub(
                $this->lng->txt('output'),
                $this->ui_factory->legacy(
                    sprintf(
                        '<pre>%s</pre>',
                        print_r(
                            $data['perform'] === true
                                ? $this->perform($activity, $data['act'])
                                : $data['act'],
                            true
                        )
                    )
                )
            );
        }
        return $this->ui_factory->panel()->standard($title, $content);
    }

    private function descriptionToTree(
        \ILIAS\Data\Description\Description $description
    ): UIComponent\Tree\Expandable {

        $recursion = new class () implements \ILIAS\UI\Component\Tree\TreeRecursion {
            public function getChildren($record, $environment = null): array
            {
                if (is_array($record)) {
                    [$label, $record] = $record;
                }
                return match($record::class) {
                    Description\DList::class => [$record->getValueType()],
                    Description\DMap::class => [
                        ['Key:', $record->getKeyType()],
                        ['Value:', $record->getValueType()]
                    ],
                    Description\DObject::class => iterator_to_array($record->getFields()),
                    default => []
                };
            }

            public function build(
                \ILIAS\UI\Component\Tree\Node\Factory $factory,
                $record,
                $environment = null
            ): \ILIAS\UI\Component\Tree\Node\Node {
                $label = '';
                if (is_array($record)) {
                    [$label, $record] = $record;
                }
                return $factory->keyValue(
                    $label . '' . (string) $record->getDescription()->toHTML(),
                    match($record::class) {
                        Description\DValue::class => $record->getType()->value,
                        Description\DList::class => 'List',
                        Description\DMap::class => 'Map (Key/Value)',
                        Description\DObject::class => 'Object',
                        Description\Field::class => $record->getName() . ':' . $record->getType()->value,
                        default => $record::class
                    },
                )->withExpanded(true);
            }
        };

        return $this->ui_factory->tree()->expandable('Label', $recursion)
            ->withData([$description]);
    }

    private function getInputForm(Activities\Activity $activity): UIComponent\Input\Container\Form\Standard
    {
        $shift_transform = $this->refinery->custom()->transformation(
            fn($v) => array_shift($v)
        );

        $input_description = ($activity instanceof Activities\ObjectActivity)
            ? $activity->getCheckedInputDescription($this->ui_factory->input()->field())
            : $activity->getInputDescription($this->ui_factory->input()->field());

        $inputs = [
            $this->ui_factory->input()->field()->section(
                [$input_description],
                $this->lng->txt('section_activity_parameters'),
                $this->lng->txt('section_activity_parameters_desc'),
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

        $uri = '#';
        return $this->ui_factory->input()->container()->form()->standard($uri, $inputs)
            ->withSubmitLabel($this->lng->txt('perform'))
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    fn($v) => array_combine(['act', 'perform'], $v)
                )
            );
    }

    private function getActivities(?string $pattern = "%.*%"): Generator
    {
        return $this->repo->getActivitiesByName($pattern);
    }

    private function overviewTable(): UIComponent\Table\Data
    {
        return $this->ui_factory->table()->data(
            $this->lng->txt('activities_overview'),
            $this->getTableColumns(),
            $this,
        )
        ->withId('adm_act_overview')
        ->withRequest($this->request);
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

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
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
                'description' => (string) $activity->getDescription()->toPlainText(),
            ];
            yield $row_builder->buildDataRow($row_id, $record);
        }
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        return count(iterator_to_array($this->getActivities()));
    }

    private function perform(Activities\Activity $activity, mixed $parameters)
    {
        if (!$activity->isAllowedToPerform($this->current_usr_id, $parameters)) {
            return $this->lng->txt('not_allowed_to_perform_activity');
        }
        try {
            return $activity->perform($parameters);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}
