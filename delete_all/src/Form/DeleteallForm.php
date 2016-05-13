<?php
/**
 * @file
 * Contains \Drupal\delete_all\Form\DeleteallForm.
 */

namespace Drupal\delete_all\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Url;

/**
 * Contribute form.
 */
class DeleteallForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'delete_all_form';
  }
    protected function getEditableConfigNames() {
    return [
      'delete_all.settings',
    ];
  }
  /**
   * {@inheritdoc}
   */

  public function buildForm(array $form, FormStateInterface $form_state) {
  $settings = $this->config('delete_all.settings');
  $result = db_query("SELECT type, COUNT(*) AS num FROM {node} GROUP BY type");
  $count = array();
  foreach ($result as $data) {
    $count[$data->type] = $data->num;
  }

  // Add the types to the form. If there are no eligible types to delete,
  // we don't need to render the form.
  $types = array();
  foreach (node_type_get_names() as $type => $info) {
    if (array_key_exists($type, $count)) {
      $types[$type] = $info . ' (' . $count[$type] . ')';
    }
  }
  asort($types);

    $form['all'] = array(
    '#type' => 'checkbox',
    '#default_value' => TRUE,
    '#title' => t('Delete All Content'),
    '#description' => t('Select to delete all content'),
    '#attributes' => array('class' => array('delete-all')),
  );

  $form['reset'] = array(
    '#type' => 'checkbox',
    '#default_value' => FALSE,
    '#title' => t('Reset node count'),
    '#description' => t('Select to reset the node count'),
    '#attributes' => array('class' => array('delete-reset')),
  );

  $form['type-fieldset'] = array(
    '#type' => 'fieldset',
    '#title' => t('Types'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    'types' => array(
      '#type' => 'checkboxes',
      '#options' => $types,
      '#description' => t('Select the types of content to delete'),
    ),
  );
  $form['method-fieldset'] = array(
    '#type' => 'fieldset',
    '#title' => t('Method'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    'method' => array(
      '#type' => 'radios',
      '#title' => t('Method'),
      '#options' => array('normal' => t('Normal'), 'quick' => t('Quick')),
      '#default_value' => 'normal',
      '#description' => t('Normal node delete calls node_delete() on every node in the database.  If you have only a few hundred nodes, this can take a very long time.  Use the quick node delete method to get around this problem.  This method deletes directly from the database, skipping the extra php processing.  The downside is that it can miss related tables that are normally handled by module hook_delete\'s.'),
    ),
  );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Submit'),
    );
  return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory()->getEditable('delete_all.settings')
    ->set('all', $form_state->getValue('all'))
    ->set('reset', $form_state->getValue('reset'))
    ->set('types', $form_state->getValue('types'))
    ->set('method', $form_state->getValue('method'))
    ->save();
     $form_state->setRedirect('delete_all.confirm');
  }
}
?>