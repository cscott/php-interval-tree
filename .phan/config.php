<?php

$cfg = require __DIR__ . '/../vendor/mediawiki/mediawiki-phan-config/src/config.php';

// Pre-existing issues, to be fixed in a follow-up
$cfg['suppress_issue_types'] = array_merge( $cfg['suppress_issue_types'], [
	'MediaWikiNoEmptyIfDefined',
	'PhanGenericConstructorTypes',
	'PhanImpossibleTypeComparisonInLoop',
	'PhanParamSignatureMismatch',
	'PhanPluginDuplicateExpressionAssignmentOperation',
	'PhanTemplateTypeNotUsedInFunctionReturn',
	'PhanTypeMismatchArgumentNullable',
] );

return $cfg;
