<?php

namespace Drupal\coi\Form;

use Drupal\coi\CoiValues;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Config Override Inspector settings.
 */
class CoiSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() : array {
    return [
      'coi.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() : string {
    return 'coi_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) : array {
    $config = $this->config('coi.settings');

    // @todo force 'disable' unless settings.php allows it.
    $form['override_behavior'] = [
      '#type' => 'radios',
      '#title' => $this->t('Overridden config behaviour'),
      '#options' => [
        CoiValues::OVERRIDE_BEHAVIOUR_NONE => $this->t('Enable element, allow user to modify active configuration (default Drupal behavior)'),
        CoiValues::OVERRIDE_BEHAVIOUR_DISABLE => $this->t('Disable element'),
        CoiValues::OVERRIDE_BEHAVIOUR_NO_ACCESS => $this->t('Disable element and hide'),
      ],
      '#description' => $this->t('What to do if an element representing a configuration is overridden.'),
      '#default_value' => $config->get('override_behavior'),
    ];

    $form['message'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Message'),
      '#tree' => TRUE,
    ];

    // message to display:
    $form['message']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show message'),
      '#description' => $this->t('Show a message to user if an element representing a configuration is overridden.'),
      '#default_value' => $config->get('message.enabled'),
    ];

    $form['message']['template'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#description' => $this->t('Message to show if an element representing a configuration is overridden.'),
      '#default_value' => $config->get('message.template'),
      '#states' => [
        'visible' => [
          ':input[name="message[enabled]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    // @todo message tokens.

    $form['overridden_value'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Overridden value'),
      '#tree' => TRUE,
    ];

    $form['overridden_value']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show real value'),
      '#description' => $this->t('Allow user to see the overridden value. If enabled, you may show the real value in message or element itself.'),
      '#default_value' => $config->get('overridden_value.enabled'),
    ];

    $form['overridden_value']['element'] = [
      '#type' => 'radios',
      '#title' => $this->t('Show real value in disabled element'),
      '#description' => $this->t('Only effective if auto disable and real value are enabled.'),
      '#options' => [
        0 => $this->t('Show original value in element (default Drupal behavior)'),
        1 => $this->t('Show overridden value in element'),
      ],
      '#default_value' => !empty($config->get('overridden_value.element')) ? 1: 0,
      '#states' => [
        'visible' => [
          ':input[name="overridden_value[enabled]"]' => ['checked' => TRUE],
          ':input[name="override_behavior"]' => ['value' => CoiValues::OVERRIDE_BEHAVIOUR_DISABLE],
        ],
      ],
    ];

    // @todo force enabled unless settings.php allows it.
    $form['overridden_value']['secrets'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Expose secrets'),
      '#description' => $this->t('Allow users to view the real value of elements designated as secrets. Such as passwords, authorization tokens, etc. Only effective if real value is enabled.'),
      '#default_value' => $config->get('overridden_value.secrets'),
      '#states' => [
        'visible' => [
          ':input[name="overridden_value[enabled]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['styling'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Styling'),
      '#tree' => TRUE,
    ];

    $form['styling']['selectors'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add classes'),
      '#description' => $this->t('Add classes to HTML allowing themers to target elements representing configuration.'),
      '#default_value' => $config->get('styling.selectors'),
    ];

    $form['styling']['default'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add default CSS'),
      '#description' => $this->t('Use module provided theming.'),
      '#default_value' => $config->get('styling.default'),
      // Not yet.
      '#access' => FALSE,
      '#states' => [
        'visible' => [
          ':input[name="styling[selectors]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) : void {
    parent::submitForm($form, $form_state);

    $this->config('coi.settings')
      ->set('override_behavior', $form_state->getValue(['override_behavior']))
      ->set('message.enabled', (bool) $form_state->getValue(['message', 'enabled']))
      ->set('message.template', $form_state->getValue(['message', 'template']))
      ->set('overridden_value.enabled', (bool) $form_state->getValue(['overridden_value', 'enabled']))
      ->set('overridden_value.element', (bool) $form_state->getValue(['overridden_value', 'element']))
      ->set('overridden_value.secrets', (bool) $form_state->getValue(['overridden_value', 'secrets']))
      ->set('styling.selectors', (bool) $form_state->getValue(['styling', 'selectors']))
      ->set('styling.default', (bool) $form_state->getValue(['styling', 'default']))
      ->save();
  }

}
