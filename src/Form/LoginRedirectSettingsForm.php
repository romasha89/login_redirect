<?php

/**
 * @file
 * Contains Drupal\login_redirect\Form\LoginRedirectSettingsForm.
 */

namespace Drupal\login_redirect\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

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
  protected function getEditableConfigNames()
  {
    return [
      'login_redirect.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'login_redirect_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $config = $this->config('login_redirect.settings');

    $options = array(0 => t('Disabled'), 1 => t('Enabled'));
    $form['status'] = array(
      '#type' => 'radios',
      '#title' => t('Module Status'),
      '#default_value' => $config->get('login_redirect_status'),
      '#options' => $options,
      '#description' => t('Should the module be enabled?'),
    );
    $form['parameter_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Parameter Name'),
      '#default_value' => $config->get('login_redirect_parameter_name'),
      '#description' => t('Enter user defined query parameter name same as we have q in drupal core. For example if the parameter name is set to "destination", then you would visit user/login&destination=(redirect destination).'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    if ((!is_numeric($form_state->getValue('parameter_name')) || $form_state->hasValue('parameter_name'))) {
      $this->config('login_redirect.settings')->set('login_redirect_parameter_name', $form_state->getValue('parameter_name'))->save();
      $this->config('login_redirect.settings')->set('login_redirect_status', $form_state->getValue('status'))->save();
    }
    else {
      drupal_set_message(t('The parameter name must consists of only alphabetical letters and cannot be left empty. The module was disabled.'), 'error');
    }
  }

}