<?php

namespace Drupal\coi;

use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Form alterations for COI.
 */
class CoiFormAlterations implements CoiFormAlterationsInterface {

  use StringTranslationTrait;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new CoiFormAlterations object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function hookFormAlter(array &$form, FormStateInterface $form_state, string $form_id) : void {
    // Unfortunately we cannot modify (disable) the element in element #process,
    // as it happens too late in FormBuilder (right after, in
    // handleInputElement), so we have to use form alters.
    $this->alterTree($form);
  }

  /**
   * Recursively looks for form elements with COI keys.
   *
   * @param array $elements
   *   A render array.
   */
  protected function alterTree(array &$elements) : void {
    foreach (Element::children($elements) as $key) {
      $element = &$elements[$key];

      $coiSettings = $this->configFactory->get('coi.settings');
      $overrideBehavior = $coiSettings->get('override_behavior');

      $modifyElement = !empty($overrideBehavior) && $overrideBehavior !== CoiValues::OVERRIDE_BEHAVIOUR_NONE;

      // If already disabled.
      $access = isset($element['#access']) ? $element['#access'] : TRUE;
      if (!$access || !empty($element['#disabled']) || !$modifyElement) {
        continue;
      }

      $this->alterTree($element);

      if (!isset($element['#config'])) {
        continue;
      }

      $elementConfig = $element['#config'];
      list($configBin, $configKey) = $elementConfig['config'];

      $config = $this->configFactory->get($configBin);
      $hasOverrides = $config->hasOverrides($configKey);

      // Selectors.
      // Add selectors regardless of whether the element is overridden.
      if ($coiSettings->get('styling.selectors')) {
        $configBinClass = str_replace('.', '-', $configBin);
        $configKeyClass = str_replace('.', '-', $configKey);
        $element['#attributes']['class'][] = Html::getClass('config');
        if ($hasOverrides) {
          $element['#attributes']['class'][] = Html::getClass('config--overridden');
        }
        $element['#attributes']['class'][] = Html::getClass('config--' . $configBinClass);
        $element['#attributes']['class'][] = Html::getClass('config--' . $configBinClass . '--' . $configKeyClass);
      }

      if (!$hasOverrides) {
        continue;
      }

      // Can see override value, and not secret or can always see secrets.
      if ($coiSettings->get('overridden_value.enabled') && (empty($elementConfig['secret']) || $coiSettings->get('overridden_value.secrets'))) {
        $value = $config->get($configKey);
      }
      else {
        $value = $this->t('- Overridden value -');
      }

      if ($overrideBehavior == CoiValues::OVERRIDE_BEHAVIOUR_DISABLE) {
        $element['#disabled'] = TRUE;
        if ($coiSettings->get('overridden_value.element')) {
          $element['#default_value'] = $value;
        }
      }
      else if ($overrideBehavior == CoiValues::OVERRIDE_BEHAVIOUR_NO_ACCESS) {
        $element['#access'] = FALSE;
      }

      // Message.
      if ($coiSettings->get('message.enabled')) {
        $message = $coiSettings->get('message.template');
        $element['#coi_override_message'] = $message;
      }
    }
  }

}
