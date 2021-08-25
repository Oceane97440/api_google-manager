<?php
require 'vendor/autoload.php';
use Google\AdsApi\AdManager\AdManagerSession;
use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\AdManager\v202108\ApiException;
use Google\AdsApi\AdManager\v202108\ServiceFactory;
use Google\AdsApi\Common\OAuth2TokenBuilder;

// Generate a refreshable OAuth2 credential for authentication.
$oAuth2Credential = (new OAuth2TokenBuilder())
    ->fromFile()
    ->build();
// Construct an API session configured from a properties file and the OAuth2
// credentials above.
$session = (new AdManagerSessionBuilder())
    ->fromFile()
    ->withOAuth2Credential($oAuth2Credential)
    ->build();

// Get a service.
$serviceFactory = new ServiceFactory();
$networkService = $serviceFactory->createNetworkService($session);

// Make a request
$network = $networkService->getCurrentNetwork();
printf(
    "Network with code %d and display name '%s' was found.\n",
    $network->getNetworkCode(),
    $network->getDisplayName()
);


$savedQueryId = 12304979660;
$statementBuilder = (new StatementBuilder())->where('id = :id')
->orderBy('id ASC')
->limit(1)
->withBindVariableValue('id', $savedQueryId);

$savedQueryPage = $reportService->getSavedQueriesByStatement(
$statementBuilder->toStatement()
);
$savedQuery = $savedQueryPage->getResults()[0];

if ($savedQuery->getIsCompatibleWithApiVersion() === false) {
throw new UnexpectedValueException(
    'The saved query is not compatible with this API version.'
);
}

$reportQuery = $savedQuery->getReportQuery();