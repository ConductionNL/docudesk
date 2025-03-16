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
 * @category Service
 * @package  OCA\DocuDesk\Service
 * @author   Conduction B.V. <info@conduction.nl>
 * @license  EUPL-1.2
 * @link     https://github.com/conductionnl/docudesk
 */

namespace OCA\DocuDesk\Service;

use Adbar\Dot;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use OCP\App\IAppManager;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Uid\Uuid;
use Psr\Container\ContainerInterface;
use OCP\IAppConfig;
use OCA\OpenRegister\Service\Exceptions\LockedException;
use OCA\OpenRegister\Service\Exceptions\NotAuthorizedException;
use OCA\OpenRegister\Service\Exceptions\NotFoundException;

/**
 * Service class for handling object-related operations
 *
 * This service provides methods for CRUD operations on objects, as well as
 * integration with OpenRegisters for external storage.
 */
class ObjectService
{
    /**
     * @var string $appName The name of the app 
     */
    private string $appName;

    /**
     * Constructor for ObjectService.
     *
     * @param ContainerInterface $container  The service container
     * @param IAppManager        $appManager The app manager
     * @param IAppConfig         $config     The app configuration
     *
     * @return void
     */
    public function __construct(
        private ContainerInterface $container,
        private readonly IAppManager $appManager,
        private readonly IAppConfig $config,
    ) {
        $this->appName = 'docudesk';
    }

    /**
     * Gets the appropriate mapper based on the object type.
     *
     * @param string $objectType The type of object to retrieve the mapper for.
     *
     * @return mixed The appropriate mapper.
     * @throws InvalidArgumentException If an unknown object type is provided.
     * @throws NotFoundExceptionInterface|ContainerExceptionInterface If OpenRegister service is not available or if register/schema is not configured.
     * @throws Exception
     */
    private function getMapper(string $objectType): mixed
    {
        $objectTypeLower = strtolower($objectType);

        // Get the source for the object type from the configuration
        $source = $this->config->getValueString($this->appName, $objectTypeLower . '_source', 'internal');

        // If the source is 'open_registers', use the OpenRegister service
        if ($source === 'openregister') {
            $openRegister = $this->getOpenRegisters();
            if ($openRegister === null) {
                throw new Exception("OpenRegister service not available");
            }
            $register = $this->config->getValueString($this->appName, $objectTypeLower . '_register', '');
            if (empty($register)) {
                throw new Exception("Register not configured for $objectType");
            }
            $schema = $this->config->getValueString($this->appName, $objectTypeLower . '_schema', '');
            if (empty($schema)) {
                throw new Exception("Schema not configured for $objectType");
            }
            return $openRegister->getMapper(register: $register, schema: $schema);
        }

        // If the source is internal, return the appropriate mapper based on the object type
        return match ($objectType) {
        default => throw new InvalidArgumentException("Unknown object type: $objectType"),
        };
    }

    /**
     * Gets an object based on the object type and id.
     *
     * @param string $objectType The type of object to retrieve.
     * @param string $id         The id of the object to retrieve.
     *
     * @return mixed The retrieved object.
     * @throws ContainerExceptionInterface|DoesNotExistException|MultipleObjectsReturnedException|NotFoundExceptionInterface
     * @throws InvalidArgumentException If extend is requested for non-OpenRegister objects
     */
    public function getObject(string $objectType, string $id, array $extend = []): mixed
    {
        // Clean up the id if it's a URI by getting only the last path part
        if (filter_var($id, FILTER_VALIDATE_URL)) {
            $parts = explode('/', rtrim($id, '/'));
            $id = end($parts);
        }

        // Get the appropriate mapper for the object type
        $mapper = $this->getMapper($objectType);

        // Check if extend is requested for non-OpenRegister objects
        if (!empty($extend) && !($mapper instanceof \OCA\OpenRegister\Service\ObjectService)) {
            throw new InvalidArgumentException('Extend functionality is only available for OpenRegister objects');
        }

        // Use the mapper to find and return the object
        $object = $mapper->find($id);

        // Convert the object to an array if it is not already an array
        if (is_object($object) && method_exists($object, 'jsonSerialize')) {
            return $object->jsonSerialize();
        }
        
        return is_array($object) ? $object : (array)$object;
    }

    /**
     * Gets objects based on the object type and various parameters.
     *
     * @param string     $objectType The type of objects to retrieve.
     * @param int|null   $limit      The maximum number of objects to retrieve.
     * @param int|null   $offset     The offset from which to start retrieving objects.
     * @param array|null $filters    Filters to apply to the query.
     * @param array|null $sort       Sorting parameters for the query.
     * @param array|null $extend     Additional parameters for extending the query.
     *
     * @return array The retrieved objects as arrays.
     * @throws ContainerExceptionInterface|DoesNotExistException|MultipleObjectsReturnedException|NotFoundExceptionInterface
     * @throws InvalidArgumentException If extend is requested for non-OpenRegister objects
     */
    public function getObjects(
        string $objectType,
        ?int $limit = null,
        ?int $offset = null,
        ?array $filters = [],
        ?array $sort = [],
        ?string $search = null,
        ?array $extend = []
    ): array {
        // Get the appropriate mapper for the object type
        $mapper = $this->getMapper($objectType);

        // Check if extend is requested for non-OpenRegister objects
        if (!empty($extend) && !($mapper instanceof \OCA\OpenRegister\Service\ObjectService)) {
            throw new InvalidArgumentException('Extend functionality is only available for OpenRegister objects');
        }

        // Use the mapper to find and return the objects based on the provided parameters
        $objects = $mapper->findAll(
            limit: $limit, 
            offset: $offset, 
            filters: $filters, 
            sort: $sort,
            search: $search,
            extend: $extend
        );

        // Convert entity objects to arrays using jsonSerialize
        return array_map(
            function ($object) {
                // If object is already an array, return it directly
                if (is_array($object)) {
                    return $object;
                }
                // Otherwise serialize the object
                return $object->jsonSerialize();
            }, $objects
        );
    }

    /**
     * Gets objects based on the object type, filters, search conditions, and other parameters.
     *
     * @param string     $objectType       The type of objects to retrieve.
     * @param int|null   $limit            The maximum number of objects to retrieve.
     * @param int|null   $offset           The offset from which to start retrieving objects.
     * @param array|null $filters          Filters to apply to the query.
     * @param array|null $searchConditions Search conditions to apply to the query.
     * @param array|null $searchParams     Search parameters for the query.
     * @param array|null $sort             Sorting parameters for the query.
     * @param array|null $extend           Additional parameters for extending the query.
     *
     * @return array The retrieved objects as arrays.
     * @throws ContainerExceptionInterface|DoesNotExistException|MultipleObjectsReturnedException|NotFoundExceptionInterface
     */
    public function getFacets(
        string $objectType,
        array $filters = [],
    ): array {
        // Get the appropriate mapper for the object type
        $mapper = $this->getMapper($objectType);

        // Use the mapper to find and return the objects based on the provided parameters
        if ($mapper instanceof \OCA\OpenRegister\Service\ObjectService === true) {
            return $mapper->getAggregations($filters);
        }

        return [];
    }

    /**
     * Gets multiple objects based on the object type and ids.
     *
     * @param string $objectType The type of objects to retrieve.
     * @param array  $ids        The ids of the objects to retrieve.
     *
     * @return array The retrieved objects.
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface If an unknown object type is provided.
     */
    public function getMultipleObjects(string $objectType, array $ids): array
    {
        // Process the ids
        $processedIds = array_map(
            function ($id) {
                if (is_object($id) && method_exists($id, 'getId')) {
                    return $id->getId();
                } elseif (is_array($id) && isset($id['id'])) {
                    return $id['id'];
                } else {
                    return $id;
                }
            }, $ids
        );

        // Clean up the ids if they are URIs
        $cleanedIds = array_map(
            function ($id) {
                // If the id is a URI, get only the last part of the path
                if (filter_var($id, FILTER_VALIDATE_URL)) {
                    $parts = explode('/', rtrim($id, '/'));
                    return end($parts);
                }
                return $id;
            }, $processedIds
        );

        // Get the appropriate mapper for the object type
        $mapper = $this->getMapper($objectType);

        // Use the mapper to find and return multiple objects based on the provided cleaned ids
        return $mapper->findMultiple($cleanedIds);
    }

    /**
     * Gets all objects of a specific type.
     *
     * @param string   $objectType The type of objects to retrieve.
     * @param int|null $limit      The maximum number of objects to retrieve.
     * @param int|null $offset     The offset from which to start retrieving objects.
     *
     * @return array The retrieved objects.
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface If an unknown object type is provided.
     */
    public function getAllObjects(string $objectType, ?int $limit = null, ?int $offset = null): array
    {
        // Get the appropriate mapper for the object type
        $mapper = $this->getMapper($objectType);

        // Use the mapper to find and return all objects of the specified type
        return $mapper->findAll($limit, $offset);
    }

    /**
     * Creates a new object or updates an existing one from an array of data.
     *
     * @param string $objectType    The type of object to create or update.
     * @param array  $object        The data to create or update the object from.
     * @param bool   $updateVersion If we should update the version or not, default = true.
     *
     * @return mixed The created or updated object.
     * @throws ContainerExceptionInterface|DoesNotExistException|MultipleObjectsReturnedException|NotFoundExceptionInterface
     */
    public function saveObject(string $objectType, array $object, array $extend = [], bool $updateVersion = true): mixed
    {
        // Get the appropriate mapper for the object type
        $mapper = $this->getMapper($objectType);
        // If the object has an id, update it; otherwise, create a new object
        if (isset($object['id']) === true) {
            return $mapper->updateFromArray(id: $object['id'], object: $object, updateVersion: $updateVersion, extend: $extend);
        }
        else {
            return $mapper->createFromArray(object: $object, extend: $extend);
        }
    }

    /**
     * Deletes an object based on the object type and id.
     *
     * @param string     $objectType The type of object to delete.
     * @param string|int $id         The id of the object to delete.
     *
     * @return bool True if the object was successfully deleted, false otherwise.
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|\OCP\DB\Exception If an unknown object type is provided.
     */
    public function deleteObject(string $objectType, string|int $id): bool
    {
        // Get the appropriate mapper for the object type
        $mapper = $this->getMapper($objectType);

        // Use the mapper to get and delete the object
        try {
            $object = $mapper->find($id);
            $mapper->delete($object);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Get the OpenRegisters service if available
     *
     * This method checks if the OpenRegister app is installed and returns
     * its ObjectService if available.
     *
     * @return \OCA\OpenRegister\Service\ObjectService|null The OpenRegisters service or null if not available
     *
     * @psalm-return \OCA\OpenRegister\Service\ObjectService|null
     * @phpstan-return \OCA\OpenRegister\Service\ObjectService|null
     */
    public function getOpenRegisters(): ?\OCA\OpenRegister\Service\ObjectService
    {
        if (in_array(needle: 'openregister', haystack: $this->appManager->getInstalledApps()) === true) {
            try {
                // Attempt to get the OpenRegister service from the container
                return $this->container->get('OCA\OpenRegister\Service\ObjectService');
            } catch (Exception $e) {
                // If the service is not available, return null
                return null;
            }
        }

        return null;
    }

    private function getCount(string $objectType, array $filters = []): int
    {
        $mapper = $this->getMapper($objectType);
        if($mapper instanceof \OCA\OpenRegister\Service\ObjectService === true) {
            return $mapper->count(filters: $filters);
        }

        return 0;
    }

    /**
     * Get a result array for a request based on the request and the object type.
     *
     * @param string $objectType    The type of object to retrieve
     * @param array  $requestParams The request parameters
     *
     * @return array The result array containing objects and total count
     * @throws ContainerExceptionInterface|DoesNotExistException|MultipleObjectsReturnedException|NotFoundExceptionInterface
     * @throws InvalidArgumentException If extend is requested for non-OpenRegister objects
     */
    public function getResultArrayForRequest(string $objectType, array $requestParams): array
    {
        // Extract specific parameters
        $limit = $requestParams['limit'] ?? $requestParams['_limit'] ?? null;
        $offset = $requestParams['offset'] ?? $requestParams['_offset'] ?? null;
        $order = $requestParams['order'] ?? $requestParams['_order'] ?? [];
        $extend = $requestParams['extend'] ?? $requestParams['_extend'] ?? null;
        $page = $requestParams['page'] ?? $requestParams['_page'] ?? null;
        $search = $requestParams['search'] ?? $requestParams['_search'] ?? null;
        // If page is set, calculate the offset
        if ($page !== null && isset($limit)) {
            $offset = $limit * ($page - 1);
        }

        // Ensure order and extend are arrays
        if (is_string($order)) {
            $order = array_map('trim', explode(',', $order));
        }
        if (is_string($extend)) {
            $extend = array_map('trim', explode(',', $extend));
        }

        // Remove unnecessary parameters from filters
        $filters = $requestParams;
        unset($filters['_route']); // Nextcloud automatically adds this
        unset($filters['_extend'], $filters['_limit'], $filters['_offset'], $filters['_order'], $filters['_page']);
        unset($filters['extend'], $filters['limit'], $filters['offset'], $filters['order'], $filters['page']);
        // Fetch objects based on filters and order
            
        $objects = $this->getObjects(
            objectType: $objectType,
            limit: $limit,
            offset: $offset,
            filters: $filters,
            sort: $order,
            extend: $extend,
            search: $search
        );

        $facets = $this->getFacets($objectType, $filters);

        return [
        'results' => $objects,
        'facets' => $facets,
        'total' => $this->getCount(objectType: $objectType, filters: $filters),
        ];
    }

    /**
     * Extends an entity with related objects based on the extend array.
     *
     * @param mixed $entity The entity to extend
     * @param array $extend An array of properties to extend
     *
     * @return array The extended entity as an array
     * @throws ContainerExceptionInterface|DoesNotExistException|MultipleObjectsReturnedException|NotFoundExceptionInterface If a property is not present on the entity
     */
    public function extendEntity(mixed $entity, array $extend): array
    {
        $surpressMapperError = false;
        // Convert the entity to an array if it's not already one
        $result = is_array($entity) ? $entity : $entity->jsonSerialize();

        if (in_array(needle: 'all', haystack: $extend) === true) {
            $extend = array_keys($entity);
            $surpressMapperError = true;
        }

        // Iterate through each property to be extended
        foreach ($extend as $property) {
            // Create a singular property name
            $singularProperty = rtrim($property, 's');

            // Check if property or singular property are keys in the array
            if (array_key_exists($property, $result)) {
                $value = $result[$property];
                if (empty($value)) {
                    continue;
                }
            } elseif (array_key_exists($singularProperty, $result)) {
                $value = $result[$singularProperty];
            } else {
                throw new Exception("Property '$property' or '$singularProperty' is not present in the entity.");
            }

            // Get a mapper for the property
            $propertyObject = $property;
            try {
                $mapper = $this->getMapper($property);
                $propertyObject = $singularProperty;
            } catch (Exception $e) {
                try {
                    $mapper = $this->getMapper($singularProperty);
                    $propertyObject = $singularProperty;
                } catch (Exception $e) {
                    // If still no mapper, throw a no mapper available error
                    if ($surpressMapperError === true) {
                        continue;
                    }
                    throw new Exception("No mapper available for property '$property'.");
                }
            }

            // Update the values
            if (is_array($value)) {
                // If the value is an array, get multiple related objects
                $result[$property] = $this->getMultipleObjects($propertyObject, $value);
            } else {
                // If the value is not an array, get a single related object
                $objectId = is_object($value) ? $value->getId() : $value;
                $result[$property] = $this->getObject($propertyObject, $objectId);
            }
        }

        // Return the extended entity as an array
        return $result;
    }

    /**
     * Get all relations for a specific object
     *
     * @param string $objectType The type of object to get relations for
     * @param string $id         The id of the object to get relations for
     * 
     * @return array The relations for the object
     * @throws Exception If OpenRegister service is not available
     */
    public function getRelations(string $objectType, string $id): array
    {
        // Get the mapper first
        $mapper = $this->getMapper($objectType);

        // Get audit trails from OpenRegister
        $auditTrails = $mapper->getRelations($id);

        return $auditTrails;
    }

    /**
     * Get all the useses that an specific object has
     *
     * @param string $objectType The type of object to get uses for
     * @param string $id         The id of the object to get uses for
     * 
     * @return array The uses for the object
     */
    public function getUses(string $objectType, string $id): array
    {
        $mapper = $this->getMapper($objectType);
        $uses = $mapper->getUses($id);
        return $uses;
    }

    /**
     * Get all files associated with a specific object
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function getFiles(string $objectType, string $id): array
    {
        // Get the mapper first
        $mapper = $this->getMapper($objectType);

        return $mapper->formatFiles($mapper->getFiles($id));
    }

    /**
     * Get all audit trails for a specific object
     *
     * @param string $objectType The type of object to get audit trails for
     * @param string $id         The id of the object to get audit trails for
     * 
     * @return array The audit trails for the object
     */
    public function getAuditTrail(string $objectType, string $id): array
    {
        // Get the mapper first
        $mapper = $this->getMapper($objectType);

        // Get audit trails from OpenRegister
        $auditTrails = $mapper->getAuditTrail($id);

        return $auditTrails;
    }

    /**
     * Lock an object
     *
     * @param  string      $objectType The type of object to lock
     * @param  string|int  $id         The id of the object to lock
     * @param  string|null $process    Optional process identifier
     * @param  int|null    $duration   Lock duration in seconds (default: 1 hour)
     * @return mixed The locked object
     */
    public function lockObject(string $objectType, string|int $id, ?string $process = null, ?int $duration = 3600): mixed
    {
        $mapper = $this->getMapper($objectType);
        return $mapper->lockObject($id, $process, $duration);
    }

    /**
     * Unlock an object
     *
     * @param  string     $objectType The type of object to unlock
     * @param  string|int $id         The id of the object to unlock
     * @return mixed The unlocked object
     */
    public function unlockObject(string $objectType, string|int $id): mixed
    {
        return $this->getMapper($objectType)->unlockObject($id);
    }

    /**
     * Check if an object is locked
     *
     * @param  string     $objectType The type of object to check
     * @param  string|int $id         The id of the object to check
     * @return bool True if object is locked, false otherwise
     */
    public function isLocked(string $objectType, string|int $id): bool
    {
        return $this->getMapper($objectType)->isLocked($id);
    }

    /**
     * Revert an object to a previous state
     *
     * @param  string               $objectType       The type of object to revert
     * @param  string|int           $id               The id of the object to revert
     * @param  DateTime|string|null $until            DateTime or AuditTrail ID to revert to
     * @param  bool                 $overwriteVersion Whether to overwrite the version or increment it
     * @return mixed The reverted object
     */
    public function revertObject(string $objectType, string|int $id, $until = null, bool $overwriteVersion = false): mixed
    {
        return $this->getMapper($objectType)->revertObject($id, $until, $overwriteVersion);
    }
}
