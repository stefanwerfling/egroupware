<?php
/**
 * TimeSheet - Projectmanager datasource
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @package timesheet
 * @copyright (c) 2005 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$ 
 */

include_once(EGW_INCLUDE_ROOT.'/projectmanager/inc/class.datasource.inc.php');
include_once(EGW_INCLUDE_ROOT.'/timesheet/inc/class.botimesheet.inc.php');

/**
 * Projectmanager DataSource for the TimeSheet
 */
class datasource_timesheet extends datasource
{
	/**
	 * Constructor
	 */
	function datasource_timesheet()
	{
		$this->datasource(TIMESHEET_APP);
		
		$this->valid = PM_REAL_START|PM_REAL_END|PM_USED_TIME|PM_USED_BUDGET|PM_USED_QUANTITY|
			PM_PRICELIST_ID|PM_UNITPRICE|PM_RESOURCES|PM_DETAILS|PM_COMPLETION;
	}
	
	/**
	 * get an entry from the underlaying app (if not given) and convert it into a datasource array
	 * 
	 * @param mixed $data_id id as used in the link-class for that app, or complete entry as array
	 * @return array/boolean array with the data supported by that source or false on error (eg. not found, not availible)
	 */
	function get($data_id)
	{
		// we use $GLOBALS['bocal'] as an already running instance is availible there
		if (!is_object($GLOBALS['botimesheet']))
		{
			$GLOBALS['botimesheet'] =& new botimesheet();
		}
		if (!is_array($data_id))
		{
			if (!(int) $data_id || !($data = $GLOBALS['botimesheet']->read((int) $data_id)))
			{
				return false;
			}
		}
		else
		{
			$data =& $data_id;
		}
		$ds = array(
			'pe_title'       => $GLOBALS['botimesheet']->link_title($data),
			'pe_real_start'  => $data['ts_start'],
			'pe_resources'   => array($data['ts_owner']),
			'pe_details'     => $data['ts_description'] ? nl2br($data['ts_description']) : '',
			'pl_id'          => $data['pl_id'],
			'pe_unitprice'   => $data['ts_unitprice'],
			'pe_used_quantity' => $data['ts_quantity'],
			'pe_used_budget' => $data['ts_quantity'] * $data['ts_unitprice'],
			'pe_completion'  => 100,
		);
		if ($data['ts_duration'])
		{
			$ds['pe_real_end'] = $data['ts_start'] + 60*$data['ts_duration'];
			$ds['pe_used_time'] = $data['ts_duration'];
		}
		if ($this->debug)
		{
			echo "datasource_timesheet($data_id) data="; _debug_array($data);
			echo "datasource="; _debug_array($ds);
		}
		return $ds;
	}

	/**
	 * Copy method (usally copies a projectelement) returns only false to prevent copying
	 *
	 * @param array $element source project element representing an InfoLog entry, $element['pe_app_id'] = info_id
	 * @param int $target target project id
	 * @param array $target_data=null data of target-project, atm not used by the infolog datasource
	 * @return array/boolean array(info_id,link_id) on success, false otherwise
	 */
	function copy($element,$target,$extra=null)
	{
		return false;
	}
	
	/**
	 * Delete the datasource of a project element
	 *
	 * @param int $id
	 * @return boolean true on success, false on error
	 */
	function delete($id)
	{
		if (!is_object($GLOBALS['botimesheet']))
		{
			$GLOBALS['botimesheet'] =& new botimesheet();
		}
		return $GLOBALS['botimesheet']->delete($id);
	}
}
