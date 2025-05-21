<?php
/**
 * DocuDesk is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * DocuDesk is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * EUPL-1.2 License for more details.
 *
 * @category Service
 * @package  OCA\DocuDesk\Service
 * @author   Conduction B.V. <info@conduction.nl>
 * @license  EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 * @link     https://www.DocuDesk.nl
 */

namespace OCA\DocuDesk\Service;

use OCP\IAppConfig;
use OCP\IRequest;
use OCP\App\IAppManager;
use Psr\Container\ContainerInterface;
use OCP\AppFramework\Http\JSONResponse;
use OC_App;

/**
 * Service for handling settings-related operations.
 *
 * Provides functionality for retrieving, saving, and loading settings,
 * as well as managing configuration for different object types.
 */
class SettingsService
{

    /**
     * This property holds the name of the application, which is used for identification and configuration purposes.
     *
     * @var string $appName The name of the app.
     */
    private string $appName;

    /**
     * This constant represents the unique identifier for the OpenRegister application, used to check its installation and status.
     *
     * @var string $openRegisterAppId The ID of the OpenRegister app.
     */
    private const OPENREGISTER_APP_ID = 'docudesk';

    /**
     * This constant defines the minimum version of the OpenRegister application that is required for compatibility and functionality.
     *
     * @var string $minOpenRegisterVersion The minimum required version of OpenRegister.
     */
    private const MIN_OPENREGISTER_VERSION = '0.1.7';


    /**
     * SettingsService constructor.
     *
     * @param IAppConfig         $config     App configuration interface.
     * @param IRequest           $request    Request interface.
     * @param ContainerInterface $container  Container for dependency injection.
     * @param IAppManager        $appManager App manager interface.
     */
    public function __construct(private readonly IAppConfig $config, private readonly IRequest $request, private readonly ContainerInterface $container, private readonly IAppManager $appManager)
    {
        // Indulge in setting the application name for identification and configuration purposes.
        $this->appName = 'docudesk';

    }//end __construct()


    /**
     * Checks if OpenRegister is installed and meets version requirements.
     *
     * @param string|null $minVersion Minimum required version (e.g. '1.0.0').
     *
     * @return bool True if OpenRegister is installed and meets version requirements.
     */
    public function isOpenRegisterInstalled(?string $minVersion=self::MIN_OPENREGISTER_VERSION): bool
    {
        if ($this->appManager->isInstalled(self::OPENREGISTER_APP_ID) === false) {
            return false;
        }

        if ($minVersion === null) {
            return true;
        }

        $currentVersion = $this->appManager->getAppVersion(self::OPENREGISTER_APP_ID);
        return version_compare($currentVersion, $minVersion, '>=');

    }//end isOpenRegisterInstalled()


    /**
     * Checks if OpenRegister is enabled.
     *
     * @return bool True if OpenRegister is enabled.
     */
    public function isOpenRegisterEnabled(): bool
    {
        return $this->appManager->isEnabled(self::OPENREGISTER_APP_ID) === true;

    }//end isOpenRegisterEnabled()


    /**
     * Attempts to install or update OpenRegister.
     *
     * @param string|null $minVersion Minimum required version.
     *
     * @return bool True if installation/update was successful.
     * @throws \RuntimeException If installation/update fails.
     */
    public function installOrUpdateOpenRegister(?string $minVersion=self::MIN_OPENREGISTER_VERSION): bool
    {
        try {
            if ($this->isOpenRegisterInstalled($minVersion) === false) {
                throw new \RuntimeException(
                    'OpenRegister is not installed. Please install it from the Nextcloud App Store.'
                );
            }

            // Check if update is needed
            if ($minVersion !== null) {
                $currentVersion = $this->appManager->getAppVersion(self::OPENREGISTER_APP_ID);
                if (version_compare($currentVersion, $minVersion, '<') === true) {
                    throw new \RuntimeException(
                        'OpenRegister version '.$minVersion.' or higher is required. Please update it from the Nextcloud App Store.'
                    );
                }
            }

            // Ensure the app is enabled
            if ($this->isOpenRegisterEnabled() === false) {
                if ($this->appManager->enableApp(self::OPENREGISTER_APP_ID) === false) {
                    throw new \RuntimeException('Failed to enable OpenRegister');
                }
            }

            return true;
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to setup OpenRegister: '.$e->getMessage());
        }
    }//end installOrUpdateOpenRegister()


    /**
     * Attempts to auto-configure registers and schemas.
     *
     * @return array The updated configuration.
     * @throws \RuntimeException If auto-configuration fails.
     */
    public function autoConfigure(): array
    {
        try {
            $objectService = $this->getObjectService();
            $registers     = $objectService->getRegisters();

            if (empty($registers) === true) {
                return [];
            }

            $configuration = [];
            foreach ($this->getSettings()['objectTypes'] as $type) {
                // Try to find a register with a matching name.
                $matchingRegister = null;
                foreach ($registers as $register) {
                    if (stripos($register['title'], $type) !== false) {
                        $matchingRegister = $register;
                        break;
                    }
                }

                if ($matchingRegister !== null) {
                    $configuration["{$type}_register"] = $matchingRegister['id'];

                    // Try to find a matching schema.
                    if (empty($matchingRegister['schemas']) === false) {
                        foreach ($matchingRegister['schemas'] as $schema) {
                            if (stripos($schema['title'], $type) !== false) {
                                $configuration["{$type}_schema"] = $schema['id'];
                                break;
                            }
                        }
                    }
                }
            }//end foreach

            return $configuration;
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to auto-configure: '.$e->getMessage());
        }//end try

    }//end autoConfigure()


    /**
     * Initializes the app with all required components.
     *
     * @param string|null $minOpenRegisterVersion Minimum required OpenRegister version.
     *
     * @return array The initialization results.
     */
    public function initialize(?string $minOpenRegisterVersion=self::MIN_OPENREGISTER_VERSION): array
    {
        $results = [
            'openRegister'   => false,
            'autoConfigured' => false,
            'settingsLoaded' => false,
            'errors'         => [],
        ];

        try {
            // Check and install/update OpenRegister.
            if ($this->isOpenRegisterInstalled($minOpenRegisterVersion) === false) {
                $this->installOrUpdateOpenRegister($minOpenRegisterVersion);
            }

            $results['openRegister'] = true;

            // Auto-configure registers and schemas.
            $configuration = $this->autoConfigure();
            if (empty($configuration) === false) {
                $this->updateSettings($configuration);
                $results['autoConfigured'] = true;
            }

            // Load settings from file.
            $this->loadSettings();
            $results['settingsLoaded'] = true;
        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
        }//end try

        return $results;

    }//end initialize()


    /**
     * Attempts to retrieve the OpenRegister service from the container.
     *
     * @return \OCA\OpenRegister\Service\ObjectService|null The OpenRegister service if available, null otherwise.
     * @throws \RuntimeException If the service is not available.
     */
    public function getObjectService(): ?\OCA\OpenRegister\Service\ObjectService
    {
        if (in_array(needle: 'openregister', haystack: $this->appManager->getInstalledApps()) === true) {
            return $this->container->get('OCA\OpenRegister\Service\ObjectService');
        }

        throw new \RuntimeException('OpenRegister service is not available.');

    }//end getObjectService()


    /**
     * Attempts to retrieve the Configuration service from the container.
     *
     * @return \OCA\OpenRegister\Service\ConfigurationService|null The Configuration service if available, null otherwise.
     * @throws \RuntimeException If the service is not available.
     */
    public function getConfigurationService(): ?\OCA\OpenRegister\Service\ConfigurationService
    {
        if (in_array(needle: 'openregister', haystack: $this->appManager->getInstalledApps()) === true) {
            return $this->container->get('OCA\OpenRegister\Service\ConfigurationService');
        }

        throw new \RuntimeException('Configuration service is not available.');

    }//end getConfigurationService()


    /**
     * Retrieve the current settings.
     *
     * @return array The current settings configuration.
     * @throws \RuntimeException If settings retrieval fails.
     */
    public function getSettings(): array
    {
        // Initialize the data array.
        $data                       = [];
        $data['objectTypes']        = [
            "report",
            "anonymization",
            "template"
        ];
        $data['openRegisters']      = false;
        $data['availableRegisters'] = [];

        // Check if the OpenRegister service is available.
        try {
            $openRegisters = $this->getObjectService();
            if ($openRegisters !== null) {
                $data['openRegisters']      = true;
                $data['availableRegisters'] = $openRegisters->getRegisters();
            }
        } catch (\RuntimeException $e) {
            // Service not available, continue with default values.
        }

        // Build defaults array dynamically based on object types.
        $defaults = [];
        foreach ($data['objectTypes'] as $type) {
            // Always use openregister as source.
            $defaults["{$type}_source"]   = 'openregister';
            $defaults["{$type}_schema"]   = '';
            $defaults["{$type}_register"] = '';
        }

        // Get the current values for the object types from the configuration.
        try {
            foreach ($defaults as $key => $defaultValue) {
                $data['configuration'][$key] = $this->config->getValueString($this->appName, $key, $defaultValue);
            }

            return $data;
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to retrieve settings: '.$e->getMessage());
        }

    }//end getSettings()


    /**
     * Update the settings configuration.
     *
     * @param array $data The settings data to update.
     *
     * @return array The updated settings configuration.
     * @throws \RuntimeException If settings update fails.
     */
    public function updateSettings(array $data): array
    {
        try {
            // Update each setting in the configuration.
            foreach ($data as $key => $value) {
                $this->config->setValueString($this->appName, $key, $value);
                // Retrieve the updated value to confirm the change.
                $data[$key] = $this->config->getValueString($this->appName, $key);
            }

            return $data;
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to update settings: '.$e->getMessage());
        }//end try

    }//end updateSettings()


    /**
     * Load settings from the publication_register.json file.
     *
     * @return array The loaded settings configuration.
     * @throws \RuntimeException If settings loading fails.
     */
    public function loadSettings(): array
    {
        // Read the settings from the document_register.json file.
        $settingsFilePath = __DIR__.'/../Settings/document_register.json';
        $settings         = [];

        try {
            // Check if the file exists.
            if (file_exists($settingsFilePath) === false) {
                throw new \Exception('Settings file not found.');
            }

            // Get the contents of the file.
            $jsonContent = file_get_contents($settingsFilePath);

            // Decode the JSON content into an associative array.
            $settings = json_decode($jsonContent, true);

            // Check for JSON decoding errors.
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Error decoding JSON: '.json_last_error_msg());
            }

            $includeObjects = false;

            // Get the configuration service and import the settings.
            $configurationService = $this->getConfigurationService();
            return $configurationService->importFromJson($settings, $includeObjects);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to load settings: '.$e->getMessage());
        }//end try

    }//end loadSettings()


}//end class
