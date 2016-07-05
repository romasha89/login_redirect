<?php

/**
 * @file
 * Contains Drupal\login_redirect\Form\LoginRedirectSettingsForm.
 */

namespace Drupal\login_redirect\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;

/**
 * Class LoginRedirectSettingsForm.
 *
 * @package Drupal\login_redirect\Form
 */
class LoginRedirectSettingsForm extends ConfigFormBase
{

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'login_redirect.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'login_redirect_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('login_redirect.settings');

    $options = array(0 => t('Disabled'), 1 => t('Enabled'));
    $form['status'] = array(
      '#type' => 'radios',
      '#title' => t('Module Status'),
      '#default_value' => $config->get('login_redirect_status'),
      '#options' => $options,
      '#description' => t('Should the module be enabled?'),
    );


    $form['force_redirect_options'] = array(
      '#type' => 'fieldset',
      '#title' => t('Force Redirect Options'),
    );
    $form['force_redirect_options']['force_redirect_status'] = array(
      '#type' => 'radios',
      '#title' => t('Force Redirect Status'),
      '#default_value' => $config->get('login_force_default_redirect_status'),
      '#options' => $options,
      '#description' => t('Should the module execute force redirection after authentication?'),
      '#attributes' => array('style' => array('display:block; float:left;padding-right: 50px')),
    );
    $form['force_redirect_options']['force_redirect_override_status'] = array(
      '#type' => 'radios',
      '#title' => t('Force Redirect Override Status'),
      '#default_value' => $config->get('login_force_default_redirect_override_status'),
      '#options' => $options,
      '#description' => t("Force Redirect overrides parameter if it's present in query?"),
      '#attributes' => array('style' => array('display:block; float:left;')),
    );
    $form['force_redirect_url'] = array(
      '#type' => 'textfield',
      '#title' => t('Force Redirect URL'),
      '#default_value' => $config->get('login_force_default_redirect_url'),
      '#description' => t('Enter force redirection URL (can be external or internal).'),
      '#attributes' => array(
        'placeholder' => array('http://'),
      ),
    );


    $form['parameter_name'] = array(
      '#type' => 'textfield',
      '#prefix' => '<div id="parameter-name">',
      '#suffix' => '</div>',
      '#title' => t('Query Parameter Name'),
      '#default_value' => $config->get('login_redirect_parameter_name'),
      '#description' => t('Enter user defined query parameter name same as we have q in drupal core. For example if the parameter name is set to "destination", then you would visit user/login&destination=(redirect destination).'),
    );
    $form['set_default'] = array(
      '#type' => 'button',
      '#value' => t('Set default'),
      '#ajax' => array(
        'callback' => array($this, 'setDefaultAjaxFormCallback'),
        'wrapper' => 'parameter-name',
        'method' => 'replace',
        'effect' => 'fade',
      ),
    );

    $roles = user_roles(TRUE);
    $roles_array = array();
    foreach ($roles as $key => $role) {
      $roles_array[$key] = $role->label();
    }
    $form['login_redirect_roles'] = array(
      '#type' => 'checkboxes',
      '#title' => t('User Roles'),
      '#options' => (array) $roles_array,
      '#default_value' => $config->get('login_redirect_roles') ? $config->get('login_redirect_roles') : array(),
      '#description' => t('Select User Roles to avail the redirect option.'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * Ajax Callback.
   *
   * @param array $form
   *    Form array.
   * @param FormStateInterface $form_state
   *    FormStateInterface Interface object.
   *
   * @return mixed
   *    Changed Form element.
   */
  public function setDefaultAjaxFormCallback(&$form, FormStateInterface &$form_state) {
    $form['parameter_name']['#value'] = 'destination';

    return $form['parameter_name'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (($form_state->hasValue('parameter_name') && !is_numeric($form_state->getValue('parameter_name')))) {
      $this->config('login_redirect.settings')->set('login_redirect_parameter_name', $form_state->getValue('parameter_name'))->save();
      $this->config('login_redirect.settings')->set('login_redirect_status', $form_state->getValue('status'))->save();
    }
    else {
      drupal_set_message(t('The parameter name must consists of only alphabetical letters and cannot be left empty. The module was disabled.'), 'error');
    }

    $url = $form_state->getValue('force_redirect_url');
    if ((UrlHelper::isExternal($url) && UrlHelper::isValid($url, TRUE)) || !(UrlHelper::isExternal($url))) {
      $this->config('login_redirect.settings')->set('login_force_default_redirect_url', $form_state->getValue('force_redirect_url'))->save();
      $this->config('login_redirect.settings')->set('login_force_default_redirect_status', $form_state->getValue('force_redirect_status'))->save();
    }
    else {
      drupal_set_message(t('Check force redirection URL validity.'), 'error');
    }

    $this->config('login_redirect.settings')->set('login_force_default_redirect_override_status', $form_state->getValue('force_redirect_override_status'))->save();
    $this->config('login_redirect.settings')->set('login_redirect_roles', $form_state->getValue('login_redirect_roles'))->save();
  }

}
