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

class DifferentialRevision extends DifferentialDAO {

  protected $title;
  protected $status;

  protected $summary;
  protected $testPlan;
  protected $revertPlan;
  protected $blameRevision;

  protected $phid;
  protected $ownerPHID;

  protected $dateCommitted;

  protected $lineCount;
  
  public function getConfiguration() {
    return array(
      self::CONFIG_AUX_PHID => true,
    ) + parent::getConfiguration();
  }

  public function generatePHID() {
    return PhabricatorPHID::generateNewPHID('DREV');
  }
  
  public function loadRelationships() {
    
  }
  
  public function getReviewers() {
    return array();
  }
  
  public function getCCPHIDs() {
    return array();
  }

}