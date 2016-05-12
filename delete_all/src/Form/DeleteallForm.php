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
  // $result = db_query("SELECT type, COUNT(*) AS num FROM {node} GROUP BY type");
  // $count = array();
  // foreach ($result as $data) {
  //   $count[$data->type] = $data->num;
  // }
  // echo '<pre>';print_r($result);exit;

class DeleteallForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'delete_all_form';
  }
  /**
   * {@inheritdoc}
   */

  public function buildForm(array $form, FormStateInterface $form_state) {

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
    $form['actions']['delete'] = array(
      '#type' => 'link',
      '#title' => $this->t('Delete'),
      '#attributes' => array(
        'class' => array('button'),
      ),
      '#weight' => 0,
      '#url' => Url::fromRoute('delete_all.confirm'),
    );
  // $form['#action'] = url('admin/content/delete_content/confirm');
  return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

     // Validate video URL.
    // if (!UrlHelper::isValid($form_state->getValue('video'), TRUE)) {
    //   $form_state->setErrorByName('video', $this->t("The video url '%url' is invalid.", array('%url' => $form_state->getValue('video'))));
    // }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
// echo '<pre>';print_r($form_state);exit;
    // Display result.
// $nids_query = db_select('node', 'n')
// ->fields('n', array('nid'))
//->condition('n.type', $types, 'IN')
// ->range(0, 500)
// ->execute();

// $nids = $nids_query->fetchCol();

// entity_delete_multiple('node', $nids);
        //echo '<pre>';print_r($form_state);exit;
    // $new1 = NULL;
    // $delete_url = Url::fromRoute('delete_all.confirm',array('form_state'=>$new1));

    // $form_state->setRedirect($delete_url);
    //Url::fromRoute('delete_all.confirm',array('form_state'=>$data));


  }
}
?>