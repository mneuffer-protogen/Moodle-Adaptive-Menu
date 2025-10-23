<?php
// local_menuadjust lib

defined('MOODLE_INTERNAL') || die();

/**
 * Extend the global navigation to add/adjust menu items.
 * Adds:
 * - When not logged in: a 'Courses' link
 * - When logged in: an 'Apply' button (configurable)
 * - When logged in and enrolled in configured course: 'Go to course'
 *
 * @param global_navigation $nav
 */
function local_menuadjust_extend_navigation($nav) {
	foreach (\local_menuadjust\menu_builder::get_menu_entries() as $entry) {
		$key = $entry['key'] ?? null;
		$text = $entry['text'] ?? '';
		if (!$key || $text === '') {
			continue;
		}

		$existing = method_exists($nav, 'get') ? $nav->get($key) : false;
		if ($existing instanceof navigation_node) {
			$existing->remove();
		}

		$node = null;
		if (method_exists($nav, 'add')) {
			$node = $nav->add(
				$text,
				$entry['url'] ?? null,
				$entry['type'] ?? navigation_node::TYPE_CUSTOM,
				null,
				$key,
				$entry['icon'] ?? null
			);
		} else if (method_exists($nav, 'add_node')) {
			$node = navigation_node::create($text, $entry['url'] ?? null);
			$node->key = $key;
			$node->type = $entry['type'] ?? navigation_node::TYPE_CUSTOM;
			if (!empty($entry['icon'])) {
				$node->icon = $entry['icon'];
			}
			$nav->add_node($node);
		}

		if (!$node instanceof navigation_node) {
			continue;
		}

		$node->type = $entry['type'] ?? navigation_node::TYPE_CUSTOM;
		if (!empty($entry['icon'])) {
			$node->icon = $entry['icon'];
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

