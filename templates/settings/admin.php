<?php
use OCP\Util;

$appId = OCA\DocuDesk\AppInfo\Application::APP_ID;
Util::addScript($appId, $appId . '-settings');
Util::addStyle($appId, 'main');

?>

<div id="settings"></div>