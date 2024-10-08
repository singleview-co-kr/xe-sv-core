<?php
class seo extends ModuleObject
{
	public $SEO = array(
		'link' => array(),
		'meta' => array()
	);

	protected $canonical_url;

	private $triggers = array(
		array('display', 'seo', 'controller', 'triggerBeforeDisplay', 'before'),
		array('file.deleteFile', 'seo', 'controller', 'triggerAfterFileDeleteFile', 'after'),
		array('document.updateDocument', 'seo', 'controller', 'triggerAfterDocumentUpdateDocument', 'after'),
		array('document.deleteDocument', 'seo', 'controller', 'triggerAfterDocumentDeleteDocument', 'after'),
		array('module.dispAdditionSetup', 'seo', 'view', 'triggerDispSeoAdditionSetup', 'before')
	);

	public function getConfig()
	{
		$oModuleModel = getModel('module');
		$config = $oModuleModel->getModuleConfig('seo');

		require_once(_XE_PATH_ . 'libs/idna_convert/idna_convert.class.php');
		$IDN = new idna_convert(array('idn_version' => 2008));
		$request_uri = $IDN->encode(Context::get('request_uri'));

		if (!$config) $config = new stdClass;
		if (!$config->enable) $config->enable = 'Y';
		if (!$config->use_optimize_title) $config->use_optimize_title = 'N';
		if (!$config->ga_except_admin) $config->ga_except_admin = 'N';
		if (!$config->ga_track_subdomain) $config->ga_track_subdomain = 'N';
		if ($config->site_image) 
		{
			$config->site_image_url = $request_uri . 'files/attach/site_image/' . $config->site_image;

			$oCacheHandler = CacheHandler::getInstance('object', NULL, TRUE);
			if($oCacheHandler->isSupport()) {
				$site_image = false;
				$cache_key = 'seo:site_image';
				$site_image = $oCacheHandler->get($cache_key);
				if(!$site_image) {
					$path = _XE_PATH_ . 'files/attach/site_image/';
					list($width, $height) = @getimagesize($path . $config->site_image);
					$site_image_dimension = array(
						'width' => $width,
						'height' => $height
					);
					$cache_key = 'seo:site_image';
					$oCacheHandler->put($cache_key, $site_image_dimension);
				}
			}
		}

		return $config;
	}

	public function addMeta($property, $content, $attr_name = 'property')
	{
		if (!$content) return;

		foreach($this->SEO['meta'] as $nIdx=>$oProperty)
		{
			if($oProperty['property'] == $property && $oProperty['attr_name'] == $attr_name)
				return;
		}

		$oModuleController = getController('module');
		$oModuleController->replaceDefinedLangCode($content);
		if (!in_array($property, array('og:url'))) {
			$content = htmlspecialchars($content);
			$content = preg_replace("/(\s+)/", ' ', $content);
		}

		$this->SEO['meta'][] = array('property' => $property, 'content' => $content, 'attr_name' => $attr_name);
	}

	public function addLink($rel, $href)
	{
		if (!$href) return;

		$this->SEO['link'][] = array('rel' => $rel, 'href' => $href);
	}

	protected function applySEO()
	{
		$config = $this->getConfig();
		$logged_info = Context::get('logged_info');

		foreach ($this->SEO as $type => $list) {
			if (!$list || !count($list)) continue;

			foreach ($list as $val) {
				if ($type == 'meta') {
					$attr_name = $val['attr_name'];
					Context::addHtmlHeader('<meta ' . $attr_name . '="' . $val['property'] . '" content="' . $val['content'] . '" />');
				} elseif ($type == 'link') {
					Context::addHtmlHeader('<link rel="' . $val['rel'] . '" href="' . $val['href'] . '" />');
				}
			}
		}
	}

	function moduleInstall()
	{
		return $this->makeObject();
	}

	function checkUpdate()
	{
		$oModuleModel = getModel('module');

		$seo_config = $this->getConfig();

		if($seo_config->enable === 'Y') {
			foreach ($this->triggers as $trigger) {
				if (!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) return TRUE;
			}
		}

		return FALSE;
	}

	function moduleUpdate()
	{
		$oModuleModel = getModel('module');
		$oModuleController = getController('module');

		$seo_config = $this->getConfig();

		if($seo_config->enable === 'Y') {
			foreach ($this->triggers as $trigger) {
				if (!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) {
					$oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
				}
			}
		}

		return $this->makeObject(0, 'success_updated');
	}

	function moduleUninstall()
	{
		$oModuleController = getController('module');

		foreach ($this->triggers as $trigger) {
			$oModuleController->deleteTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
		}

		return $this->makeObject();
	}

	public function makeObject($code = 0, $message = 'success')
	{
		return class_exists('BaseObject') ? new BaseObject($code, $message) : new Object($code, $message);
	}
}
/* !End of file */
