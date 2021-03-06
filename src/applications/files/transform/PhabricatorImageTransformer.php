<?php

/*
 * Copyright 2011 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

final class PhabricatorImageTransformer {

  public function executeThumbTransform(
    PhabricatorFile $file,
    $x,
    $y) {

    $data = $file->loadFileData();
    $image = $this->crudelyScaleTo($data, $x, $y);

    return PhabricatorFile::newFromFileData(
      $image,
      array(
        'name' => 'thumb-'.$file->getName(),
      ));
  }

  public function executeProfileTransform(
    PhabricatorFile $file,
    $x,
    $min_y,
    $max_y) {

    $data = $file->loadFileData();
    $image = $this->crudelyCropTo($data, $x, $min_y, $max_y);

    return PhabricatorFile::newFromFileData(
      $image,
      array(
        'name' => 'profile-'.$file->getName(),
      ));
  }

  private function crudelyCropTo($data, $x, $min_y, $max_y) {
    $img = imagecreatefromstring($data);
    $sx = imagesx($img);
    $sy = imagesy($img);

    $scaled_y = ($x / $sx) * $sy;
    if ($scaled_y > $max_y) {
      // This image is very tall and thin.
      $scaled_y = $max_y;
    } else if ($scaled_y < $min_y) {
      // This image is very short and wide.
      $scaled_y = $min_y;
    }

    $img = $this->applyScaleTo(
      $img,
      $x,
      $scaled_y);

    return $this->saveImageDataInAnyFormat($img);
  }

  /**
   * Very crudely scale an image up or down to an exact size.
   */
  private function crudelyScaleTo($data, $dx, $dy) {
    $src = imagecreatefromstring($data);

    $dst = $this->applyScaleTo($src, $dx, $dy);

    return $this->saveImageDataInAnyFormat($dst);
  }

  private function applyScaleTo($src, $dx, $dy) {
    $x = imagesx($src);
    $y = imagesy($src);

    $scale = min($x / $dx, $y / $dy);
    $dst = imagecreatetruecolor($dx, $dy);

    // If we need to chop off some pixels, chop them off from the sides instead
    // of scaling in on <0, 0>.
    $sdx = $scale * $dx;
    $sdy = $scale * $dy;

    imagecopyresampled(
      $dst,
      $src,
      0, 0,
      ($x - $sdx) / 2,  ($y - $sdy) / 2,
      $dx, $dy,
      $sdx, $sdy);

    return $dst;
  }

  private function saveImageDataInAnyFormat($data) {
    $img = null;

    if (function_exists('imagejpeg')) {
      ob_start();
      imagejpeg($data);
      $img = ob_get_clean();
    } else if (function_exists('imagepng')) {
      ob_start();
      imagepng($data);
      $img = ob_get_clean();
    } else if (function_exists('imagegif')) {
      ob_start();
      imagegif($data);
      $img = ob_get_clean();
    } else {
      throw new Exception("No image generation functions exist!");
    }

    return $img;
  }

}
