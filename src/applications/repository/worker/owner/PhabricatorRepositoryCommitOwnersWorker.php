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

class PhabricatorRepositoryCommitOwnersWorker
  extends PhabricatorRepositoryCommitParserWorker {

  protected function parseCommit(
    PhabricatorRepository $repository,
    PhabricatorRepositoryCommit $commit) {

    $affected_paths = PhabricatorOwnerPathQuery::loadAffectedPaths(
      $repository, $commit);
    $affected_packages = PhabricatorOwnersPackage::loadAffectedPackages(
      $repository,
      $affected_paths);

    if (!$affected_packages) {
      return;
    }

    foreach ($affected_packages as $package) {
      $relationship = id(new PhabricatorOwnersPackageCommitRelationship())
        ->loadOneWhere('packagePHID=%s AND commitPHID=%s',
        $package->getPHID(),
        $commit->getPHID());

      // Don't update relationship if  it exists  already
      if (!$relationship) {
        if ($package->getAuditingEnabled()) {
          $reasons = $this->checkAuditReasons($commit, $package);
          if ($reasons) {
            $audit_status =
              PhabricatorAuditStatusConstants::AUDIT_REQUIRED;
          } else {
            $audit_status =
              PhabricatorAuditStatusConstants::AUDIT_NOT_REQUIRED;
          }
        } else {
          $reasons = array();
          $audit_status = PhabricatorAuditStatusConstants::NONE;
        }

        $relationship = new PhabricatorOwnersPackageCommitRelationship();
        $relationship->setPackagePHID($package->getPHID());
        $relationship->setCommitPHID($commit->getPHID());
        $relationship->setAuditReasons($reasons);
        $relationship->setAuditStatus($audit_status);

        $relationship->save();
      }
    }
  }

  private function checkAuditReasons(
    PhabricatorRepositoryCommit $commit,
    PhabricatorOwnersPackage $package) {

    $data = id(new PhabricatorRepositoryCommitData())->loadOneWhere(
      'commitID = %d',
      $commit->getID());

    $reasons = array();

    $commit_author_phid = $data->getCommitDetail('authorPHID');
    if (!$commit_author_phid) {
      $reasons[] = "Commit Author Not Recognized";
    }

    $revision_id = $data->getCommitDetail('differential.revisionID');
    $revision_author_phid = null;
    if ($revision_id) {
      $revision = id(new DifferentialRevision())->load($revision_id);
      if ($revision) {
        $revision->loadRelationships();
        $revision_author_phid = $revision->getAuthorPHID();
        $revision_reviewedby_phid = $revision->loadReviewedBy();
        $commit_reviewedby_phid = $data->getCommitDetail('reviewerPHID');
        if ($revision_author_phid !== $commit_author_phid) {
          $reasons[] = "Author Not Matching with Revision";
        }
        if ($revision_reviewedby_phid !== $commit_reviewedby_phid) {
          $reasons[] = "ReviewedBy Not Matching with Revision";
        }

      } else {
        $reasons[] = "Revision Not Found";
      }

    } else {
      $reasons[] = "No Revision Specified";
    }

    $owners = id(new PhabricatorOwnersOwner())->loadAllWhere(
      'packageID = %d',
      $package->getID());
    $owners_phids = mpull($owners, 'getUserPHID');

    if (!($commit_author_phid && in_array($commit_author_phid, $owners_phids) ||
        $revision_author_phid && in_array($revision_author_phid,
          $owners_phids))) {
      $reasons[] = "Owners Not Involved";
    }

    return $reasons;
  }

}
