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

class PhabricatorRepositoryCommit extends PhabricatorRepositoryDAO {

  protected $repositoryID;
  protected $phid;
  protected $commitIdentifier;
  protected $epoch;

  private $commitData;

  public function getConfiguration() {
    return array(
      self::CONFIG_AUX_PHID   => true,
      self::CONFIG_TIMESTAMPS => false,
    ) + parent::getConfiguration();
  }

  public function generatePHID() {
    return PhabricatorPHID::generateNewPHID(
      PhabricatorPHIDConstants::PHID_TYPE_CMIT);
  }

  public function attachCommitData(PhabricatorRepositoryCommitData $data) {
    $this->commitData = $data;
    return $this;
  }

  public function getCommitData() {
    if (!$this->commitData) {
      throw new Exception("Attach commit data with attachCommitData() first!");
    }
    return $this->commitData;
  }

}
