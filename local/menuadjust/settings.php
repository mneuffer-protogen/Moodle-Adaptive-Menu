<?php
// This file is part of Moodle - http://moodle.org/
//
// local_menuadjust settings

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) { // needs this condition or there is error on login page
	$settings = new admin_settingpage('local_menuadjust', get_string('pluginname', 'local_menuadjust'));

	// Text for not-logged-in menu
	$settings->add(new admin_setting_configtext('local_menuadjust/courseslabel',
		get_string('courseslabel', 'local_menuadjust'),
		get_string('courseslabel_desc', 'local_menuadjust'), 'Courses', PARAM_TEXT));

	// Apply button label and url
	$settings->add(new admin_setting_configtext('local_menuadjust/applylabel',
		get_string('applylabel', 'local_menuadjust'),
		get_string('applylabel_desc', 'local_menuadjust'), 'Apply', PARAM_TEXT));

	$settings->add(new admin_setting_configtext('local_menuadjust/applyurl',
		get_string('applyurl', 'local_menuadjust'),
		get_string('applyurl_desc', 'local_menuadjust'), '/login/index.php', PARAM_URL));

	// Course ID to check for enrolled users
	$settings->add(new admin_setting_configtext('local_menuadjust/targetcourseid',
		get_string('targetcourseid', 'local_menuadjust'),
		get_string('targetcourseid_desc', 'local_menuadjust'), '', PARAM_INT));

	// Label when user is enrolled
	$settings->add(new admin_setting_configtext('local_menuadjust/gotocourselabel',
		get_string('gotocourselabel', 'local_menuadjust'),
		get_string('gotocourselabel_desc', 'local_menuadjust'), 'Go to course', PARAM_TEXT));

	// Optional: main navigation items editable as a JSON string for advanced use
	$settings->add(new admin_setting_configtextarea('local_menuadjust/mainnavjson',
		get_string('mainnavjson', 'local_menuadjust'),
		get_string('mainnavjson_desc', 'local_menuadjust'), '', PARAM_RAW));

	$ADMIN->add('localplugins', $settings);
}

