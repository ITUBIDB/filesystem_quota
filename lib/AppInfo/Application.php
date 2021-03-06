<?php
/**
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
 *
 * @copyright Copyright (c) 2017, ITU/BIDB
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */
namespace OCA\Filesystem_Quota\AppInfo;
use OCA\Filesystem_Quota\LdapService;
use OCA\Filesystem_Quota\QuotaService;
use OCA\Filesystem_Quota\Wrapper\FilesystemQuota;
use \OCP\AppFramework\App;
use OCP\AppFramework\IAppContainer;
class Application extends App {
	public function __construct(array $urlParams=array()){
		parent::__construct('filesystem_quota', $urlParams);
		$container = $this->getContainer();
		$container->registerService('QuotaService', function (IAppContainer $c) {
			return new QuotaService(\OC::$server->getHTTPClientService()->newClient());
		});
		$container->registerService('LdapService', function (IAppContainer $c) {
			return new LdapService($this->getContainer()->query('OCP\ISession'));
		});
	}
	/**
	 * Add filesystem quota wrapper for storages
	 */
	public function setupWrapper(){
		\OC\Files\Filesystem::addStorageWrapper(
			'oc_filesystem_quota',
			function ($mountPoint, $storage) {
				/**
				 * @var \OC\Files\Storage\Storage $storage
				 */
				if ($storage->instanceOfStorage('\OC\Files\Storage\Home')
					|| $storage->instanceOfStorage('\OC\Files\ObjectStore\HomeObjectStoreStorage')
				) {
					return new FilesystemQuota(array(
						'storage' => $storage,
						'quota_service' => $this->getContainer()->query('QuotaService'),
						'ldap_service' => $this->getContainer()->query('LdapService')
					));
				}
				return $storage;
			});
	}
}