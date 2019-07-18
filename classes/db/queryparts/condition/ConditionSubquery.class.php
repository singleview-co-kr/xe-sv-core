<?php
/* Copyright (C) XEHub <https://www.xpressengine.com> */

/**
 * @author XEHub (developers@xpressengine.com)
 * @package /classes/db/queryparts/condition
 * @version 0.1
 */
class ConditionSubquery extends Condition
{

	/**
	 * constructor
	 * @param string $column_name
	 * @param mixed $argument
	 * @param string $operation
	 * @param string $pipe
	 * @return void
	 */
	function __construct($column_name, $argument, $operation, $pipe = "")
	{
		parent::__construct($column_name, $argument, $operation, $pipe);
		$this->_value = $this->argument->toString();
	}

}
/* End of file ConditionSubquery.class.php */
/* Location: ./classes/db/queryparts/condition/ConditionSubquery.class.php */
