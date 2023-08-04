<?php

namespace Drupal\sunlight_base;

class Sunlight {
  public static function formAddErrorClass(&$variables) {
    if (isset($variables["element"]["#errors"])) {
      $variables["attributes"]["class"][] = 'is-invalid';
    }
  }

  public static function formAddDescriptionClass(&$variables) {
    if (isset($variables["description"])) {
      $variables["description"]["attributes"]->addClass('form-text');
    }
  }

}