<?php

namespace Drupal\guestbook\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;

/**
 * Define controller class.
 */
class BookController extends ControllerBase {

  /**
   * Getting simple form from BookForm.
   */
  public function content() {
    $form = \Drupal::formBuilder()->getForm('Drupal\guestbook\Form\BookForm');
    $element = 'Please, add your feedback';
    return [
      '#theme' => 'guestbook',
      '#form' => $form,
      '#markup' => $element,
      '#list' => $this->guestbookTable(),
    ];
  }

  /**
   * Get data in the table from database.
   */
  public function guestbookTable(): array {
    $query = \Drupal::database();
    $result = $query->select('guestbook', 'booktb')
      ->fields('booktb', [
        'name',
        'email',
        'number',
        'feedback',
        'avatar',
        'image',
        'date',
        'id',
      ])
      ->orderBy('date', 'DESC')
      ->execute()->fetchAll();
    $data = [];
    foreach ($result as $row) {
      if (!$row->avatar == 0) {
        $file_ava = File::load($row->avatar);
        $ava_uri = $file_ava->getFileUri();
        $ava_is_set = TRUE;
      }
      else {
        $ava_uri = 'default_image.jpg';
        $ava_is_set = FALSE;
      }
      if (!$row->image == 0) {
        $file_img = File::load($row->image);
        $img_uri = $file_img->getFileUri();

      }
      else {
        $img_uri = 0;
      }

      $ava_img = [
        '#theme' => 'image_style',
        '#style_name' => 'wide',
        '#uri' => $ava_uri,
        '#title' => 'avatar',
        '#width' => 50,
        '#height' => 50,
        '#isset' => $ava_is_set,
      ];
      $img_img = [
        '#theme' => 'image_style',
        '#style_name' => 'wide',
        '#uri' => $img_uri,
        '#title' => 'image',
        '#width' => 200,
        '#height' => 200,
      ];

      $data[] = [
        'name' => $row->name,
        'email' => $row->email,
        'number' => $row->number,
        'feedback' => $row->feedback,
        'avatar' => $ava_img,
        'image' => $img_img,
        'date' => date('d-m-Y H:i:s', $row->date),
        'id' => $row->id,
      ];
    }
    return $data;
  }

}
