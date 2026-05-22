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

namespace CaT\CourseCreation\Activities;

use ILIAS\Component\Dependencies\Name;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\Data\Result;
use ILIAS\Data\Text;
use ILIAS\Data\UserId;
use ILIAS\Data\Description;
use ILIAS\Component\Activities\Command;
use ILIAS\Component\Activities\ObjectActivity;
use ILIAS\Component\Activities\ObjectActivityTrait;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\AccessControl\RBACAccess;

class CreateCourseDirectlyFromTemplate extends Command implements ObjectActivity
{
    use ObjectActivityTrait;

    private const TARGET_TYPE = 'Course';

    public function __construct(
        protected DataFactory $data_factory,
        protected Refinery $refinery,
        protected RBACAccess $access
    ) {
    }

    public function getTargetType(): string
    {
        return self::TARGET_TYPE;
    }

    public function getDescription(): Text\SimpleDocumentMarkdown
    {
        return $this->data_factory->text()->markdown()->simpleDocument(
            'Create a Course directly from template.'
        );
    }

    public function getInputDescription(FieldFactory $f): FormInput
    {
        return $f->group([
            $f->text('source_ref_id')
                ->withDedicatedName('id')
                ->withAdditionalTransformation(
                    $this->refinery->kindlyTo()->int()
                ),
            $f->text('target_category_ref_id')
                ->withAdditionalTransformation(
                    $this->refinery->kindlyTo()->int()
                ),
            ]);
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        $md = fn(string $markdown) => $this->data_factory->text()->markdown()->simpleDocument($markdown);

        return $f->int(
            $md('The ref_id of the newly created course')
        );
    }

    public function isAllowedToPerform(UserId $usr_id, mixed $parameters): bool
    {
        [$source_template_ref_id, $target_category_ref_id] = $parameters;
        return $this->access->checkAccess('write', $source_template_ref_id)
            && $this->access->checkAccess('write', $target_category_ref_id);

    }

    public function perform(mixed $parameters): string
    {
        try {


            return 'created course from template';


        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
