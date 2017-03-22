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
        $this->_hook("render.sprint_new.before_submit", array($this, "groupSelect"));
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
    public function groupSelect() {
        if ($this->_installed()) {
            $f3 = \Base::instance();

            $group = new \Model\User();
            $groups = $group->find("deleted_date IS NULL AND role = 'group'");

            $group_array = array();
            $db = $f3->get("db.instance");
            foreach($groups as $g) {
                $db->exec("SELECT g.id FROM user_group g JOIN user u ON g.user_id = u.id WHERE g.group_id = ? AND u.deleted_date IS NULL", $g["id"]);
                $count = $db->count();
                $group_array[] = array(
                    "id" => $g["id"],
                    "name" => $g["name"],
                    "task_color" => $g["task_color"],
                    "count" => $count
                );
            }
            $f3->set("groups", $group_array);
            echo \Template::instance()->render("groupsprints/view/sprint_new.html");
        }
    }
}
