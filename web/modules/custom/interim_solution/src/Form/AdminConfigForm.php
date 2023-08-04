<?php

namespace Drupal\interim_solution\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AdminConfigForm.
 */
class AdminConfigForm extends ConfigFormBase {

  /**
   * @return string
   */
  public function getFormId() {
    return 'interim_solution_admin_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'interim_solution.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('interim_solution.settings');

    $form['automatic_switch'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Automatic Switch'),
      '#description' => $this->t('Check this to use the available assets from Catalog in all Finding aids. With this will not be necessary to moderate the Finding aids content.'),
      '#default_value' => $config->get('automatic_switch'),
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#description' => $this->t('Email to receive the notifications when a Finding aid has content in th Catalog.'),
      '#default_value' => $config->get('email'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $automatic_switch = $form_state->getValue('automatic_switch');
    $config = $this->config('interim_solution.settings');
    $automatic_switch_old = $config->get('automatic_switch');
    $config
      ->set('automatic_switch', $automatic_switch)
      ->set('email', $form_state->getValue('email'))
      ->save();

    if ($automatic_switch != $automatic_switch_old) {
      Cache::invalidateTags(['interim_solution:finding_aid:anonymous']);
    }

    parent::submitForm($form, $form_state);
  }

}
