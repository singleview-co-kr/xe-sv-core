<?php
/* Copyright (C) XEHub <https://www.xehub.io> */

/**
 * XML Generater
 * @author XEHub (developers@xpressengine.com)
 */
class XmlGenerater
{

	/**
	 * Generate XML using given data
	 *
	 * @param array $params The data
	 * @return string Returns xml string
	 */
	public static function generate(&$params)
	{
		$xmlDoc = '<?xml version="1.0" encoding="utf-8" ?><methodCall><params>';
		if(!is_array($params))
		{
			return NULL;
		}

		$params["module"] = "resourceapi";
		foreach($params as $key => $val)
		{
			$xmlDoc .= sprintf("<%s><![CDATA[%s]]></%s>", $key, $val, $key);
		}
		$xmlDoc .= "</params></methodCall>";
		return $xmlDoc;
	}

	/**
	 * Request data to server and returns result
	 *
	 * @param array $params Request data
	 * @return object
	 */
	public static function getXmlDoc(&$params)
	{
		// $body = XmlGenerater::generate($params);
		// $buff = FileHandler::getRemoteResource(_XE_DOWNLOAD_SERVER_, $body, 3, "POST", "application/xml");
		$ch = curl_init(); // 리소스 초기화
		$url = _XE_SV_DOWNLOAD_SERVER_ . '?mode=applist';
	  
		// 옵션 설정
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$buff = curl_exec($ch); // 데이터 요청 후 수신
		curl_close($ch);  // 리소스 해제

		if(!$buff)
		{
			return;
		}

		$xml = new XeXmlParser();
		$xmlDoc = $xml->parse($buff);
		return $xmlDoc;
	}

}

/**
 * High class of the autoinstall module
 * @author XEHub (developers@xpressengine.com)
 */
class autoinstall extends ModuleObject
{

	/**
	 * Temporary directory path
	 */
	var $tmp_dir = './files/cache/autoinstall/';

	/**
	 * Constructor
	 *
	 * @return void
	 */
	function __construct()
	{
		$oModuleModel = getModel('module');
		$config = $oModuleModel->getModuleConfig('autoinstall');
		if($config->downloadServer != _XE_DOWNLOAD_SERVER_)
		{
			$this->stop('msg_not_match_server');
		}
	}

	/**
	 * For additional tasks required when installing
	 *
	 * @return BaseObject
	 */
	function moduleInstall()
	{
		$oModuleController = getController('module');

		$config = new stdClass;
		$config->downloadServer = _XE_DOWNLOAD_SERVER_;
		$oModuleController->insertModuleConfig('autoinstall', $config);
	}

	/**
	 * Method to check if installation is succeeded
	 *
	 * @return bool
	 */
	function checkUpdate()
	{
		$oDB = DB::getInstance();
		$oModuleModel = getModel('module');
		$oModuleController = getController('module');
		$version_update_id = implode('.', array(__CLASS__, __XE_VERSION__, 'updated'));
		if($oModuleModel->needUpdate($version_update_id))
		{
			if(!FileHandler::exists('./modules/autoinstall/schemas/autoinstall_installed_packages.xml')
				&& $oDB->isTableExists("autoinstall_installed_packages"))
			{
				return TRUE;
			}
			if(!FileHandler::exists('./modules/autoinstall/schemas/autoinstall_remote_categories.xml')
				&& $oDB->isTableExists("autoinstall_remote_categories"))
			{
				return TRUE;
			}

			// 2011.08.08 add column 'list_order' in ai_remote_categories
			if(!$oDB->isColumnExists('ai_remote_categories', 'list_order'))
			{
				return TRUE;
			}

			// 2011.08.08 set _XE_DOWNLOAD_SERVER_ at module config
			$config = $oModuleModel->getModuleConfig('autoinstall');
			if(!isset($config->downloadServer))
			{
				return TRUE;
			}

			// 2012.11.12 add column 'have_instance' in autoinstall_packages
			if(!$oDB->isColumnExists('autoinstall_packages', 'have_instance'))
			{
				return TRUE;
			}

			$oModuleController->insertUpdatedLog($version_update_id);
		}

		return FALSE;
	}

	/**
	 * Execute update
	 *
	 * @return BaseObject
	 */
	function moduleUpdate()
	{
		$oDB = DB::getInstance();
		$oModuleModel = getModel('module');
		$oModuleController = getController('module');
		$version_update_id = implode('.', array(__CLASS__, __XE_VERSION__, 'updated'));
		if($oModuleModel->needUpdate($version_update_id))
		{
			if(!FileHandler::exists('./modules/autoinstall/schemas/autoinstall_installed_packages.xml')
				&& $oDB->isTableExists("autoinstall_installed_packages"))
			{
				$oDB->dropTable("autoinstall_installed_packages");
			}
			if(!FileHandler::exists('./modules/autoinstall/schemas/autoinstall_remote_categories.xml')
				&& $oDB->isTableExists("autoinstall_remote_categories"))
			{
				$oDB->dropTable("autoinstall_remote_categories");
			}

			// 2011.08.08 add column 'list_order' in 'ai_remote_categories
			if(!$oDB->isColumnExists('ai_remote_categories', 'list_order'))
			{
				$oDB->addColumn('ai_remote_categories', 'list_order', 'number', 11, NULL, TRUE);
				$oDB->addIndex('ai_remote_categories', 'idx_list_order', array('list_order'));
			}

			// 2011. 08. 08 set _XE_DOWNLOAD_SERVER_ at module config
			$config = $oModuleModel->getModuleConfig('autoinstall');
			if(!isset($config->downloadServer))
			{
				$config->downloadServer = _XE_DOWNLOAD_SERVER_;
				$oModuleController->insertModuleConfig('autoinstall', $config);
			}

			// 2012.11.12 add column 'have_instance' in autoinstall_packages
			if(!$oDB->isColumnExists('autoinstall_packages', 'have_instance'))
			{
				$oDB->addColumn('autoinstall_packages', 'have_instance', 'char', '1', 'N', TRUE);
			}

			$oModuleController->insertUpdatedLog($version_update_id);
		}

		return new BaseObject(0, 'success_updated');
	}

	/**
	 * Re-generate the cache file
	 * @return BaseObject
	 */
	function recompileCache()
	{
		
	}

}
/* End of file autoinstall.class.php */
/* Location: ./modules/autoinstall/autoinstall.class.php */
