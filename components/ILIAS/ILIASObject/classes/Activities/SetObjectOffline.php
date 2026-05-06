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

namespace ILIAS\Object\Activities;

use ILIAS\Component\Dependencies\Name;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\Data\Result;
use ILIAS\Data\Text;
use ILIAS\Data\Description;
use ILIAS\Data\UserId;
use ILIAS\Component\Activities;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\AccessControl\RBACAccess;

class SetObjectOffline extends Activities\Command implements Activities\ObjectActivity
{
    use Activities\ObjectActivityTrait;

    private const TARGET_TYPE = 'ILIASObject';

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
        return $this->data_factory->text()->markdown()->simpleDocument('
            Set online-status of an object to "offline".
        ');
    }

    public function getInputDescription(FieldFactory $f): FormInput
    {
        return $f->text('ref_id')
            ->withDedicatedName('id')
            ->withAdditionalTransformation(
                $this->refinery->kindlyTo()->int()
            );
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        $md = fn(string $markdown) => $this->data_factory->text()->markdown()->simpleDocument($markdown);

        return $f->bool(
            $md('true, if successful')
        );
    }

    public function isAllowedToPerform(UserId $usr_id, mixed $parameters): bool
    {
        return $this->access->checkAccess('write', $parameters);
    }

    public function perform(mixed $parameters): mixed
    {
        try {
            assert(is_int($parameters));
            $ref_id = $parameters;

            $obj_id = \ilObject::_lookupObjId($ref_id);
            if ($obj_id === 0) {
                throw new \InvalidArgumentException('no obj with ref id ' . $ref_id . '.');
            }

            $obj = \ilObjectFactory::getInstanceByRefId($ref_id);
            $props = $obj->getObjectProperties()->getPropertyIsOnline();
            $obj->getObjectProperties()->storePropertyIsOnline(
                $props->withOffline()
            );
            return true;

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}
