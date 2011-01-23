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

class AphrontDialogView extends AphrontView {

  private $title;
  private $submitButton;
  private $cancelURI;
  private $submitURI;

  public function setSubmitURI($uri) {
    $this->submitURI = $uri;
    return $this;
  }

  public function setTitle($title) {
    $this->title = $title;
    return $this;
  }

  public function getTitle() {
    return $this->title;
  }

  public function addSubmitButton($text = 'Okay') {
    $this->submitButton = $text;
    return $this;
  }

  public function addCancelButton($uri) {
    $this->cancelURI = $uri;
    return $this;
  }

  final public function render() {

    $buttons = array();
    if ($this->submitButton) {
      $buttons[] =
        '<button name="__submit__">'.
          phutil_escape_html($this->submitButton).
        '</button>';
    }

    if ($this->cancelURI) {
      $buttons[] = phutil_render_tag(
        'a',
        array(
          'href'  => $this->cancelURI,
          'class' => 'button grey',
        ),
        'Cancel');
    }

    return phutil_render_tag(
      'form',
      array(
        'class'   => 'aphront-dialog-view',
        'action'  => $this->submitURI,
        'method'  => 'post',
      ),
      '<input type="hidden" name="__form__" value="1" />'.
      '<div class="aphront-dialog-head">'.
        phutil_escape_html($this->title).
      '</div>'.
      '<div class="aphront-dialog-body">'.
        $this->renderChildren().
      '</div>'.
      '<div class="aphront-dialog-tail">'.
        implode('', $buttons).
        '<div style="clear: both;"></div>'.
      '</div>');
  }

}