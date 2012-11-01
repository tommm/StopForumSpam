<?php
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// Specific ACP actions
if($mybb->input['module'] == 'config-settings' && $mybb->input['action'] == 'change')
{
	// Saving or loading settings
	$plugins->add_hook('admin_config_settings_change', 'stopforumspam_save');
}
else
{
	return;
}

function stopforumspam_save()
{
	global $db, $mybb, $plugins;

	// Check to see if this is our SFS setting group
	$gid = (int)$mybb->input['gid'];

	if($gid <= 21)
	{
		return;
	}
	elseif($gid > 21)
	{
		$query = $db->simple_select('settinggroups', 'gid', "name = 'stopforumspam'");
		$this_group = $db->fetch_field($query, 'gid');

		if($this_group != $gid)
		{
			return;
		}
	}

	// This is our SFS setting group - save the data
	if($mybb->request_method == 'post')
	{
		$values = array();
		$settings = array('username', 'email', 'ip');

		foreach($settings as $setting)
		{
			$values[] = (isset($mybb->input['sfs_settings'][$setting])) ? intval($mybb->input['sfs_settings'][$setting]) : 0;
		}

		$mybb->input['upsetting']['sp_check'] = implode(',', $values);
		return;
	}

	// Modify our setting so it's easier for users to manage
	$plugins->add_hook('admin_formcontainer_output_row', 'stopforumspam_permissions');
}

function stopforumspam_permissions(&$row)
{
	global $form, $mybb;

	if($row['row_options']['id'] != 'row_setting_sp_check')
	{
		return;
	}

	$i = 1;
	$row['content'] = '';
	$cur_settings = explode(',', $mybb->settings['sp_check']);

	$settings = array(
		'username' => array('name' => 'Check Username?', 'description' => 'Check the username against StopForumSpam database?', 'value' => $cur_settings[0]),
		'email' => array('name' => 'Check Email?', 'description' => 'Check the email against StopForumSpam database?', 'value' => $cur_settings[1]),
		'ip' => array('name' => 'Check IP Address?', 'description' => 'Check the IP address against StopForumSpam database?', 'value' => $cur_settings[2]),
	);

	foreach($settings as $v => $p)
	{
		$row['content'] .= $form->generate_check_box("sfs_settings[{$v}]", 1, $p['name'], array('id' => "setting_sfs_settings_{$i}", 'class' => "setting_sfs_settings_{$i}", 'checked' => $p['value']));
		$row['content'] .= "<div class='description' style='margin: 0 0 10px 25px;'>{$p['description']}</div>";
		++$i;
	}
}

function stopforumspam_install()
{
	global $db, $mybb;

	stopforumspam_uninstall();

	$setting_group = array(
		'name' => 'stopforumspam',
		'title' => 'Stop Forum Spam Check',
		'description' => 'Checks new registrations against Stop Forum Spam',
		'disporder' => 5,
		'isdefault' => 0
	);

	$gid = $db->insert_query("settinggroups", $setting_group);

	$setting_array = array(
		'sp_check' => array(
			'title' => 'Check User Details',
			'description' => 'Select which information you want to check against SFS when a user registers:',
			'optionscode' => 'text',
			'value' => '0,0,0',
			'disporder' => 1
		),
		'sp_confidence' => array(
			'title' => 'Confidence Level',
			'description' => 'Select the minimum confidence level for checking spammers (the higher the level the more likely the user is a spammer). A user that generates a level higher than this will be denied registration.<br />Example: A user who has been mistakenly added to the spam database might generate a 0-20% level. A hardened spammer will be upwards of 40-50% and more.',
			'optionscode' => "select\n0=0%\n10=10%\n20=20%\n30=30%\n40=40%\n50=50%\n60=60%\n70=70%\n80=80%\n90=90%\n100=100%",
			'value' => 40,
			'disporder' => 2
		),
		'sp_log' => array(
			'title' => 'Log Denials?',
			'description' => 'Log users that are denied registrations in a log file?',
			'optionscode' => 'yesno',
			'value' => 1,
			'disporder' => 3
		),
		'sp_fail' => array(
			'title' => 'Failsafe',
			'description' => 'If there is an error loading SFS, should users be allowed to register?',
			'optionscode' => 'yesno',
			'value' => 1,
			'disporder' => 4
		)
	);

	foreach($setting_array as $name => $setting)
	{
		$setting['name'] = $name;
		$setting['gid'] = $gid;

		$db->insert_query('settings', $setting);
	}

	rebuild_settings();
}

function stopforumspam_uninstall()
{
	global $db, $mybb, $lang;

	$db->delete_query('settings', "name IN ('sp_check','sp_confidence','sp_log','sp_fail')");
	$db->delete_query('settinggroups', "name = 'stopforumspam'");

	rebuild_settings();
}

function stopforumspam_is_installed()
{
	global $mybb;

	if($mybb->settings['sp_fail'])
	{
		return true;
	}

	return false;
}

function stopforumspam_activate()
{
	global $mybb;

	$plugin = stopforumspam_info();
	if(version_compare($plugin['version'], '1.4', '>=') && !isset($mybb->settings['sp_check']))
	{
		stopforumspam_1400();
	}
}
	// Upgrade Functions
	function stopforumspam_1400()
	{
		global $db, $mybb;

		$query = $db->simple_select('settinggroups', 'gid', "name = 'stopforumspam'");
		$gid = $db->fetch_field($query, 'gid');

		$settings = array(
			'sp_check' => array(
				'title' => 'Check User Details',
				'description' => 'Select which information you want to check against SFS when a user registers:',
				'optionscode' => 'text',
				'value' => "{$mybb->settings['sp_user']},{$mybb->settings['sp_email']},{$mybb->settings['sp_ip']}",
				'disporder' => 1,
				'gid' => $gid
			),
			'sp_confidence' => array(
				'title' => 'Confidence Level',
				'description' => 'Select the minimum confidence level for checking spammers (the higher the level the more likely the user is a spammer). A user that generates a level higher than this will be denied registration.<br />Example: A user who has been mistakenly added to the spam database might generate a 0-20% level. A hardened spammer will be upwards of 40-50% and more.',
				'optionscode' => "select\n0=0%\n10=10%\n20=20%\n30=30%\n40=40%\n50=50%\n60=60%\n70=70%\n80=80%\n90=90%\n100=100%",
				'value' => 40,
				'disporder' => 2,
				'gid' => $gid
			),
		);

		foreach($settings as $name => $setting)
		{
			$setting['name'] = $name;
			$db->insert_query('settings', $setting);
		}

		// Delete old settings, not required anymore
		$db->delete_query('settings', "name IN ('sp_user','sp_email','sp_ip','sp_mode')");

		rebuild_settings();
	}
?>