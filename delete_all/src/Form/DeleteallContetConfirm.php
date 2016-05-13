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
        return t('Are you sure you wish to delete content?');
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
        return t('This will delete all Content. This action cannot be undone');
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
        return $this->t('Cancel delete of all content');
    }

    /**
     * {@inheritdoc}
     *
     * @param int $id
     *   (optional) The ID of the item to be deleted.
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
    $all = \Drupal::config('delete_all.settings')->get('all');         
    $reset = \Drupal::config('delete_all.settings')->get('reset');
    $types = \Drupal::config('delete_all.settings')->get('types');
    $method = \Drupal::config('delete_all.settings')->get('method');
        foreach ($types as $key => $value) {
          if(isset($value) && !empty($value)){
            $ctype[] = $value;          
           }
        }
        if ($method == 'normal'){
            set_time_limit(240);
            if (is_array($ctype) && count($ctype) > 0) {
                foreach ($ctype as $type) {
                $nids_query = db_select('node', 'n')
                ->fields('n', array('nid'))
                ->condition('n.type', $type)
                ->execute();
                $nids = $nids_query->fetchCol();
                entity_delete_multiple('node', $nids);
                }
            }else {
                  $nids_query = db_select('node', 'n')
                  ->fields('n', array('nid'))
                  ->execute();
                  $nids = $nids_query->fetchCol();
                  entity_delete_multiple('node', $nids);
            }
          }else{
    /**
     * {@quick delete}
     */
           set_time_limit(240);
            if (is_array($ctype) && count($ctype) > 0) {
            }
          }
        if(!$type){
          // Delete the URL aliases
            db_query("DELETE FROM {url_alias} WHERE source LIKE 'node/%%'");

            drupal_set_message(t('All nodes, comments and URL aliases have been deleted. Number of nodes deleted'));

            if (isset($reset) && !empty($reset)) {
              db_query("ALTER TABLE node AUTO_INCREMENT=1");
              db_query("ALTER TABLE node_revision AUTO_INCREMENT=1");
              if (module_exists('comment')) {
                db_query("ALTER TABLE comment AUTO_INCREMENT=1");
                drupal_set_message(t('All node, revision and comment counts have been reset.'));
              }
              else {
                drupal_set_message(t('All node and revision counts have been reset.'));
              }
            }
        }else {
          drupal_set_message(t('Nodes and comments of typehave been deleted. Number of nodes deleted'));
        }
        
    }
}