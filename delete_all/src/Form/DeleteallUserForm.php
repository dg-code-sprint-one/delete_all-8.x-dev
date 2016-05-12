<?php

namespace Drupal\delete_all\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Defines a confirmation form for deleting mymodule data.
 */
class DeleteallUserForm extends ConfirmFormBase {

    /**
     * The ID of the item to delete.
     *
     * @var string
     */
    // protected $id;

    /**
     * {@inheritdoc}.
     */
    public function getFormId()
    {
        return 'delete-all-user';
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestion() {
        return t('Are you sure you want to delete all users (uid > 1)?');
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
        return t('This will delete all users except for User 1 (the administrative user). This action cannot be undone');
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
    public function buildForm(array $form, FormStateInterface $form_state) {
        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
      $roles = NULL;        
      $count = 0;
       if (!$roles) { //echo '<pre>no roles';print_r($roles);exit;
        $result = db_query('SELECT uid FROM {users} WHERE uid > 1');
        foreach ($result as $data) {
          user_delete($data->uid);
          $count++;
        }
        // Delete the URL aliases
        db_query("DELETE FROM {url_alias} WHERE source LIKE 'user/%%'");
      }
      else {
        if (is_array($roles)) {  //echo '<pre>roles';print_r($roles);exit;
          $result = array();
          foreach ($roles as $role) {
            $rid = db_select('role', 'r')->fields('r', array('rid'))->condition('name', $role, '=')->execute()->fetchField();
            $result = array_merge($result, db_select('users_roles', 'ur')->fields('ur', array('uid'))->condition('rid', $rid, '=')->execute()->fetchCol('uid'));
          }
        }
        else {
          $rid = db_select('role', 'r')->fields('r', array('rid'))->condition('name', $roles, '=')->execute()->fetchField();
          $result = db_select('users_roles', 'ur')->fields('ur', array('uid'))->condition('rid', $rid, '=')->execute()->fetchCol('uid');
        }
        foreach ($result as $data) {
          user_delete($data);
          $count++;
        }
        // @TODO Delete individual aliases
      }
     
      drupal_set_message(t('All users have been deleted. Number of users deleted: !count.', array('!count' => $count)));
      $form_state->setRedirect('system.admin');
    }
}