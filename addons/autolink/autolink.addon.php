<?php
/* Copyright (C) XEHub <https://www.xpressengine.com> */

if(!defined('__XE__'))
	exit();

/**
 * @file autolink.addon.php
 * @author XEHub (developers@xpressengine.com)
 * @brief Automatic link add-on
 */
if($called_position == 'after_module_proc' && Context::getResponseMethod() == "HTML")
{
	Context::loadFile(array('./addons/autolink/autolink.js', 'body', '', null), true);
}
/* End of file autolink.addon.php */
/* Location: ./addons/autolink/autolink.addon.php */
