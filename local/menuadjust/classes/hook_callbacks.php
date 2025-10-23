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

namespace local_menuadjust;

use core\hook\navigation\primary_extend;

/**
 * Hook callback handlers for local_menuadjust.
 */
class hook_callbacks {
    /**
     * Extend the primary navigation using the hook API.
     *
     * @param primary_extend $hook Hook payload from Moodle core.
     * @return void
     */
    public static function extend_primary_navigation(primary_extend $hook): void {
        $primary = $hook->get_primaryview();
        foreach (menu_builder::get_menu_entries() as $entry) {
            $key = $entry['key'] ?? null;
            $text = $entry['text'] ?? '';
            if (!$key || $text === '') {
                continue;
            }

            $existing = $primary->get($key);
            if ($existing instanceof \navigation_node) {
                $existing->remove();
            }

            $node = $primary->add(
                $text,
                $entry['url'] ?? null,
                $entry['type'] ?? \navigation_node::TYPE_CUSTOM,
                null,
                $key,
                $entry['icon'] ?? null
            );

            if (!$node instanceof \navigation_node) {
                continue;
            }

            if (!empty($entry['classes']) && is_array($entry['classes'])) {
                foreach ($entry['classes'] as $class) {
                    if ($class !== '') {
                        $node->add_class($class);
                    }
                }
            }
        }
    }
}
