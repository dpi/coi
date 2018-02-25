<?php

namespace Drupal\coi;

use Drupal\Core\Form\FormStateInterface;

/**
 * Interface for form alterations for COI.
 */
interface CoiFormAlterationsInterface {

  /**
   * Implements hook_form_alter().
   *
   * @see \hook_form_alter()
   * @see \coi_form_alter()
   */
  public function hookFormAlter(array &$form, FormStateInterface $form_state, string $form_id) : void;

}
