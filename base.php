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
        if($installed) {
            $f3->set("plugins.groupsprints.installed", true, 3600*24);
        }
        return $installed;
    }
}
