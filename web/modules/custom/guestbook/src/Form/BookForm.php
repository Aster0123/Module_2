<?php

namespace Drupal\guestbook\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\file\Entity\File;

/**
 * Define class for the BookForm.
 */
class BookForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'guestbook_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your name:'),
      '#placeholder' => $this->t('The name must be in range from 2 to 100 symbols'),
      '#required' => TRUE,
      '#maxlength' => 100,
      '#ajax' => [
        'callback' => '::ajaxName',
        'event' => 'change',
        'progress' => [
          'type' => 'none',
        ],
      ],
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Your email:'),
      '#placeholder' => $this->t('example@email.com'),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::ajaxEmail',
        'event' => 'change',
        'progress' => [
          'type' => 'none',
        ],
      ],
    ];

    $form['number'] = [
      '#type' => 'tel',
      '#title' => $this->t('Your phone number:'),
      '#placeholder' => $this->t('+xxxxxxxxxxxx'),
      '#required' => TRUE,
      '#maxlength' => 13,
      '#ajax' => [
        'callback' => '::ajaxNumber',
        'event' => 'change',
        'progress' => [
          'type' => 'none',
        ],
      ],
    ];

    $form['avatar'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Please, upload your photo:'),
      '#description' => t('Please use only these extensions: jpeg, jpg, png'),
      '#upload_location' => 'public://avatar/',
      '#upload_validators' => [
        'file_validate_extensions' => ['jpeg jpg png'],
        'file_validate_size' => [2 * 1024 * 1024],
      ],
    ];

    $form['feedback'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Your feedback:'),
      '#rows' => 5,
      '#cols' => 15,
      '#placeholder' => $this->t('Please, write your feedback here'),
      '#required' => TRUE,
      '#maxlength' => 1000,
    ];

    $form['image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Please, upload your image:'),
      '#description' => t('Please use only these extensions: jpeg, jpg, png'),
      '#upload_location' => 'public://images/',
      '#upload_validators' => [
        'file_validate_extensions' => ['jpeg jpg png'],
        'file_validate_size' => [5 * 1024 * 1024],
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * Validation for the name field.
   */
  public function validateName(array &$form, FormStateInterface $form_state): bool {
    if ((mb_strlen($form_state->getValue('name')) < 2)) {
      return FALSE;
    }
    elseif ((mb_strlen($form_state->getValue('name')) > 100)) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Set messages of errors or success using ajax for the name field.
   */
  public function ajaxName(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    if ((mb_strlen($form_state->getValue('name')) < 2)) {
      $response->addCommand(new MessageCommand('Your name is too short', ".null", ['type' => 'error'], TRUE));
    }
    else {
      $response->addCommand(new MessageCommand('Your name is valid'));
    }
    return $response;
  }

  /**
   * Validation for the email field.
   */
  public function validateEmail(array &$form, FormStateInterface $form_state): bool {
    if (preg_match("/^[a-zA-Z_0-9.\-]+@[a-zA-Z_\-\.]+\.[a-zA-Z\.]{2,6}+$/", $form_state->getValue('email'))) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Set messages of errors or success using ajax for the email field.
   */
  public function ajaxEmail(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    if (preg_match("/^[a-zA-Z_0-9.\-]+@[a-zA-Z_\-\.]+\.[a-zA-Z\.]{2,6}+$/", $form_state->getValue('email'))) {
      $response->addCommand(new MessageCommand('Your email is valid'));
    }
    else {
      $response->addCommand(new MessageCommand('Your email is NOT valid', ".null", ['type' => 'error'], TRUE));
    }
    return $response;
  }

  /**
   * Validation for the number field.
   */
  public function validateNumber(array &$form, FormStateInterface $form_state): bool {
    if (preg_match("/[+][0-9]{10}/", $form_state->getValue('number'))) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Set messages of errors or success using ajax for the number field.
   */
  public function ajaxNumber(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    if (preg_match("/[+]380[0-9]{7}/", $form_state->getValue('number'))) {
      $response->addCommand(new MessageCommand('Your number is valid'));
    }
    else {
      $response->addCommand(new MessageCommand('Your number is wrong', ".null", ['type' => 'error'], TRUE));
    }
    return $response;
  }

  /**
   * Validation of the whole form using validation of certain fields.
   */
  public function validateForm(array &$form, FormStateInterface $form_state): bool {
    if ($this->validateName($form, $form_state) && $this->validateEmail($form, $form_state) && $this->validateNumber($form, $form_state)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritDoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Exception
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $avatar = $form_state->getValue('avatar');
    $image = $form_state->getValue('image');
    if ($this->validateForm($form, $form_state)) {
      if (!empty($avatar[0])) {
        $file_ava = File::load($avatar[0]);
        $file_ava->setPermanent();
        $file_ava->save();
      }
      else {
        $avatar[0] = 0;
      }
      if (!empty($image[0])) {
        $file_img = File::load($image[0]);
        $file_img->setPermanent();
        $file_img->save();
      }
      else {
        $image[0] = 0;
      }

      $cat = [
        'name' => $form_state->getValue('name'),
        'email' => $form_state->getValue('email'),
        'number' => $form_state->getValue('number'),
        'feedback' => $form_state->getValue('feedback'),
        'image' => $image[0],
        'avatar' => $avatar[0],
        'date' => time(),
      ];
      \Drupal::database()->insert('guestbook')->fields($cat)->execute();
      \Drupal::messenger()
        ->addStatus(t('Congratulations! You added your feedback!;)'));
    }
    else {
      \Drupal::messenger()
        ->addError(t('Your form is filled with errors.  Please fill in correctly'));
    }
  }

}
