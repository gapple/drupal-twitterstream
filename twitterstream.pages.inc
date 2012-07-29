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

  return system_settings_form($form);
}
