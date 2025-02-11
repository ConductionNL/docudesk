<?php

return [
	'routes' => [
		// Dashboard
		['name' => 'dashboard#page', 'url' => '/', 'verb' => 'GET'],
		// Object API routes	
		['name' => 'objects#index', 'url' => 'api/objects/{objectType}', 'verb' => 'GET'],
		['name' => 'objects#create', 'url' => 'api/objects/{objectType}', 'verb' => 'POST'],
		['name' => 'objects#show', 'url' => 'api/objects/{objectType}/{id}', 'verb' => 'GET'],
		['name' => 'objects#update', 'url' => 'api/objects/{objectType}/{id}', 'verb' => 'PUT'],
		['name' => 'objects#destroy', 'url' => 'api/objects/{objectType}/{id}', 'verb' => 'DELETE'],
		['name' => 'objects#lock', 'url' => 'api/objects/{objectType}/{id}/lock', 'verb' => 'POST'],
		['name' => 'objects#unlock', 'url' => 'api/objects/{objectType}/{id}/unlock', 'verb' => 'POST'],
		['name' => 'objects#revert', 'url' => 'api/objects/{objectType}/{id}/revert', 'verb' => 'POST'],
		['name' => 'objects#getAuditTrail', 'url' => 'api/objects/{objectType}/{id}/audit', 'verb' => 'GET'],
		['name' => 'objects#getRelations', 'url' => 'api/objects/{objectType}/{id}/relations', 'verb' => 'GET'],
		['name' => 'objects#getUses', 'url' => 'api/objects/{objectType}/{id}/uses', 'verb' => 'GET'],
		['name' => 'objects#getFiles', 'url' => 'api/objects/{objectType}/{id}/files', 'verb' => 'GET'],
	],
];
