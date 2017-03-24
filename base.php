<?php
/**
 * @package groupsprints
 * @author Sam Allen <allensam86@gmail.com>
 * @version 0.0.1
 */

namespace Plugin\Groupsprints;

class Base extends \Plugin {

    /**
     * Initialize plugin
     */
    public function _load() {
        $this->_hook("render.sprints.theadrow_after", array($this, "groupColumnHeading"));
        $this->_hook("render.sprints.tablerow_after", array($this, "groupColumn"));
        $this->_hook("render.sprint_new.before_submit", array($this, "groupSelect"));
        $this->_hook("render.sprint_edit.before_submit", array($this, "groupSelect"));
        $this->_hook("model/sprint.after_save", array($this, "saveSprintGroup"));
    }

    /**
     * Install plugin (add database tables)
     */
    public function _install() {
        $f3 = \Base::instance();
        $db = $f3->get("db.instance");
        $install_db = file_get_contents(__DIR__ . "/db/database.sql");
        $db->exec(explode(";", $install_db));
    }

    /**
     * Check if plugin is installed
     * @return bool
     */
    public function _installed() {
        $f3 = \Base::instance();
        if($f3->get("plugins.groupsprints.installed")) {
            return true;
        }
        $db = $f3->get("db.instance");
        $q = $db->exec("SHOW TABLES LIKE 'sprint_group'");
        $installed = !!$db->count();
        $f3 = \Base::instance();
        if($installed) {
            $f3->set("plugins.groupsprints.installed", true, 3600*24);
        }
        return $installed;
    }

    /**
     * Output group select input on new sprint form
     * @return [type] [description]
     */
    public function groupSelect($sprint = null) {
        if ($this->_installed()) {
            $f3 = \Base::instance();

            $group = new \Model\User();
            $groups = $group->find("deleted_date IS NULL AND role = 'group'");

            $groupArray = array();
            $db = $f3->get("db.instance");
            foreach($groups as $g) {
                $db->exec("SELECT g.id FROM user_group g JOIN user u ON g.user_id = u.id WHERE g.group_id = ? AND u.deleted_date IS NULL", $g["id"]);
                $count = $db->count();
                $groupArray[] = array(
                    "id" => $g["id"],
                    "name" => $g["name"],
                    "task_color" => $g["task_color"],
                    "count" => $count
                );
            }
            $f3->set("groups", $groupArray);

            if ($sprint) {
                $sprintGroup = new Model\Group;
                $sprintGroup->load(array("sprint_id = ?", $sprint->id));
                $f3->set("sprintgroup", $sprintGroup);
            }

            echo \Template::instance()->render("groupsprints/view/group_select.html");
        }
    }

    /**
     * Update or add sprint group on sprint save
     * @param  $sprint
     */
    public function saveSprintGroup($sprint) {
        $f3 = \Base::instance();
        $post = $f3->get("POST");

        $sprintGroup = new Model\Group;
        $sprintGroupCollection = $sprintGroup->find(array("sprint_id = ?", $sprint->id));

        if (count($sprintGroupCollection)) {
            foreach ($sprintGroupCollection as $sprintGroup) {
                $sprintGroup->sprint_id = $sprint->id;
                $sprintGroup->group_id = $post["sprint_group"];
                $sprintGroup->save();
            }
            return;
        }

        $sprintGroup->sprint_id = $sprint->id;
        $sprintGroup->group_id = $post["sprint_group"];
        $sprintGroup->save();
    }

    /**
     * Add group heading to sprints table
     */
    public function groupColumnHeading() {
        if ($this->_installed()) {
            echo "<th>Group</th>";
        }
    }

    /**
     * Add group name to each sprint on sprints table
     * @param  $sprint
     */
    public function groupColumn($sprint) {
        if ($this->_installed()) {
            $f3 = \Base::instance();

            $sprintGroup = new Model\Group;
            $sprintGroup->load(array("sprint_id = ?", $sprint->id));

            $group = new \Model\User();
            $group->load($sprintGroup->group_id);

            echo "<td>" . $group->name . "</td>";
        }
    }
}
