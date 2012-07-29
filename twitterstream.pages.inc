<?php
/**
 * @file
 * Twitterstream page callbacks.
 */

/**
 * Module settings form.
 */
function twitterstream_admin_settings($form, &$form_state) {
  $form = array();

  $form['auth'] = array(
    '#type' => 'fieldset',
    '#title' => t('Authentication'),
    '#tree' => FALSE,
  );

  $form['auth']['twitterstream_username'] = array(
    '#type' => 'textfield',
    '#title' => t('Account Username'),
    '#size' => 20,
    '#default_value' => variable_get('twitterstream_username', ''),
  );

  $form['auth']['twitterstream_password'] = array(
    '#type' => 'textfield',
    '#title' => t('Account Password'),
    '#size' => 20,
    '#default_value' => variable_get('twitterstream_password', ''),
  );


  $form['daemon'] = array(
    '#type' => 'fieldset',
    '#title' => t('Daemon Settings'),
    '#tree' => FALSE,
  );

  $form['daemon']['twitterstream_daemon_name'] = array(
    '#type' => 'textfield',
    '#title' => t('Daemon name'),
    '#size' => 16,
    '#maxlength' => 16,
    '#default_value' => variable_get('twitterstream_daemon_name', 'twitterstream'),
    '#description' => t('App name of the daemon.  Only needs to be changed if running multiple instances on the same server.'),
  );

  $form['daemon']['twitterstream_daemon_user'] = array(
    '#type' => 'textfield',
    '#title' => t('Name of system user to run daemon as'),
    '#size' => 20,
    '#default_value' => variable_get('twitterstream_daemon_user', 'www-data'),
  );

  $form['daemon']['twitterstream_daemon_group'] = array(
    '#type' => 'textfield',
    '#title' => t('Name of system group to run daemon as'),
    '#size' => 20,
    '#default_value' => variable_get('twitterstream_daemon_group', ''),
    '#description' => t('Leave blank to use the user\'s default group.'),
  );

  return system_settings_form($form);
}
