<?php
/* Copyright (C) XEHub <https://www.xpressengine.com> */
class moduleMobile extends moduleObject
{
	function dispModuleChangeLang()
	{
		$this->setTemplatePath(sprintf("%stpl/",$this->module_path));
		$this->setTemplateFile('lang.html');
	}
}
/* End of file module.mobile.php */
/* Location: ./modules/module/module.mobile.php */
