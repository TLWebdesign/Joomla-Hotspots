<?php
/**
 * @package    MatukioEvents
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-09-28
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe, Daniel Dimitrov. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Basic Api class providing default implementations
 *
 * @since  __DEPLOY_VERSION__
 */
class HotspotsApi
{
	/**
	 * Task to execute
	 *
	 * @var     string
	 *
	 * @since   6.1.2
	 */
	protected $task;

	/**
	 * Input
	 *
	 * @var     JInput
	 *
	 * @since   6.1.2
	 */
	protected $input;

	/**
	 * Input
	 *
	 * @var     JInput
	 *
	 * @since   6.1.2
	 */
	protected $db;

	/**
	 * The request
	 *
	 * @var     string
	 *
	 * @since   6.1.2
	 */
	protected $request;

	/**
	 * The default task (can be overwritten in sub class)
	 *
	 * @var     string
	 *
	 * @since   6.1.2
	 */
	protected $defaultTask = '_';

	/**
	 * The output, defaults to JSON
	 *
	 * @var     string
	 *
	 * @since   6.1.2
	 */
	protected $output = 'json';

	/**
	 * API constructor.
	 *
	 * @since  6.1.2
	 */
	public function __construct($config = array())
	{
		$this->input   = JFactory::getApplication()->input;
		$this->db      = JFactory::getDbo();
		$this->request = $this->input->getCmd('request');
	}

	/**
	 * Execute the given task in the class
	 *
	 * @param   string  $task  The task
	 *
	 * @return  void
	 *
	 * @since   6.1.2
	 */
	public function execute($task)
	{
		$this->task = $task;

		if (empty($task))
		{
			$this->task = $this->defaultTask;
		}

		$execute = strtolower($this->task);

		$result = $this->$execute();

        $appl = JFactory::getApplication();

		/* Allow plugins to manipulate the result */
        $appl->triggerEvent('onBeforeApiSubmit', array('com_hotspots.api', &$result, $task));

		// TODO move to presentation logic
		if ($this->output == 'json')
		{
			header('Content-type:application/json;charset=utf-8');

			echo json_encode($result);
		}
		else
		{
			echo $result;
		}

		jexit();
	}

	/**
	 * Default fallback function
	 *
	 * @return  mixed
	 *
	 * @since   6.1.2
	 */
	public function _()
	{
		return null;
	}


	/**
	 * Create an Error
	 *
	 * @param   int     $statusCode  Status Code
	 * @param   string  $message     Message
	 *
	 * @return  array
	 *
	 * @since   3.0.0
	 */
	protected function getError($statusCode, $message)
	{
		return array('error' => true, 'status' => $statusCode, 'message' => $message);
	}
}
