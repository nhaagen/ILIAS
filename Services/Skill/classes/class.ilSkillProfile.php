<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Skill profile
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillProfile implements ilSkillUsageInfo
{
    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $id;
    protected $title;
    protected $description;
    protected $skill_level = array();
    
    /**
     * Constructor
     *
     * @param int $a_id profile id
     */
    public function __construct($a_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        if ($a_id > 0) {
            $this->setId($a_id);
            $this->read();
        }
    }
    
    /**
     * Set id
     *
     * @param int $a_val id
     */
    public function setId($a_val)
    {
        $this->id = $a_val;
    }
    
    /**
     * Get id
     *
     * @return int id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set title
     *
     * @param string $a_val title
     */
    public function setTitle($a_val)
    {
        $this->title = $a_val;
    }
    
    /**
     * Get title
     *
     * @return string title
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Set description
     *
     * @param string $a_val description
     */
    public function setDescription($a_val)
    {
        $this->description = $a_val;
    }
    
    /**
     * Get description
     *
     * @return string description
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Add skill level
     *
     * @param
     * @return
     */
    public function addSkillLevel($a_base_skill_id, $a_tref_id, $a_level_id)
    {
        //echo "-".$a_base_skill_id."-";
        $this->skill_level[] = array(
            "base_skill_id" => $a_base_skill_id,
            "tref_id" => $a_tref_id,
            "level_id" => $a_level_id
            );
    }
    
    /**
     * Remove skill level
     *
     * @param
     * @return
     */
    public function removeSkillLevel($a_base_skill_id, $a_tref_id, $a_level_id)
    {
        foreach ($this->skill_level as $k => $sl) {
            if ((int) $sl["base_skill_id"] == (int) $a_base_skill_id &&
                (int) $sl["tref_id"] == (int) $a_tref_id &&
                (int) $sl["level_id"] == (int) $a_level_id) {
                unset($this->skill_level[$k]);
            }
        }
    }

    /**
     * Get skill levels
     *
     * @param
     * @return
     */
    public function getSkillLevels()
    {
        return $this->skill_level;
    }
    
    /**
     * Read skill profile from db
     *
     * @param
     * @return
     */
    public function read()
    {
        $ilDB = $this->db;
        
        $set = $ilDB->query(
            "SELECT * FROM skl_profile " .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer")
            );
        $rec = $ilDB->fetchAssoc($set);
        $this->setTitle($rec["title"]);
        $this->setDescription($rec["description"]);
        
        $set = $ilDB->query(
            "SELECT * FROM skl_profile_level " .
            " WHERE profile_id = " . $ilDB->quote($this->getId(), "integer")
            );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->addSkillLevel(
                (int) $rec["base_skill_id"],
                (int) $rec["tref_id"],
                (int) $rec["level_id"]
            );
        }
    }
    
    /**
     * Create skill profile
     */
    public function create()
    {
        $ilDB = $this->db;
        
        // profile
        $this->setId($ilDB->nextId("skl_profile"));
        $ilDB->manipulate("INSERT INTO skl_profile " .
            "(id, title, description) VALUES (" .
            $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote($this->getTitle(), "text") . "," .
            $ilDB->quote($this->getDescription(), "text") .
            ")");
        
        // profile levels
        foreach ($this->skill_level as $level) {
            $ilDB->replace(
                "skl_profile_level",
                array("profile_id" => array("integer", $this->getId()),
                    "tref_id" => array("integer", (int) $level["tref_id"]),
                    "base_skill_id" => array("integer", (int) $level["base_skill_id"])
                    ),
                array("level_id" => array("integer", (int) $level["level_id"]))
                );
        }
    }
    
    /**
     * Update skill profile
     */
    public function update()
    {
        $ilDB = $this->db;
        
        // profile
        $ilDB->manipulate(
            "UPDATE skl_profile SET " .
            " title = " . $ilDB->quote($this->getTitle(), "text") . "," .
            " description = " . $ilDB->quote($this->getDescription(), "text") .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer")
            );
        
        // profile levels
        $ilDB->manipulate(
            "DELETE FROM skl_profile_level WHERE " .
            " profile_id = " . $ilDB->quote($this->getId(), "integer")
            );
        foreach ($this->skill_level as $level) {
            $ilDB->replace(
                "skl_profile_level",
                array("profile_id" => array("integer", $this->getId()),
                    "tref_id" => array("integer", (int) $level["tref_id"]),
                    "base_skill_id" => array("integer", (int) $level["base_skill_id"])
                    ),
                array("level_id" => array("integer", (int) $level["level_id"]))
                );
            
            /*$ilDB->manipulate("INSERT INTO skl_profile_level ".
                "(profile_id, base_skill_id, tref_id, level_id) VALUES (".
                $ilDB->quote($this->getId(), "integer").",".
                $ilDB->quote((int) $level["base_skill_id"], "integer").",".
                $ilDB->quote((int) $level["tref_id"], "integer").",".
                $ilDB->quote((int) $level["level_id"], "integer").
                ")");*/
        }
    }
    
    /**
     * Delete skill profile
     */
    public function delete()
    {
        $ilDB = $this->db;
        
        // profile levels
        $ilDB->manipulate(
            "DELETE FROM skl_profile_level WHERE " .
            " profile_id = " . $ilDB->quote($this->getId(), "integer")
            );
        
        // profile
        $ilDB->manipulate(
            "DELETE FROM skl_profile WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
            );
    }
    
    /**
     * Get profiles
     *
     * @param
     * @return
     */
    public static function getProfiles()
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query(
            "SELECT * FROM skl_profile " .
            " ORDER BY title "
            );
        $profiles = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $profiles[$rec["id"]] = $rec;
        }
        
        return $profiles;
    }
    
    /**
     * Lookup
     *
     * @param
     * @return
     */
    protected static function lookup($a_id, $a_field)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query(
            "SELECT " . $a_field . " FROM skl_profile " .
            " WHERE id = " . $ilDB->quote($a_id, "integer")
            );
        $rec = $ilDB->fetchAssoc($set);
        return $rec[$a_field];
    }
    
    /**
     * Lookup title
     *
     * @param
     * @return
     */
    public static function lookupTitle($a_id)
    {
        return self::lookup($a_id, "title");
    }
    
    ////
    //// Skill user assignment
    ////

    /**
     * Get all assignments (users and roles)
     */
    public function getAssignments()
    {
        $assignments = array();

        $users = $this->getAssignedUsers();
        $roles = $this->getAssignedRoles();
        $assignments = $users + $roles;
        ksort($assignments);

        return $assignments;
    }

    /**
     * Get assigned users
     */
    public function getAssignedUsers()
    {
        $ilDB = $this->db;
        $lng = $this->lng;
        
        $set = $ilDB->query(
            "SELECT * FROM skl_profile_user " .
            " WHERE profile_id = " . $ilDB->quote($this->getId(), "integer")
            );
        $users = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $name = ilUserUtil::getNamePresentation($rec["user_id"]);
            $type = $lng->txt("user");
            $users[$rec["user_id"]] = array(
                "type" => $type,
                "name" => $name,
                "id" => $rec["user_id"]
                );
        }
        return $users;
    }
    
    /**
     * Add user to profile
     *
     * @param int $a_user_id user id
     */
    public function addUserToProfile($a_user_id)
    {
        $ilDB = $this->db;
        
        $ilDB->replace(
            "skl_profile_user",
            array("profile_id" => array("integer", $this->getId()),
                "user_id" => array("integer", (int) $a_user_id),
                ),
            array()
            );
    }
    
    /**
     * Remove user from profile
     *
     * @param int $a_user_id user id
     */
    public function removeUserFromProfile($a_user_id)
    {
        $ilDB = $this->db;
        
        $ilDB->manipulate(
            "DELETE FROM skl_profile_user WHERE " .
            " profile_id = " . $ilDB->quote($this->getId(), "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer")
            );
    }

    /**
     * Remove user from all profiles
     *
     * @param int $a_user_id
     */
    public static function removeUserFromAllProfiles($a_user_id)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $ilDB->manipulate(
            "DELETE FROM skl_profile_user WHERE " .
            " user_id = " . $ilDB->quote($a_user_id, "integer")
        );
    }

    
    /**
     * Get profiles of a user
     *
     * @param int $a_user_id user id
     */
    public static function getProfilesOfUser($a_user_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $rbacreview = $DIC->rbac()->review();

        $all_profiles = array();

        // competence profiles coming from user assignments
        $user_profiles = array();
        $set = $ilDB->query(
            "SELECT p.id, p.title FROM skl_profile_user u JOIN skl_profile p " .
            " ON (u.profile_id = p.id) " .
            " WHERE user_id = " . $ilDB->quote($a_user_id, "integer") .
            " ORDER BY p.title ASC"
            );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $user_profiles[] = $rec;
        }

        // competence profiles coming from role assignments
        $role_profiles = array();
        $user_roles = $rbacreview->assignedRoles($a_user_id);
        foreach ($user_roles as $role) {
            $profiles = self::getProfilesOfRole($role);
            foreach ($profiles as $profile) {
                $role_profiles[] = $profile;
            }
        }

        // merge competence profiles and remove multiple occurrences
        $all_profiles = array_merge($user_profiles, $role_profiles);
        $temp_profiles = array();
        foreach ($all_profiles as &$v) {
            if (!isset($temp_profiles[$v["id"]])) {
                $temp_profiles[$v["id"]] = &$v;
            }
        }
        $all_profiles = array_values($temp_profiles);
        return $all_profiles;
    }

    /**
     * Get assigned users
     */
    public static function countUsers($a_profile_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query(
            "SELECT count(*) ucnt FROM skl_profile_user " .
            " WHERE profile_id = " . $ilDB->quote($a_profile_id, "integer")
            );
        $rec = $ilDB->fetchAssoc($set);
        return (int) $rec["ucnt"];
    }

    /**
     * Get assigned roles
     *
     * @return array
     */
    public function getAssignedRoles()
    {
        $ilDB = $this->db;
        $lng = $this->lng;

        $set = $ilDB->query(
            "SELECT * FROM skl_profile_role " .
            " WHERE profile_id = " . $ilDB->quote($this->getId(), "integer")
        );
        $roles = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $name = ilObjRole::_lookupTitle($rec["role_id"]);
            $type = $lng->txt("role");
            $roles[$rec["role_id"]] = array(
                "type" => $type,
                "name" => $name,
                "id" => $rec["role_id"]
            );
        }
        return $roles;
    }

    /**
     * Add role to profile
     *
     * @param int $a_role_id role id
     */
    public function addRoleToProfile(int $a_role_id)
    {
        $ilDB = $this->db;

        $ilDB->replace(
            "skl_profile_role",
            array("profile_id" => array("integer", $this->getId()),
                "role_id" => array("integer", (int) $a_role_id),
            ),
            array()
        );
    }

    /**
     * Remove role from profile
     *
     * @param int $a_role_id role id
     */
    public function removeRoleFromProfile(int $a_role_id)
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_profile_role WHERE " .
            " profile_id = " . $ilDB->quote($this->getId(), "integer") .
            " AND role_id = " . $ilDB->quote($a_role_id, "integer")
        );
    }

    /**
     * Remove role from all profiles
     *
     * @param int $a_role_id
     */
    public static function removeRoleFromAllProfiles(int $a_role_id)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $ilDB->manipulate(
            "DELETE FROM skl_profile_role WHERE " .
            " role_id = " . $ilDB->quote($a_role_id, "integer")
        );
    }

    /**
     * Get profiles of a role
     *
     * @param int $a_role_id role id
     * @return array
     */
    public static function getProfilesOfRole(int $a_role_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $profiles = array();
        $set = $ilDB->query(
            "SELECT p.id, p.title FROM skl_profile_role r JOIN skl_profile p " .
            " ON (r.profile_id = p.id) " .
            " WHERE role_id = " . $ilDB->quote($a_role_id, "integer") .
            " ORDER BY p.title ASC"
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $profiles[] = $rec;
        }
        return $profiles;
    }

    /**
     * Count assigned roles of a profile
     *
     * @param int $a_profile_id
     * @return int
     */
    public static function countRoles(int $a_profile_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT count(*) rcnt FROM skl_profile_role " .
            " WHERE profile_id = " . $ilDB->quote($a_profile_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return (int) $rec["rcnt"];
    }
    
    /**
     * Get usage info
     *
     * @param
     * @return
     */
    public static function getUsageInfo($a_cskill_ids, &$a_usages)
    {
        global $DIC;

        ilSkillUsage::getUsageInfoGeneric(
            $a_cskill_ids,
            $a_usages,
            ilSkillUsage::PROFILE,
            "skl_profile_level",
            "profile_id",
            "base_skill_id"
        );
    }
}
