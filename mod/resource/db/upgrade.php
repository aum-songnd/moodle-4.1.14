<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Resource module upgrade code
 *
 * This file keeps track of upgrades to
 * the resource module
 *
 * Sometimes, changes between versions involve
 * alterations to database structures and other
 * major things that may break installations.
 *
 * The upgrade function in this file will attempt
 * to perform all the necessary actions to upgrade
 * your older installation to the current version.
 *
 * If there's something it cannot do itself, it
 * will tell you what you need to do.
 *
 * The commands in here will all be database-neutral,
 * using the methods of database_manager class
 *
 * Please do not forget to use upgrade_set_timeout()
 * before any action that may take longer time to finish.
 *
 * @package    mod_resource
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

function xmldb_resource_upgrade($oldversion) {
    global $CFG, $DB;

    if ($oldversion < 2024051900) {
        
        $dbman = $DB->get_manager();
        $table = new xmldb_table('resource');
        $field = new xmldb_field('usedrm', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2026061100) {
        // 1. Định nghĩa bảng 'resource_video_view'
        $table = new xmldb_table('resource_video_view');

        // 2. Định nghĩa các trường thông tin (Fields)
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('cm_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('currenttime', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('duration', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('percent', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('mapa', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // 3. Định nghĩa Khóa chính (Primary Key)
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // 4. Định nghĩa Chỉ mục (Index)
        $table->add_index('cm_user', XMLDB_INDEX_NOTUNIQUE, ['cm_id', 'user_id']);
        $dbman = $DB->get_manager();
        // Dòng 83 gây lỗi lúc trước - Giờ $dbman đã được định nghĩa ở đầu hàm nên sẽ chạy tốt
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Khai báo nâng cấp thành công
        upgrade_mod_savepoint(true, 2026061100, 'resource');
    }
    
    if ($oldversion < 2026062900) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('resource_video_view');

        // Thêm cột timefinished để lưu thời điểm kết thúc phiên xem
        $field = new xmldb_field('timefinished', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timemodified');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2026062900, 'resource');
    }

    if ($oldversion < 2026063000) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('resource');

        $field = new xmldb_field('completionpercent', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'usedrm');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2026063000, 'resource');
    }

    if ($oldversion < 2026070100) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('resource_video_view');
        $field = new xmldb_field('timefinished');

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2026070100, 'resource');
    }

    if ($oldversion < 2026071000) {
        // Đăng ký thêm web service cho Moodle mobile (get/save video progress).
        upgrade_mod_savepoint(true, 2026071000, 'resource');
    }

    return true;
}