<?php
	/**
	 * @author			Matthias Reuter
	 * @package			calendar
	 * @copyright		2007-2014 Matthias Reuter
	 * @link			http://examples.ipbwi.com/downloads.php
	 * @since			3.6
	 * @license			http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
	 */

    namespace IPBWI;

	class ipbwi_calendar extends ipbwi {
		private $ipbwi			= null;
		public $installed		= false;
		public $online			= false;

		/**
		 * @desc			Loads and checks different vars when class is initiating
		 * @author			Matthias Reuter
		 * @since			3.6
		 * @ignore
		 */
		public function __construct($ipbwi){
			// loads common classes
			$this->ipbwi = $ipbwi;

			// check if addon is installed
			$query = $this->ipbwi->ips_wrapper->DB->query('SELECT conf_value,conf_default FROM '.$this->ipbwi->board['sql_tbl_prefix'].'core_sys_conf_settings WHERE conf_key="show_calendar"');
			if($this->ipbwi->ips_wrapper->DB->getTotalRows($query) != 0){
				$data = $this->ipbwi->ips_wrapper->DB->fetch($query);
				// retrieve Gallery URL
				$this->online = (($data['conf_value'] != '') ? $data['conf_value'] : $data['conf_default']);
				$this->installed = true;
			}
		}
		/**
		 * @desc			Returns calendar readable by the current member.
		 * @return	array	Readable category IDs
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->calendar->getViewable();
		 * </code>
		 * @since			3.6
		 */
		public function getViewable(){
			if($cache = $this->ipbwi->cache->get('calendarGetViewable', $this->ipbwi->member->myInfo['member_id'])){
				return $cache;
			}else{
				// get member's permission groups
				$memberPermissionGroups = $this->ipbwi->permissions->listMemberPermissionGroups();
			
				// retrieve readable IDs
				$sql = 'SELECT * FROM '.$this->ipbwi->board['sql_tbl_prefix'].'permission_index WHERE app="calendar" AND perm_type="calendar"';
				$this->ipbwi->ips_wrapper->DB->query($sql);
				$cats = array();
				while($row = $this->ipbwi->ips_wrapper->DB->fetch()){
					if(
					(
						count(array_intersect(explode(',',$row['perm_view']), $memberPermissionGroups)) > 0
						|| $row['perm_view'] == '*'
					)
					&&
					(
						$row['owner_only'] == 0
						&& $row['friend_only'] == 0
					)
					){
						$cats[$row['perm_type_id']] = $row['perm_type_id'];
					}
				}
				
				$this->ipbwi->cache->save('calendarGetViewable', $this->ipbwi->member->myInfo['member_id'], $cats);
				return $cats;
			}
		}
		/**
		 * @desc			lists categories from calendar
		 * @return	array	Image-Informations as multidimensional array
		 * @author			Matthias Reuter
		 * @since			3.6
		 */
		public function getListCategories($catIDs,$settings=array()){
			if($this->installed === true){
				if($catIDs == '*'){
					$viewable = $this->getViewable();
				}elseif(intval($catIDs) != 0){
					$viewable = (in_array(intval($catIDs),$this->getViewable()) ? array(intval($catIDs)) : false);
				}elseif(is_array($catIDs)){
					$viewable = array_intersect($this->getViewable(),$catIDs);
				}
				//  nothing found or no permissions
				if($viewable == false){
					return false;
				}else{
					$cats = implode(',',$viewable);
				}

				// retrieve detailed information
				$sql = 'SELECT * FROM '.$this->ipbwi->board['sql_tbl_prefix'].'cal_calendars WHERE cal_id IN ('.$cats.')';
				$this->ipbwi->ips_wrapper->DB->query($sql);
				$cats = array();
				while($row = $this->ipbwi->ips_wrapper->DB->fetch()){
					$cats[$row['cal_id']] = $row;
				}

				return $cats;
			}else{
				return false;
			}
		}
		/**
		 * @desc			lists downloads from IP.downloads
		 * @return	array	Image-Informations as multidimensional array
		 * @author			Matthias Reuter
		 * @since			3.6
		 */
		public function getList($catIDs='*',$settings=array()){
			if($this->installed === true){
				if($catIDs == '*'){
					$viewable = $this->getViewable();
				}elseif(intval($catIDs) != 0){
					$viewable = (in_array(intval($catIDs),$this->getViewable()) ? array(intval($catIDs)) : false);
				}elseif(is_array($catIDs)){
					$viewable = array_intersect($this->getViewable(),$catIDs);
				}
				//  nothing found or no permissions
				if($viewable == false){
					return false;
				}else{ // found
					$cats = implode(',',$viewable);
				}
				
				// filters
				if(isset($settings['start']) || isset($settings['limit'])){
					$startlimit = ' LIMIT '.intval($settings['start']).', '.intval($settings['limit']).'';
				}else{
					$startlimit = ' LIMIT 0,15';
				}
				
				if(isset($settings['order'])){
					$order = ' '.($settings['order'] == 'asc' ? 'ASC' : 'DESC');
				}else{
					$order = ' DESC';
				}
				
				if(isset($settings['orderby'])){
					$orderby = ' ORDER BY '.$settings['orderby'];
				}else{
					$orderby = ' ORDER BY event_start_date';
				}

				// retrieve detailed information
				$sql = 'SELECT * FROM '.$this->ipbwi->board['sql_tbl_prefix'].'cal_events WHERE event_calendar_id IN ('.$cats.') AND event_approved="1" AND event_start_date > "'.date('Y-m-d G:i:s',strtotime('-1 day', time())).'"'.$orderby.$order.$startlimit;
				$this->ipbwi->ips_wrapper->DB->query($sql);
				$events = array();
				while($row = $this->ipbwi->ips_wrapper->DB->fetch()){
						$events[$row['event_id']] = $row;
				}

				return $events;
			}else{
				return false;
			}
		}
	}
?>