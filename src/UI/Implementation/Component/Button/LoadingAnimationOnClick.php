<?php declare(strict_types=1);

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
 
namespace ILIAS\UI\Implementation\Component\Button;

/**
 * Implements LoadingAnimationOnClick interface
 * @author killing@leifos.de
 */
trait LoadingAnimationOnClick
{
    protected bool $loading_animation_on_click = false;

    /**
     * @inheritdoc
     * @return static
     */
    public function withLoadingAnimationOnClick(bool $loading_animation_on_click = true)
    {
        $clone = clone $this;
        $clone->loading_animation_on_click = $loading_animation_on_click;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function hasLoadingAnimationOnClick() : bool
    {
        return $this->loading_animation_on_click;
    }
}
