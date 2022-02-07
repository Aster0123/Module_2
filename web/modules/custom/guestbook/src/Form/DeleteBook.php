<?php

namespace Drupal\guestbook\Form;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Define class for the DeleteBook.
 */
class DeleteBook extends ConfirmFormBase {

  /**
   * Drupal\Core\Database definition.
   *
   * @var \Drupal\Core\Database\Connection|object|null
   */
  public $idBook;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'delete book';
  }

  /**
   * Return question in the form.
   */
  public function getQuestion(): TranslatableMarkup {
    return t('To delete this feedback?');
  }

  /**
   * Return description in the form.
   */
  public function getDescription(): TranslatableMarkup {
    return t('Do you really want to delete this feedback?');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText(): TranslatableMarkup {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelText(): TranslatableMarkup {
    return t('Cancel');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $idBook = NULL): array {
    $this->id = $idBook;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $query = \Drupal::database();
    $query->delete('guestbook')
      ->condition('id', $this->id)
      ->execute();
    \Drupal::messenger()->addStatus('You deleted your feedback');
    $form_state->setRedirect('guest.book');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('guest.book');
  }

}
