<?php
/**
 * @file
 * Contains Drupal\csv_to_config\Form\CSVImportStep2Form
 */

namespace Drupal\csv_to_config\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class CSVImportStep2Form extends MultistepFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'csv_to_config_import_form_step2';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    if (empty($this->store->get('csv_array'))) {
      drupal_set_message($this->t('You need to upload a CSV file.'), 'error');
      $url = Url::fromRoute('csv_to_config.csv_import.step1')->toString();
      return new RedirectResponse($url);
    }

    $csvArray = $this->store->get('csv_array');

    $columns = array_shift($csvArray);
    $tokenArray = array();
    foreach ($csvArray as $key => $csvRow) {
      $tokenRow = array();

      foreach ($csvRow as $i => $value) {
        $tokenRow[$columns[$i]] = $value;
      }

      $tokenArray[$key] = $tokenRow;
    }

    $form['config_name'] = array(
      '#title' => t('Configuration name'),
      '#type' => 'textfield',
      '#default_value' => $form_state->getValue('config_name'),
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
      '#description' => t('The key to store the configuration. You can use replacement tokens to use values from the CSV. Example: domain.config.[site_machine_me].config_token.tokens'),
    );

    $form['actions']['previous'] = array(
      '#type' => 'link',
      '#title' => $this->t('Previous'),
      '#attributes' => array(
        'class' => array('button'),
      ),
      '#weight' => 0,
      '#url' => Url::fromRoute('csv_to_config.csv_import.step1'),
    );

    $limit = 3;
    $form['csv_contents'] = array(
      '#type' => 'table',
      '#caption' => $this->t('Rows'),
      '#header' => array_merge(['Key'], array_slice($columns, 0, $limit)),
    );

    foreach ($tokenArray as $key => $values) {
      $form['csv_contents'][$key]['#attributes'] = array('class' => array('foo', 'baz'));
      $form['csv_contents'][$key]['token'] = array(
        '#markup' => $key,
        '#title_display' => 'invisible',
      );
      $i = 0;
      foreach ($values as $column_key => $value) {
        if ($i++ >= $limit) {
          break;
        }
        $form['csv_contents'][$key][$column_key] = array(
          '#markup' => $value,
          '#title_display' => 'invisible',
        );
      }

    }

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
//    $this->store->set('file', $form_state->getValue('file'));
//    $this->store->set('file_contents', $form_state->getValue('file_contents'));

    // Save the data
    parent::saveData();
    //$form_state->setRedirect('some_route');
  }

}
