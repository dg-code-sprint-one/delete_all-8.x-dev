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
            $deleted = 0;
    /**
     * {@quick delete}
     */
           set_time_limit(240);
            if (is_array($ctype) && count($ctype) > 0) {
                  $deleted = 0;
  foreach ($ctype as $type) {
    // keep this alive
    set_time_limit(240);

    // determine how many items will be deleted
    $query = "SELECT COUNT(*) amount FROM {node} n ".
              "WHERE n.type = :type";
     $result = db_query($query, array(':type' => $type))->fetch();
     $count = $result->amount;   
    if ($count) { // should always be positive
      /**
       * build a list of tables that need to be deleted from
       *
       * The tables array is of the format table_name => array('col1', 'col2', ...)
       * where "col1, col2" are using "nid, vid", but could simply be "nid".
       */

      $nid_vid = array('nid', 'vid');
      $nid = array('nid');
      $tables = array('node_revisions' => $nid_vid, 'comments' => $nid);
      $tables[_content_tablename($type, CONTENT_DB_STORAGE_PER_CONTENT_TYPE)] = $nid_vid;
      $content = content_types($type);
      if (count($content['fields'])) {
        foreach ($content['fields'] as $field) {
          $field_info = content_database_info($field);
          $tables[$field_info['table']] = $nid_vid;
        }
      }

      // find all other tables that might be related
      switch ($GLOBALS['db_type']) {
        case 'mysql':
        case 'mysqli':
          $result_tables = db_query("SHOW TABLES");
          while ($data = db_fetch_array($result_tables)) {
            $table = array_pop($data);
            if (isset($tables[$table]) || substr($table, 0, 8) == 'content_') {
              continue;
            }
            $result_cols = db_query("SHOW COLUMNS FROM %s", $table);
            $cols = array();
            while ($data = db_fetch_array($result_cols)) {
              $cols[$data['Field']] = $data;
            }
            if (isset($cols['nid'])) {
              $tables[$table] = isset($cols['vid']) ? $nid_vid : $nid;
            }
          }
          break;

        case 'pgsql':
          // @TODO: inspect the database and look for nid fields
          break;
      }

      // @todo: update all node related nid references
      // delete from all of the content tables in one sql statement
      $sql = array('delete' => array(), 'from' => array(), 'where' => array());
      $index = 0;
      foreach ($tables as $table => $cols) {
        $table = '{' . $table . '}';
        $sql['cols'][] = "t$index.*";
        // build the ON clause
        $on = array();
        foreach ($cols as $col) {
          $on[] = "t$index.$col = n.$col";
        }
        // now that we have the ON clause, build the join clause
        $sql['join'][] = " LEFT JOIN $table t$index ON " . implode(' AND ', $on);
        $index ++;
      }
      $delete_sql = "DELETE n.*, " . implode(', ', $sql['cols']) . " FROM {node} n " . implode(' ', $sql['join']);
      db_query($delete_sql . " WHERE n.type = '%s'", $type);

      $deleted += $count;
    }
  }
  return $deleted;

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