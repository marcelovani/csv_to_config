<?php
/**
 * @file
 * Contains Drupal\csv_to_config\Form\CSVImportStep3Form
 */

namespace Drupal\csv_to_config\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CSVImportStep3Form extends MultistepFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'csv_to_config_import_form_step1';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // If the user comes straight to this page, redirect.
    if (empty($this->store->get('csv_array'))) {
      $url = Url::fromRoute('csv_to_config.csv_import.step1')->toString();
      return new RedirectResponse($url);
    }

    $form['text'] = array(
      '#markup' => t('Done'),
    );

    $form['actions']['submit']['#value'] = $this->t('Start over');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('csv_to_config.csv_import.step1');
  }

}
