<?php

namespace Drupal\delete_all\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Defines a confirmation form for deleting mymodule data.
 */
class DeleteallContetConfirm extends ConfirmFormBase {

    /**
     * The ID of the item to delete.
     *
     * @var string
     */
     //protected $new1;

    /**
     * {@inheritdoc}.
     */
    public function getFormId()
    {
        return 'delete-all-content-confirm';
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestion() {
        return t('Are you sure you want to delete all content');
    }

    /**
     * {@inheritdoc}
     */
    public function getCancelUrl() {
        //this needs to be a valid route otherwise the cancel link won't appear
        return new Url('system.admin');
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription() {
        return t('This will delete all content');
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmText() {
        return $this->t('Delete');
    }


    /**
     * {@inheritdoc}
     */
    public function getCancelText() {
        return $this->t('Cancel');
    }

    /**
     * {@inheritdoc}
     *
     * @param int $id
     *   (optional) The ID of the item to be deleted.
     */
    public function buildForm(array $form, FormStateInterface $form_state, $new = NULL) {
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
    return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
    //echo $this->all;
    $all = $form_state->getValue('all');        
    $reset = $form_state->getValue('reset');
    $types = $form_state->getValue('types');
    $method = $form_state->getValue('method');
    //echo '<pre>';print_r($types);exit;
        foreach ($types as $key => $value) {
           if(isset($value) && !empty($value)){
            
           }
        }

    
    }
}