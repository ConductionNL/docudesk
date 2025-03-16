<?php

namespace OCA\DocuDesk\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;

/**
 * Class Application
 *
 * @package OCA\LarpingApp\AppInfo
 */
class Application extends App implements IBootstrap
{
    public const APP_ID = 'docudesk';

    /**
     * Constructor
     *
     * @param array $urlParams
     */
    public function __construct(array $urlParams = [])
    {
        parent::__construct(appName: self::APP_ID, urlParams: $urlParams);
    }

    public function register(IRegistrationContext $context): void
    {
        // Register event listeners for file operations
        $context->registerEventListener(
            \OCP\Files\Events\Node\NodeCreatedEvent::class,
            \OCA\DocuDesk\EventListener\FileEventListener::class
        );
        $context->registerEventListener(
            \OCP\Files\Events\Node\NodeDeletedEvent::class,
            \OCA\DocuDesk\EventListener\FileEventListener::class
        );
        $context->registerEventListener(
            \OCP\Files\Events\Node\NodeTouchedEvent::class,
            \OCA\DocuDesk\EventListener\FileEventListener::class
        );
        $context->registerEventListener(
            \OCP\Files\Events\Node\NodeWrittenEvent::class,
            \OCA\DocuDesk\EventListener\FileEventListener::class
        );
        $context->registerEventListener(
            \OCP\Files\Events\Node\BeforeNodeCreatedEvent::class,
            \OCA\DocuDesk\EventListener\FileEventListener::class
        );
        $context->registerEventListener(
            \OCP\Files\Events\Node\BeforeNodeDeletedEvent::class,
            \OCA\DocuDesk\EventListener\FileEventListener::class
        );
        $context->registerEventListener(
            \OCP\Files\Events\Node\BeforeNodeWrittenEvent::class,
            \OCA\DocuDesk\EventListener\FileEventListener::class
        );

        // Register background jobs
        // $server = $context->getServerContainer();
        //$jobList = $server->getJobList();
        // $jobList->add(\OCA\DocuDesk\BackgroundJob\ProcessPendingReports::class);
    }

    public function boot(IBootContext $context): void
    {
    }
}
