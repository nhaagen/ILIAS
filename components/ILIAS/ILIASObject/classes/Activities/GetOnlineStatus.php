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
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Component\Activities;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\AccessControl\RBACAccess;

class GetOnlineStatus extends Activities\Query implements Activities\ObjectActivity
{
    use Activities\ObjectActivityTrait;

    private const TARGET_TYPE = 'ILIASObject';
    private const RETURN_ONLINE = 'online';
    private const RETURN_OFFLINE = 'offline';

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
            Get online-status of an object.
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

        return $f->string(
            $md('status is \'online\' or \'offline\'')
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

            $obj_id = \ilObject::_lookupObjId($parameters);
            if ($obj_id === 0) {
                throw new \InvalidArgumentException('no obj with ref id ' . $parameters . '.');
            }

            return \ilObject::lookupOfflineStatus($obj_id)
                ? self::RETURN_OFFLINE
                : self::RETURN_ONLINE;

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}
