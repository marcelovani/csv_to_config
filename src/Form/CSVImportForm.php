<?php
/**
 * @file
 * Contains Drupal\csv_to_config\Form\CSVImportForm
 */

namespace Drupal\csv_to_config\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class CSVImportForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'csv_to_config_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = array();

    $form['csv_upload'] = array(
      '#title' => t('Upload a CSV File'),
      '#type' => 'file',
      '#description' => t('Select the CSV file that you want to convert to configuration.'),
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Continue'),
      '#weight' => 1,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $form_state->getValues();
    //backup_migrate_perform_restore($config['source_id'], 'upload', 'backup_migrate_restore_upload', $config);
  }

}
