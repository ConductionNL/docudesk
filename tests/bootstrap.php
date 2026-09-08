<?php

/**
 * Bootstrap file for PHPUnit tests
 *
 * @category Test
 * @package  OCA\Filinq\Tests
 *
 * @author    Conduction Development Team <dev@conduction.nl>
 * @copyright 2025 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * @version GIT: <git-id>
 *
 * @link https://filinq.app
 */

declare(strict_types=1);

// Define that we're running PHPUnit.
define('PHPUNIT_RUN', 1);

// Include Composer's autoloader.
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Tell whether a Nextcloud root is an INSTALLED instance, not just a source tree.
 *
 * `lib/base.php` from a source tree that was never installed (the workspace
 * checkout above apps-extra/ has a 0-byte config/config.php) still declares
 * `OC` and builds `\OC::$server` before it throws "Not installed". That server
 * cannot be undone (`OC::$server` is a typed static), so from then on every
 * `\OC::$server->get()` in the code under test hits a container that knows
 * none of this app's registrations and autowires from scratch; constructor
 * cycles then recurse until memory runs out (19 GB and 6 GB of swap in one
 * openregister run on 2026-09-08). So the decision has to be made BEFORE
 * base.php is loaded, and the only cheap signal is the `installed` flag in
 * config/config.php.
 *
 * @param string $ncRoot Candidate Nextcloud root.
 *
 * @return bool True when config/config.php declares `installed => true`.
 */
function filinq_nc_root_is_installed(string $ncRoot): bool
{
	$configFile = $ncRoot . '/config/config.php';
	if (is_file($configFile) === false || filesize($configFile) === 0) {
		return false;
	}

	// The config file is a plain `$CONFIG = [...]` script; including it in a
	// closure keeps `$CONFIG` out of the global scope.
	$config = (static function () use ($configFile): array {
		$CONFIG = [];
		try {
			include $configFile;
		} catch (\Throwable) {
			return [];
		}

		if (is_array($CONFIG) === false) {
			return [];
		}

		return $CONFIG;
	})();

	return ($config['installed'] ?? false) === true;
}//end filinq_nc_root_is_installed()

// Bootstrap Nextcloud if not already done. Only an INSTALLED root is booted;
// a bare source tree runs in pure-unit mode with the composer autoload only.
// NC's tests/autoload.php requires lib/base.php itself, so it sits behind the
// same guard, and so do the OC_App calls that need a booted server.
if (!defined('OC_CONSOLE')) {
	$filinqNcRoot = realpath(__DIR__ . '/../../..');
	if ($filinqNcRoot !== false && file_exists($filinqNcRoot . '/lib/base.php') === true) {
		if (filinq_nc_root_is_installed($filinqNcRoot) === true) {
			try {
				require_once $filinqNcRoot . '/lib/base.php';

				// Load Test\TestCase and other NC test classes (NC convention).
				if (file_exists($filinqNcRoot . '/tests/autoload.php') === true) {
					require_once $filinqNcRoot . '/tests/autoload.php';
				}
			} catch (\Throwable $e) {
				// The root passed the installed check but base.php still
				// failed (unreachable database, broken app, ...). `OC::$server`
				// is a typed static that already holds a half-built container,
				// so falling through to "composer autoload only" would be a lie
				// that costs gigabytes. Stop the run and say why.
				fwrite(
					STDERR,
					sprintf(
						"[filinq/tests/bootstrap] Nextcloud root at %s could not be initialised (%s).\n"
						. "  A half-booted server cannot be undone, so the run stops here rather than pretending to be pure-unit.\n"
						. "  Fix the instance, or define OC_CONSOLE for pure-unit mode.\n",
						$filinqNcRoot,
						$e->getMessage()
					)
				);
				exit(1);
			}

			// Load all enabled apps, then our specific app, then clear hooks
			// for testing.
			\OC_App::loadApps();
			\OC_App::loadApp('filinq');
			OC_Hook::clear();
		} else {
			fwrite(
				STDERR,
				sprintf(
					"[filinq/tests/bootstrap] Nextcloud root at %s is not an installed instance (config/config.php lacks installed => true); "
					. "skipping lib/base.php and running with composer autoload only (pure-unit mode).\n",
					$filinqNcRoot
				)
			);
		}
	}

	unset($filinqNcRoot);
}
