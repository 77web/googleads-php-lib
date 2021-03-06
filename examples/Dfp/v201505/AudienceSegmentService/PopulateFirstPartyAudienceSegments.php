<?php
/**
 * This example populates a first party audience segment. To determine which
 * audience segments exist, run GetAllAudienceSegments.php.
 *
 * PHP version 5
 *
 * Copyright 2014, Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package    GoogleApiAdsDfp
 * @subpackage v201505
 * @category   WebServices
 * @copyright  2014, Google Inc. All Rights Reserved.
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License,
 *             Version 2.0
 */
error_reporting(E_STRICT | E_ALL);

// You can set the include path to src directory or reference
// DfpUser.php directly via require_once.
// $path = '/path/to/dfp_api_php_lib/src';
$path = dirname(__FILE__) . '/../../../../src';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

require_once 'Google/Api/Ads/Dfp/Lib/DfpUser.php';
require_once 'Google/Api/Ads/Dfp/Util/v201505/StatementBuilder.php';
require_once dirname(__FILE__) . '/../../../Common/ExampleUtils.php';

// Set the ID of the first party audience segment to populate.
$audienceSegmentId = 'INSERT_AUDIENCE_SEGMENT_ID_HERE';

try {
  // Get DfpUser from credentials in "../auth.ini"
  // relative to the DfpUser.php file's directory.
  $user = new DfpUser();

  // Log SOAP XML request and response.
  $user->LogDefaults();

  // Get the AudienceSegmentService.
  $audienceSegmentService =
      $user->GetService('AudienceSegmentService', 'v201505');

  // Create a statement to only select a specified first party audience
  // segment.
  $statementBuilder = new StatementBuilder();
  $statementBuilder->Where('id = :id and type = :type')
      ->OrderBy('id ASC')
      ->Limit(1)
      ->WithBindVariableValue('id', $audienceSegmentId)
      ->WithBindVariableValue('type', 'FIRST_PARTY');

  // Default for total result set size.
  $totalResultSetSize = 0;

  do {
    // Get audience segments by statement.
    $page = $audienceSegmentService->getAudienceSegmentsByStatement(
        $statementBuilder->ToStatement());

    // Display results.
    if (isset($page->results)) {
      $totalResultSetSize = $page->totalResultSetSize;
      $i = $page->startIndex;
      foreach ($page->results as $audienceSegment) {
        printf("%d) Audience segment with ID %d and name '%s' will be "
            . "populated.\n", $i++, $audienceSegment->id,
            $audienceSegment->name);
      }
    }

    $statementBuilder->IncreaseOffsetBy(StatementBuilder::SUGGESTED_PAGE_LIMIT);
  } while ($statementBuilder->GetOffset() < $totalResultSetSize);

  printf("Number of audience segments to be populated: %d\n",
      $totalResultSetSize);

  if ($totalResultSetSize > 0) {
    // Remove limit and offset from statement.
    $statementBuilder->RemoveLimitAndOffset();

    // Create action.
    $action = new PopulateAudienceSegments();

    // Perform action.
    $result = $audienceSegmentService->performAudienceSegmentAction($action,
        $statementBuilder->ToStatement());

    // Display results.
    if (isset($result) && $result->numChanges > 0) {
      printf("Number of audience segments populated: %d\n",
          $result->numChanges);
    } else {
      printf("No audience segments were populated.\n");
    }
  }
} catch (OAuth2Exception $e) {
  ExampleUtils::CheckForOAuth2Errors($e);
} catch (ValidationException $e) {
  ExampleUtils::CheckForOAuth2Errors($e);
} catch (Exception $e) {
  printf("%s\n", $e->getMessage());
}

