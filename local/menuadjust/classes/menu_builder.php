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

use context_course;
use moodle_url;
use Throwable;

/**
 * Helper for building menu entries based on plugin configuration.
 */
class menu_builder {
    /**
     * Build the list of menu entries that should be injected.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function get_menu_entries(): array {
        global $CFG, $DB;

        $entries = [];

        $courseslabel = trim((string) get_config('local_menuadjust', 'courseslabel')) ?: get_string('courseslabel', 'local_menuadjust');
        $applylabel = trim((string) get_config('local_menuadjust', 'applylabel')) ?: get_string('applylabel', 'local_menuadjust');
        $applyurlvalue = trim((string) get_config('local_menuadjust', 'applyurl')) ?: '/login/index.php';
        $targetcourseid = (int) get_config('local_menuadjust', 'targetcourseid');
        $gotocourselabel = trim((string) get_config('local_menuadjust', 'gotocourselabel')) ?: get_string('gotocourselabel', 'local_menuadjust');
        $mainnavjson = (string) get_config('local_menuadjust', 'mainnavjson');

        if (!isloggedin() || isguestuser()) {
            $entries[] = self::build_entry('courses', $courseslabel, new moodle_url('/course/index.php'), [
                'menuadjust-link',
                'menuadjust-courses',
            ]);
        } else {
            $added = false;
            if ($targetcourseid > 0) {
                require_once($CFG->dirroot . '/course/lib.php');
                $course = $DB->get_record('course', ['id' => $targetcourseid]);
                if ($course) {
                    $context = context_course::instance($course->id);
                    if (is_enrolled($context, null, '', true)) {
                        $entries[] = self::build_entry('gotocourse', $gotocourselabel, new moodle_url('/course/view.php', ['id' => $course->id]), [
                            'menuadjust-link',
                            'menuadjust-gotocourse',
                        ]);
                        $added = true;
                    }
                }
            }
            if (!$added) {
                $entries[] = self::build_entry('apply', $applylabel, self::safe_url($applyurlvalue), [
                    'menuadjust-link',
                    'menuadjust-apply',
                ]);
            }
        }

        $entries = self::append_custom_entries($entries, $mainnavjson);

        return $entries;
    }

    /**
     * Build a single menu entry structure.
     *
     * @param string $suffix Unique suffix for the node key.
     * @param string $text Display text.
     * @param moodle_url|null $url Target URL or null for non-link items.
     * @param array<int, string> $classes CSS classes to apply.
     * @return array<string, mixed>
     */
    protected static function build_entry(string $suffix, string $text, ?moodle_url $url, array $classes = []): array {
        return [
            'key' => 'local_menuadjust_' . $suffix,
            'text' => $text,
            'url' => $url,
            'type' => \navigation_node::TYPE_CUSTOM,
            'icon' => null,
            'classes' => array_values(array_unique(array_filter($classes))),
        ];
    }

    /**
     * Parse JSON configuration for additional menu entries.
     *
     * @param array<int, array<string, mixed>> $entries Existing entries.
     * @param string $rawjson Raw JSON from configuration.
     * @return array<int, array<string, mixed>>
     */
    protected static function append_custom_entries(array $entries, string $rawjson): array {
        $rawjson = trim($rawjson);
        if ($rawjson === '') {
            return $entries;
        }

        $decoded = json_decode($rawjson, true);
        if (!is_array($decoded)) {
            debugging('local_menuadjust: invalid JSON in mainnavjson setting - ' . json_last_error_msg(), DEBUG_DEVELOPER);
            return $entries;
        }

        $index = 0;
        foreach ($decoded as $item) {
            if (!is_array($item)) {
                continue;
            }
            $text = trim((string) ($item['title'] ?? $item['text'] ?? ''));
            if ($text === '') {
                continue;
            }
            $specifiedkey = trim((string) ($item['key'] ?? ''));
            $key = $specifiedkey !== '' ? 'local_menuadjust_' . preg_replace('/[^a-z0-9_]+/i', '', $specifiedkey) : 'local_menuadjust_custom_' . $index;
            $classes = self::normalise_classes($item['classes'] ?? null);
            $classes = array_merge(['menuadjust-link', 'menuadjust-custom'], $classes);

            $urlvalue = trim((string) ($item['url'] ?? ''));
            $url = $urlvalue !== '' ? self::safe_url($urlvalue) : null;

            $entries[] = [
                'key' => $key,
                'text' => $text,
                'url' => $url,
                'type' => \navigation_node::TYPE_CUSTOM,
                'icon' => null,
                'classes' => $classes,
            ];
            $index++;
        }

        return $entries;
    }

    /**
     * Convert custom classes definition into an array.
     *
     * @param mixed $classes Raw classes data.
     * @return array<int, string>
     */
    protected static function normalise_classes(mixed $classes): array {
        if (empty($classes)) {
            return [];
        }
        if (is_array($classes)) {
            $values = $classes;
        } else {
            $values = preg_split('/\s+/', (string) $classes) ?: [];
        }
        $values = array_map('trim', $values);
        return array_values(array_filter($values));
    }

    /**
     * Safely create a moodle_url from configuration.
     *
     * @param string $value Raw URL value.
     * @return moodle_url|null
     */
    protected static function safe_url(string $value): ?moodle_url {
        try {
            return new moodle_url($value);
        } catch (Throwable $throwable) {
            debugging('local_menuadjust: invalid URL "' . $value . '" - ' . $throwable->getMessage(), DEBUG_DEVELOPER);
            return null;
        }
    }
}
