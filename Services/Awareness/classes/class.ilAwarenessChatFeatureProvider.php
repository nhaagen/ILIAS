<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Awareness/classes/class.ilAwarenessFeatureProvider.php");

/**
 * Adds link to chat feature
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessChatFeatureProvider extends ilAwarenessFeatureProvider
{
	/**
	 * @var array
	 */
	protected static $user_access = array();

	/**
	 * @var int|string
	 */
	protected $pub_ref_id = 0;

	/**
	 * Boolean to indicate if the chat is enabled.
	 *
	 */
	protected $chat_enabled = false;

	/**
	 * Boolean to indicate if on screen chat is enabled.
	 */
	protected $osc_enabled = false;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		include_once 'Modules/Chatroom/classes/class.ilObjChatroom.php';
		$this->pub_ref_id = ilObjChatroom::_getPublicRefId();

		$chatSettings = new ilSetting('chatroom');
		$this->chat_enabled = $chatSettings->get('chat_enabled');
		$this->osc_enabled  = $chatSettings->get('enable_osc');

		$this->lng->loadLanguageModule('chatroom');
	}

	/**
	 * @param int $a_user_id
	 * @return bool
	 */
	protected function checkUserChatAccess($a_user_id)
	{
		if(!array_key_exists($a_user_id, self::$user_access))
		{
			self::$user_access[$a_user_id] = $GLOBALS['DIC']->rbac()->system()->checkAccessOfUser($a_user_id, 'read', $this->pub_ref_id);
		}

		return self::$user_access[$a_user_id];
	}



	/**
	 * Collect all features
	 *
	 * @param int $a_target_user target user
	 * @return ilAwarenessUserCollection collection
	 */
	public function collectFeaturesForTargetUser($a_target_user)
	{
		$coll = ilAwarenessFeatureCollection::getInstance();

		require_once 'Services/Awareness/classes/class.ilAwarenessFeature.php';
		require_once 'Services/User/classes/class.ilObjUser.php';

		if(!$this->chat_enabled)
		{
			return $coll;
		}

		if($this->checkUserChatAccess($this->getUserId()))
		{
			// this check is not really needed anymore, since the current
			// user will never be listed in the awareness tool
			if($a_target_user != $this->getUserId())
			{
				if($this->checkUserChatAccess($a_target_user))
				{
					$f = new ilAwarenessFeature();
					$f->setText($this->lng->txt('chat_invite_public_room'));
					$f->setHref('./ilias.php?baseClass=ilRepositoryGUI&amp;ref_id=' . $this->pub_ref_id . '&amp;usr_id=' . $a_target_user . '&amp;cmd=view-invitePD');
					$coll->addFeature($f);
				}
			}
		}

		if($this->osc_enabled)
		{
			// @todo: Check whether or not a user wants to receice os 
			$f = new ilAwarenessFeature();
			$f->setText($this->lng->txt('chat_osc_start_conversation'));
			$f->setHref('#');
			$f->setData(array(
				'onscreenchat-userid'   => $a_target_user,
				'onscreenchat-username' => ilObjUser::_lookupLogin($a_target_user)
			));
			$coll->addFeature($f);
		}

		return $coll;
	}
}
?>