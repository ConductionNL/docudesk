<?php

/**
 * @copyright Copyright (c) 2024 Conduction B.V. <info@conduction.nl>
 * @license EUPL-1.2
 *
 * DocuDesk is free software: you can redistribute it and/or modify
 * it under the terms of the European Union Public License (EUPL), 
 * version 1.2 only (the "Licence"), appearing in the file LICENSE
 * included in the packaging of this file.
 *
 * DocuDesk is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * European Union Public License for more details.
 *
 * You should have received a copy of the European Union Public License
 * along with DocuDesk. If not, see <https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12>.
 *
 * @category Controller
 * @package  OCA\DocuDesk\Controller
 * @author   Conduction B.V. <info@conduction.nl>
 * @license  EUPL-1.2
 * @link     https://github.com/conductionnl/docudesk
 */

namespace OCA\DocuDesk\Controller;

use OCP\IAppConfig;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IConfig;
use OCA\DocuDesk\Service\ObjectService;

/**
 * Class SettingsController
 *
 * Controller for handling settings-related operations in the DocuDesk app.
 *
 * @category Controller
 * @package  OCA\DocuDesk\Controller
 * @author   Conduction B.V. <info@conduction.nl>
 * @license  EUPL-1.2
 * @link     https://github.com/conductionnl/docudesk
 */
class SettingsController extends Controller
{
	/**
	 * SettingsController constructor.
	 *
	 * @param string        $appName       The name of the app
	 * @param IRequest      $request       The request object
	 * @param IAppConfig    $appConfig     The app configuration
	 * @param IConfig       $config        The system configuration
	 * @param ObjectService $objectService The object service
	 *
	 * @return void
	 */
	public function __construct(
		$appName,
		IRequest $request,
		private readonly IAppConfig $appConfig,
		private readonly IConfig $config,
		private readonly ObjectService $objectService
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Retrieve the current settings.
	 *
	 * @return JSONResponse JSON response containing the current settings
	 *
	 * @NoCSRFRequired
	 *
	 * @psalm-return JSONResponse
	 * @phpstan-return JSONResponse
	 */
	public function index(): JSONResponse
	{
		// Initialize the data array
		$data = [];
		$data['objectTypes'] = ['template', 'anonymization', 'report'];
		$data['openRegisters'] = false;
		$data['availableRegisters'] = [];

		// Check if the OpenRegister service is available
		$openRegisters = $this->objectService->getOpenRegisters();
		if ($openRegisters !== null) {
			$data['openRegisters'] = true;
			$data['availableRegisters'] = $openRegisters->getRegisters();
		}

		// Build defaults array dynamically based on object types
		$defaults = [];
		foreach ($data['objectTypes'] as $type) {
			$defaults["{$type}_source"] = 'internal';
			$defaults["{$type}_schema"] = '';
			$defaults["{$type}_register"] = '';
		}

		// Add system configuration values
		$data['presidioAnalyzerUrl'] = $this->config->getSystemValue(
			'docudesk_presidio_analyzer_url',
			'http://presidio-api:8080/analyze'
		);
		$data['presidioAnonymizerUrl'] = $this->config->getSystemValue(
			'docudesk_presidio_anonymizer_url',
			'http://presidio-api:8080/anonymize'
		);
		$data['confidenceThreshold'] = $this->config->getSystemValue('docudesk_confidence_threshold', 0.7);
		$data['enableReporting'] = $this->config->getSystemValue('docudesk_enable_reporting', true);
		$data['enableAnonymization'] = $this->config->getSystemValue('docudesk_enable_anonymization', true);
		$data['storeOriginalText'] = $this->config->getSystemValue('docudesk_store_original_text', true);

		// Get the current values for the object types from the configuration
		try {
			foreach ($defaults as $key => $defaultValue) {
				$data[$key] = $this->appConfig->getValueString($this->appName, $key, $defaultValue);
			}
			return new JSONResponse($data);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * Handle the post request to update settings.
	 *
	 * @return JSONResponse JSON response containing the updated settings
	 *
	 * @NoCSRFRequired
	 *
	 * @psalm-return JSONResponse
	 * @phpstan-return JSONResponse
	 */
	public function create(): JSONResponse
	{
		// Get all parameters from the request
		$data = $this->request->getParams();

		try {
			// Separate system config values from app config values
			$systemConfigKeys = [
				'presidioAnalyzerUrl' => 'docudesk_presidio_analyzer_url',
				'presidioAnonymizerUrl' => 'docudesk_presidio_anonymizer_url',
				'confidenceThreshold' => 'docudesk_confidence_threshold',
				'enableReporting' => 'docudesk_enable_reporting',
				'enableAnonymization' => 'docudesk_enable_anonymization',
				'storeOriginalText' => 'docudesk_store_original_text',
			];

			// Update system configuration values
			foreach ($systemConfigKeys as $requestKey => $configKey) {
				if (isset($data[$requestKey])) {
					$this->config->setSystemValue($configKey, $data[$requestKey]);
					// Add the updated value to the response
					$data[$requestKey] = $this->config->getSystemValue($configKey);
				}
			}

			// Update app configuration values (for object storage settings)
			foreach ($data as $key => $value) {
				// Skip system config keys that we've already processed
				if (in_array($key, array_keys($systemConfigKeys))) {
					continue;
				}
				
				$this->appConfig->setValueString($this->appName, $key, $value);
				// Retrieve the updated value to confirm the change
				$data[$key] = $this->appConfig->getValueString($this->appName, $key);
			}
			
			return new JSONResponse($data);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * Test the connection to the Presidio Analyzer API.
	 *
	 * @return JSONResponse JSON response containing the test result
	 *
	 * @NoCSRFRequired
	 *
	 * @psalm-return JSONResponse
	 * @phpstan-return JSONResponse
	 */
	public function testPresidioAnalyzer(): JSONResponse
	{
		$presidioUrl = $this->request->getParam('presidioUrl');
		
		if (empty($presidioUrl)) {
			return new JSONResponse(['error' => 'Presidio Analyzer URL is required'], 400);
		}
		
		try {
			// Create a test payload
			$payload = [
				'text' => 'John Smith lives in New York and his phone number is 212-555-1234.',
				'language' => 'en',
				'score_threshold' => 0.5,
				'return_decision_process' => false,
			];
			
			// Create a Guzzle client
			$client = new \GuzzleHttp\Client([
				'timeout' => 10,
				'connect_timeout' => 5,
			]);
			
			// Send a test request to the Presidio Analyzer API
			$response = $client->post($presidioUrl, [
				'json' => $payload,
				'headers' => [
					'Content-Type' => 'application/json',
					'Accept' => 'application/json',
				],
			]);
			
			// Check if the response is valid
			$statusCode = $response->getStatusCode();
			$body = json_decode($response->getBody()->getContents(), true);
			
			if ($statusCode === 200 && is_array($body)) {
				return new JSONResponse([
					'success' => true,
					'message' => 'Connection to Presidio Analyzer API successful',
					'entities_detected' => count($body['entities'] ?? []),
				]);
			} else {
				return new JSONResponse([
					'success' => false,
					'message' => 'Invalid response from Presidio Analyzer API',
				], 500);
			}
		} catch (\Exception $e) {
			return new JSONResponse([
				'success' => false,
				'message' => 'Failed to connect to Presidio Analyzer API: ' . $e->getMessage(),
			], 500);
		}
	}

	/**
	 * Test the connection to the Presidio Anonymizer API.
	 *
	 * @return JSONResponse JSON response containing the test result
	 *
	 * @NoCSRFRequired
	 *
	 * @psalm-return JSONResponse
	 * @phpstan-return JSONResponse
	 */
	public function testPresidioAnonymizer(): JSONResponse
	{
		$presidioUrl = $this->request->getParam('presidioUrl');
		
		if (empty($presidioUrl)) {
			return new JSONResponse(['error' => 'Presidio Anonymizer URL is required'], 400);
		}
		
		try {
			// Create a test payload
			$payload = [
				'text' => 'John Smith lives in New York and his phone number is 212-555-1234.',
				'analyzer_results' => [
					[
						'start' => 0,
						'end' => 10,
						'score' => 0.8,
						'entity_type' => 'PERSON',
					],
				],
				'anonymizers' => [
					'DEFAULT' => [
						'type' => 'replace',
						'new_value' => '[REDACTED]',
					],
				],
			];
			
			// Create a Guzzle client
			$client = new \GuzzleHttp\Client([
				'timeout' => 10,
				'connect_timeout' => 5,
			]);
			
			// Send a test request to the Presidio Anonymizer API
			$response = $client->post($presidioUrl, [
				'json' => $payload,
				'headers' => [
					'Content-Type' => 'application/json',
					'Accept' => 'application/json',
				],
			]);
			
			// Check if the response is valid
			$statusCode = $response->getStatusCode();
			$body = json_decode($response->getBody()->getContents(), true);
			
			if ($statusCode === 200 && is_array($body) && isset($body['text'])) {
				return new JSONResponse([
					'success' => true,
					'message' => 'Connection to Presidio Anonymizer API successful',
					'anonymized_text' => $body['text'],
				]);
			} else {
				return new JSONResponse([
					'success' => false,
					'message' => 'Invalid response from Presidio Anonymizer API',
				], 500);
			}
		} catch (\Exception $e) {
			return new JSONResponse([
				'success' => false,
				'message' => 'Failed to connect to Presidio Anonymizer API: ' . $e->getMessage(),
			], 500);
		}
	}

	/**
	 * Get API configuration.
	 *
	 * @return JSONResponse JSON response containing the API configuration
	 *
	 * @NoCSRFRequired
	 *
	 * @psalm-return JSONResponse
	 * @phpstan-return JSONResponse
	 */
	public function getApiConfig(): JSONResponse
	{
		try {
			$apiConfig = [
				'presidio' => [
					'url' => $this->config->getSystemValue('docudesk_presidio_analyzer_url', ''),
					'key' => $this->config->getSystemValue('docudesk_presidio_api_key', ''),
				],
				'chatgpt' => [
					'url' => $this->config->getSystemValue('docudesk_chatgpt_url', ''),
					'key' => $this->config->getSystemValue('docudesk_chatgpt_api_key', ''),
				],
				'nldocs' => [
					'url' => $this->config->getSystemValue('docudesk_nldocs_url', ''),
					'key' => $this->config->getSystemValue('docudesk_nldocs_api_key', ''),
				],
			];
			
			return new JSONResponse($apiConfig);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * Save API configuration.
	 *
	 * @return JSONResponse JSON response containing the updated API configuration
	 *
	 * @NoCSRFRequired
	 *
	 * @psalm-return JSONResponse
	 * @phpstan-return JSONResponse
	 */
	public function saveApiConfig(): JSONResponse
	{
		try {
			$apiConfig = json_decode($this->request->getBody(), true);
			
			if (!is_array($apiConfig)) {
				return new JSONResponse(['error' => 'Invalid API configuration'], 400);
			}
			
			// Update Presidio API configuration
			if (isset($apiConfig['presidio'])) {
				if (isset($apiConfig['presidio']['url'])) {
					$this->config->setSystemValue('docudesk_presidio_analyzer_url', $apiConfig['presidio']['url']);
				}
				if (isset($apiConfig['presidio']['key'])) {
					$this->config->setSystemValue('docudesk_presidio_api_key', $apiConfig['presidio']['key']);
				}
			}
			
			// Update ChatGPT API configuration
			if (isset($apiConfig['chatgpt'])) {
				if (isset($apiConfig['chatgpt']['url'])) {
					$this->config->setSystemValue('docudesk_chatgpt_url', $apiConfig['chatgpt']['url']);
				}
				if (isset($apiConfig['chatgpt']['key'])) {
					$this->config->setSystemValue('docudesk_chatgpt_api_key', $apiConfig['chatgpt']['key']);
				}
			}
			
			// Update NLDocs API configuration
			if (isset($apiConfig['nldocs'])) {
				if (isset($apiConfig['nldocs']['url'])) {
					$this->config->setSystemValue('docudesk_nldocs_url', $apiConfig['nldocs']['url']);
				}
				if (isset($apiConfig['nldocs']['key'])) {
					$this->config->setSystemValue('docudesk_nldocs_api_key', $apiConfig['nldocs']['key']);
				}
			}
			
			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}
}
