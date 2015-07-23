<?php
	/**
	 * @author			Matthias Reuter
	 * @package			group
	 * @copyright		2007-2013 Matthias Reuter
	 * @link			http://ipbwi.com/examples/group.php
	 * @since			2.0
	 * @license			http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
	 */

    namespace IPBWI;

	class ipbwi_group extends ipbwi {
		private $ipbwi			= null;
		/**
		 * @desc			Loads and checks different vars when class is initiating
		 * @author			Matthias Reuter
		 * @since			2.0
		 * @ignore
		 */
		public function __construct($ipbwi){
			// loads common classes
			$this->ipbwi = $ipbwi;
		}
		/**
		 * @desc			Returns information on a group.
		 * @param	int		$group Group ID. If $group is ommited, the last known group (of the last member) is used.
		 * @return	array	Group Information
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->group->info(5);
		 * </code>
		 * @since			2.0
		 */
		public function info($group=false){
			if(!$group){
				// No Group? Return current group info
				$group = $this->ipbwi->member->myInfo['member_group_id'];
			}
			// Check for cache - if exists don't bother getting it again
			if($cache = $this->ipbwi->cache->get('groupInfo', $group)){
				return $cache;
			}else{
				// Return group info if group given
				$this->ipbwi->ips_wrapper->DB->query('SELECT * FROM '.$this->ipbwi->board['sql_tbl_prefix'].'groups WHERE g_id="'.intval($group).'"');
				if($this->ipbwi->ips_wrapper->DB->getTotalRows()){
					$info = $this->ipbwi->ips_wrapper->DB->fetch();
					$this->ipbwi->cache->save('groupInfo', $group, $info);
					return $info;
				}else{
					return false;
				}
			}
		}
		/**
		 * @desc			Returns list of all usergroups.
		 * @return	array	Group list and information
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->group->getList();
		 * </code>
		 * @since			3.4.3
		 */
		public function getList(){
			// Check for cache - if exists don't bother getting it again
			if($cache = $this->ipbwi->cache->get('groupInfo', 'list')){
				return $cache;
			}else{
				// Return group info if group given
				$this->ipbwi->ips_wrapper->DB->query('SELECT * FROM '.$this->ipbwi->board['sql_tbl_prefix'].'groups');
				if($this->ipbwi->ips_wrapper->DB->getTotalRows()){
					while($info = $this->ipbwi->ips_wrapper->DB->fetch()){
						$groups[$info['g_id']] = $info;
						
						// save cache for each group
						$this->ipbwi->cache->save('groupInfo', $info['g_id'], $info);
					}
					// save cache for entire list
					$this->ipbwi->cache->save('groupInfo', 'list', $groups);
					
					return $groups;
				}else{
					return false;
				}
			}
		}
		/**
		 * @desc			Returns ID of a group name
		 * @return	int		Group ID
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->group->name2id('Test Group');
		 * </code>
		 * @since			3.6.6
		 */
		public function name2id($name){
			$sql = $this->ipbwi->ips_wrapper->DB->query('SELECT g_id FROM '.$this->ipbwi->board['sql_tbl_prefix'].'groups WHERE LOWER(g_title)="'.$this->ipbwi->ips_wrapper->DB->addSlashes($this->ipbwi->makeSafe(strtolower(trim($name)))).'"');
			if($row = $this->ipbwi->ips_wrapper->DB->fetch($sql)){
				return $row['g_id'];
			}else{
				return false;
			}
		}
		/**
		 * @desc			Creates a new usergroup
		 * @param	string	$title Title of the new usergroup
		 * @param	int		$perm_id Permission ID
		 * @param	array	$settings optional, Array of group settings, matches 'groups' table of IPB
		 * @param	int		$groupID optional, ID of group used as settings-draft for new group. You can override setting fields of your choice with $settings array.
		 * @return	bool	group ID success, otherwise false
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->group->create('group title', 3, array('g_view_board' => 1));
		 * $ipbwi->group->create('group title', 3, array('g_view_board' => 1), 4);
		 * $ipbwi->group->create('group title', 3, false, 4);
		 * </code>
		 * @since			3.4.3
		 */
		public function create($title,$perm_id,$settings=false,$groupID=false){
			// check wether group settings are based on another group
			if(isset($groupID) && intval($groupID) > 0){
				$settingsDraft = $this->ipbwi->group->info($groupID);
				unset($settingsDraft['g_id']);
				
				// merge settings, override double settings with $settings var
				$settings = array_merge($settingsDraft,$settings);
			}
			$settings['g_title'] 	= $title;
			$settings['g_perm_id'] 	= intval($perm_id);
			
			if($this->ipbwi->ips_wrapper->DB->insert('groups',$settings)){
				$this->ipbwi->ips_wrapper->cache->rebuildCache('group_cache');
				return $this->ipbwi->ips_wrapper->DB->getAffectedRows();
			}else{
				return false;
			}
		}
		/**
		 * @desc			Updates a usergroup
		 * @param	int		$groupID
		 * @param	array	$settings Array of group settings, matches 'groups' table of IPB
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->group->update(3, array('g_view_board' => 1));
		 * </code>
		 * @since			3.4.3
		 */
		public function update($groupID,$settings){
			if($info = $this->info($groupID)){
				$settings = array_merge($info,$settings);
			
				if($this->ipbwi->ips_wrapper->DB->update('groups',$settings,'g_id = "'.intval($groupID).'"')){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		/**
		 * @desc			Changes Member group to delivered group-id.
		 * @param	int		$group Group ID
		 * @param	int		$member Member ID. If no Member-ID is delivered, the currently logged in member will moved.
		 * @param	array	$extra secondary Group-IDs
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->group->change(5);
		 * $ipbwi->group->change(7,12,array(1,2,3,4));
		 * </code>
		 * @since			2.0
		 */
		public function change($group,$member=false,$extra=false){
			if(!$member){
				$member = $this->ipbwi->member->myInfo['member_id'];
			}
			if($extra !== false){
				$sql_extra = ',mgroup_others=",'.implode(',',$extra).',"';
			}else{
				$sql_extra = '';
			}
			
			$SQL = 'UPDATE '.$this->ipbwi->board['sql_tbl_prefix'].'members SET member_group_id="'.$group.'"'.$sql_extra.' WHERE member_id="'.intval($member).'"';

			if($this->ipbwi->ips_wrapper->DB->query($SQL)){
				$this->ipbwi->member->myInfo['member_group_id'] = $group;
				return true;
			}else{
				return false;
			}
			
			// set DB to WP again
			if(defined('IPBWIwpDB')){
				$wpdb->query('USE '.IPBWIwpDB);
			}
		}
		/**
		 * @desc			Returns whether a member is in the specified group(s).
		 * @param	int		$group Group ID or array of groups-ids separated with comma: 2,5,7
		 * @param	int		$member Member ID to find
		 * @param	bool	$extra Include secondary groups to test against?
		 * @return	mixed	Whether member is in group(s)
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->group->isInGroup(5);
		 * $ipbwi->group->isInGroup(7,12,true);
		 * </code>
		 * @since			2.0
		 */
		public function isInGroup($group, $member = false, $extra = true) {
			if (!is_array($group)){
				$group = explode(',', $group);
			}
			settype($group, 'array');
			if($member){
				$this->ipbwi->ips_wrapper->DB->query('SELECT member_group_id,mgroup_others FROM '.$this->ipbwi->board['sql_tbl_prefix'].'members WHERE member_id="'.$member.'"');
				if($row = $this->ipbwi->ips_wrapper->DB->fetch()){
					if(in_array($row['member_group_id'], $group)){
						return true;
					}
					if($extra){
						$others = explode(',',$row['mgroup_others']);
						foreach($others as $other){
							if(in_array($other,$group)){
								return true;
							}
						}
					}
				}
				return false;
			}else{
				if(in_array($this->ipbwi->member->myInfo['member_group_id'], $group)){
					return true;
				}else{
					$other = explode(',',$this->ipbwi->member->myInfo['mgroup_others']);
					if(is_array($other)) {
						foreach($other as $v) {
							if($v != '' && intval($v) > 0 && in_array($v, $group)) {
								return true;
							}
						}
					}

					return false;
				}
			}
		}
		/**
		 * @desc			Returns all groups of a member
		 * @param	int		$member optional Member ID to find
		 * @return	array	member groups splitted in primary and secondary
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->group->listMemberGroups();
		 * $ipbwi->group->listMemberGroups(7);
		 * </code>
		 * @since			2.0
		 */
		public function listMemberGroups($memberID = false) {
			if(intval($memberID) > 0){
				if(!($info = $this->ipbwi->member->info($memberID))){
					return false;
				}
			}else{
				$info = $this->ipbwi->member->myInfo;
			}
			$groups = array();
			$groups['primary']		= $info['member_group_id'];
			$groups['secondary']	= explode(',',$info['mgroup_others']);
			
			return $groups;
		}
	}
?>