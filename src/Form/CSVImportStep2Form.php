<?php
/**
 * @file
 * Contains Drupal\csv_to_config\Form\CSVImportStep2Form
 */

namespace Drupal\csv_to_config\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

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

    // If the user comes straight to this page, redirect.
    if (empty($this->store->get('csv_array'))) {
      $url = Url::fromRoute('csv_to_config.csv_import.step1')->toString();
      return new RedirectResponse($url);
    }

    $form['config_name'] = array(
      '#title' => t('Configuration name'),
      '#type' => 'textfield',
      '#default_value' => $form_state->getValue('config_name'),
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
      '#description' => t('The key to store the configuration. You can use 1 (one) replacement token to get the value from the CSV. Example: domain.config.[site_machine_name].config_token.tokens'),
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

    $form['csv_contents'] = array(
      '#type' => 'table',
      '#caption' => $this->t('Rows (Limited results)'),
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

    $this->store->set('csv_array_processed', $tokenArray);

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
    $formValues = &$form_state->getValues();
    $formConfigName = $formValues['config_name'];

//@todo test with wrong key
//@todo test without token
    $configArray = array();
    foreach ($this->store->get('csv_array_processed') as $key => $values) {
      $rowValues= array_filter($values);
      if (empty($rowValues)) {
        // Ignore rows with no values.
        continue;
      }
      $i = 0;
      foreach ($values as $column_key => $value) {
        $configArray[$i][$key] = $value;
        $i++;
      }
    }

    // Get the config name ID from token if it exits.
    $configNameId = NULL;
    preg_match("/\\[(\\w+)\\]/", $formConfigName, $matches);
    if (isset($matches[1])) {
      $configNameId = $matches[1];
    }

    foreach ($configArray as $ckey => $values) {
      if (isset($values[$configNameId])) {
        if (empty($values[$configNameId])) {
          continue;
        }
        $configName = str_replace('[' . $configNameId . ']', $values[$configNameId], $formConfigName);
        unset($values[$configNameId]);
      }

      $configObj = \Drupal::service('config.factory')->getEditable($configName);
      foreach ($values as $key => $value) {
        $configObj->set($key, $value);
      }
      $configObj->save();
    }

    parent::deleteStore();
    $form_state->setRedirect('csv_to_config.csv_import.step2');
  }

}
