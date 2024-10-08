<?php
/* Copyright (C) XEHub <https://www.xehub.io> */
/**
 * @class  moduleView
 * @author XEHub (developers@xpressengine.com)
 * @brief view class of the module module
 */
class moduleView extends module
{
	/**
	 * @brief Initialization
	 */
	function init()
	{
		// Set the template path
		$this->setTemplatePath($this->module_path.'tpl');
	}

	/**
	 * @brief Display skin information
	 */
	function dispModuleSkinInfo()
	{
		$selected_module = Context::get('selected_module');
		$skin = urlencode(preg_replace("/[^a-z0-9-_]+/i", '', Context::get('skin')));
		// Get modules/skin information
		$module_path = sprintf("./modules/%s/", $selected_module);
		if(!is_dir($module_path)) $this->stop("msg_invalid_request");

		$skin_info_xml = sprintf("%sskins/%s/skin.xml", $module_path, $skin);
		if(!file_exists($skin_info_xml)) $this->stop("msg_invalid_request");

		$oModuleModel = getModel('module');
		$skin_info = $oModuleModel->loadSkinInfo($module_path, $skin);
		Context::set('skin_info', $skin_info);

		$this->setLayoutFile("popup_layout");
		$this->setTemplateFile("skin_info");
	}

	/**
	 * @brief Select a module
	 */
	function dispModuleSelectList()
	{
		if(!Context::get('is_logged')) return new BaseObject(-1, 'msg_not_permitted');

		$oModuleModel = getModel('module');
		$logged_info = Context::get('logged_info');
		$selected_module = Context::get('selected_module');
		$site_module_info = Context::get('site_module_info');
		$site_keyword = Context::get('site_keyword');

		$output = executeQuery('module.getSiteCount');
		$site_count = $output->data->count;
		Context::set('site_count', $site_count);

		$args = new stdClass();
		$module_category_exists = false;
		if($logged_info->is_admin == 'Y' && $site_keyword)
		{
			$args->site_keyword = $site_keyword;
		}
		else
		{
			$args->site_srl = (int)$site_module_info->site_srl;
			Context::set('site_keyword', null);
		}

		if($logged_info->is_admin == 'Y')
		{
			$module_category_exists = true;
		}

		// Get a list of modules at the site
		$mid_list = array();
		$output = executeQueryArray('module.getSiteModules', $args);
		if(!$output->data) $output->data = array();

		foreach($output->data as $key => $val)
		{
			$module = trim($val->module);
			if(!$module) continue;

			if(!$oModuleModel->getGrant($val, $logged_info)->access)
			{
				continue;
			}

			$category = $val->category;
			$obj = new stdClass();
			$obj->module_srl = $val->module_srl;
			$obj->browser_title = $val->browser_title;
			if(is_null($mid_list[$module]))
				$mid_list[$module] = new stdClass();
			$mid_list[$module]->list[$category][$val->mid] = $obj;

			if(!$selected_module) $selected_module = $module;
			if(!$mid_list[$module]->title)
			{
				$xml_info = $oModuleModel->getModuleInfoXml($module);
				$mid_list[$module]->title = $xml_info->title;
			}
		}

		Context::set('mid_list', $mid_list);
		Context::set('selected_module', $selected_module);
		Context::set('selected_mids', $mid_list[$selected_module]->list);
		Context::set('module_category_exists', $module_category_exists);

		$security = new Security();
		$security->encodeHTML('id', 'type', 'site_keyword');

		$this->setLayoutFile('popup_layout');
		$this->setTemplateFile('module_selector');
	}

	// See the file box
	function dispModuleFileBox()
	{
		$logged_info = Context::get('logged_info');
		if($logged_info->is_admin !='Y' && !$logged_info->is_site_admin) return new BaseObject(-1, 'msg_not_permitted');

		$input_name = Context::get('input');
		if(!preg_match('/^[a-z0-9_]+$/i', $input_name))
		{
			return new BaseObject(-1, 'msg_invalid_request');
		}

		if(!$input_name) return new BaseObject(-1, 'msg_not_permitted');

		$addscript = sprintf('<script>//<![CDATA[
				var selected_filebox_input_name = "%s";
				//]]></script>',$input_name);
		Context::addHtmlHeader($addscript);

		$oModuleModel = getModel('module');
		$output = $oModuleModel->getModuleFileBoxList();
		Context::set('filebox_list', $output->data);

		$filter = Context::get('filter');
		if($filter) Context::set('arrfilter',explode(',',$filter));

		Context::set('page_navigation', $output->page_navigation);
		$this->setLayoutFile('popup_layout');
		$this->setTemplateFile('filebox_list');
	}

	// Screen to add a file box
	function dispModuleFileBoxAdd()
	{
		$logged_info = Context::get('logged_info');
		if($logged_info->is_admin !='Y' && !$logged_info->is_site_admin) return new BaseObject(-1, 'msg_not_permitted');

		$filter = Context::get('filter');
		if($filter) Context::set('arrfilter',explode(',',$filter));

		$this->setLayoutFile('popup_layout');
		$this->setTemplateFile('filebox_add');
	}
}
/* End of file module.view.php */
/* Location: ./modules/module/module.view.php */
