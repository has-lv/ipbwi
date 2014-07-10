<?php
	/**
	 * @author			Matthias Reuter
	 * @package			permissions
	 * @copyright		2007-2013 Matthias Reuter
	 * @link			http://ipbwi.com
	 * @since			2.0
	 * @license			http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
	 */

    namespace IPBWI;

	class ipbwi_permissions extends ipbwi {
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
		 * @desc			Finds out if a user has permission to do something...
		 * @param	string	$perm the permission to be worked out
		 * @param	int		$user the user to have permissions checked. If left blank, currently logged in user used.
		 * @return	bool	true if user has perm, otherwise false
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->permissions->has('g_access_cp',55);
		 * </code>
		 * @since			2.0
		 */
		public function has($perm,$user=false){
			if(substr($perm,0,2) != "g_"){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('badPermID'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			$perm = preg_replace('#[^a-z_]#','',$perm);
			$info = $this->ipbwi->member->info($user);
			if(!is_array($info)){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('badMemID'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			if($info[$perm]){
				return true;
			// Take a look at secondary groups
			}elseif(isset($info['mgroup_others'])){
				$info['mgroup_others'] = substr($info['mgroup_others'],1,strlen($info['mgroup_others'])-2);
				if($info['mgroup_others'] != ''){
					$this->ipbwi->ips_wrapper->DB->query('SELECT '.$perm.' FROM '.$this->ipbwi->board['sql_tbl_prefix'].'groups WHERE g_id IN("'.$info['mgroup_others'].'")');
					while($row = $this->ipbwi->ips_wrapper->DB->fetch()){
						if($row[$perm]){
							return true;
						}
					}
				}
			}
			return false;
		}
		/**
		 * @desc			Attempts to sort out the weird permissions array.
		 * @param	string	$permArray the permission array to be sorted
		 * @return	array	sorted permissions
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->permissions->sort(array('show' => '*','read' => '*','start' => '*','reply' => '*','upload' => '*','download' => '*'));
		 * </code>
		 * @since			2.0
		 */
		public function sort($permArray){
			$perms = unserialize(stripslashes($permArray));
			$fr['read_perms']   = $perms['read_perms'];
			$fr['reply_perms']  = $perms['reply_perms'];
			$fr['start_perms']  = $perms['start_perms'];
			$fr['upload_perms'] = $perms['upload_perms'];
			$fr['show_perms']   = $perms['show_perms'];
			return $fr;
		}
		/**
		 * @desc			Returns the best perms a user has for something...
		 * @param	string	$perm the permission to be worked out
		 * @param	int		$user the user to have permissions checked. if left blank, currently logged in user used.
		 * @param	bool	$zero if true, zero is best
		 * @return	array	best permissions
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->permissions->best(array('g_max_messages');
		 * </code>
		 * @since			2.0
		 */
		public function best($perm,$user=false,$zero=true){
			if(substr($perm,0,2) != 'g_'){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('badPermID'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			$perm = preg_replace('#[^a-z_]#','',$perm);
			$info = $this->ipbwi->member->info($user);
			if(!is_array($info)){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('badMemID'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			$init = $info[$perm];
			if(intval($init) == 0 && $zero){
				return 0;
			}
			// Take a look at secondary groups
			$info['mgroup_others'] = substr($info['mgroup_others'],1,strlen($info['mgroup_others'])-2);
			$info['mgroup_others'] = explode(',',$info['mgroup_others']);
			$info['mgroup_others'] = implode(',',$info['mgroup_others']);
			if($info['mgroup_others'] != ''){
				$this->ipbwi->ips_wrapper->DB->query('SELECT '.$perm.' FROM '.$this->ipbwi->board['sql_tbl_prefix'].'groups WHERE g_id IN("'.$info['mgroup_others'].'")');
				while($row = $this->ipbwi->ips_wrapper->DB->fetch()){
					if($row[$perm] > $init){
						$init = $row[$perm];
					}
					if(intval($init) == 0 && $zero){
						return 0;
					}
				}
			}
			return $init;
		}
		/**
		 * @desc			Creates a new permission set
		 * @param	string	$permName the title of the new permission
		 * @param	array	$perms array-matrix of permissions with the following syntax:
		 *					array-KEY (forum_ID) = array(
		 *													array-KEY (column) => array-VALUE(state)
		 *												)
		 *					syntax description:
		 *					array-KEY	'forum_ID'		allowed KEYs; the forum-IDs for the permission matrix
		 *					array-KEY	'column'		allowed KEYs: view, read, reply, start, upload, download
		 *					array-VALUE	'state'			allowed VALUEs: true (allowed) or false
		 * @return	int		permission set ID on success, otherwise false
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->permissions->create('Test Permission', array(
																	5 => array(
																					'view' => true,
																					'read' => true,
																					'reply' => false,
																					'start' => false,
																					'upload' => false,
																					'download' => true
																				),
																	7 => array(
																					'view' => true,
																					'read' => true,
																					'reply' => true,
																					'start' => true,
																					'upload' => true,
																					'download' => true
																				)
																)
										);
		 * </code>
		 * @since			3.4.3
		 */
		public function create($permName,$perms){
			$permXrel	= array(
				'view'		=> 'perm_view',
				'read'		=> 'perm_2',
				'reply'		=> 'perm_3',
				'start'		=> 'perm_4',
				'upload'	=> 'perm_5',
				'download'	=> 'perm_6'
			);
			
			// retrieve existing permission sets
			$sql = $this->ipbwi->ips_wrapper->DB->query('SELECT * FROM '.$this->ipbwi->board['sql_tbl_prefix'].'forum_perms');
			while($row = $this->ipbwi->ips_wrapper->DB->fetch($sql)){
				$currentPerms[]				= $row['perm_id'];
			}
			
			// prepare insert settings
			$settings = array(
				'perm_name'									=> $permName
			);
			
			// create new permission set
			if($this->ipbwi->ips_wrapper->DB->insert('forum_perms',$settings)){
				$newPermissionSetID							= $this->ipbwi->ips_wrapper->DB->getInsertId();

				// retrieve current permissions
				$currentPerm = array();
				$sql = $this->ipbwi->ips_wrapper->DB->query('SELECT * FROM '.$this->ipbwi->board['sql_tbl_prefix'].'permission_index');
				while($row = $this->ipbwi->ips_wrapper->DB->fetch($sql)){
					$currentPerm[$row['perm_type_id']]		= array(
						'perm_view'							=> (($row['perm_view'] == '*') ? '*' : explode(',',substr($row['perm_view'],1,-1))),
						'perm_2'							=> (($row['perm_2'] == '*') ? '*' : explode(',',substr($row['perm_2'],1,-1))),
						'perm_3'							=> (($row['perm_3'] == '*') ? '*' : explode(',',substr($row['perm_3'],1,-1))),
						'perm_4'							=> (($row['perm_4'] == '*') ? '*' : explode(',',substr($row['perm_4'],1,-1))),
						'perm_5'							=> (($row['perm_5'] == '*') ? '*' : explode(',',substr($row['perm_5'],1,-1))),
						'perm_6'							=> (($row['perm_6'] == '*') ? '*' : explode(',',substr($row['perm_6'],1,-1))),
					);
				}
				
				// roll out each new forum permission
				foreach($perms as $forum => $matrix){
					// check which forums (perm_type_id) need to be updated
					if(isset($currentPerm[$forum])){
						// now check which column needs to be updated
						foreach($matrix as $column => $state){
							// if column has the wildcard and state is set to true, keep the wildcard
							if($currentPerm[$forum][$permXrel[$column]] == '*' && $state === true){
								$newPerm[$permXrel[$column]]	= $currentPerm[$forum][$permXrel[$column]];
							// if column has the wildcard and state is set to false or 0, insert all permission IDs, except the new one
							}elseif($currentPerm[$forum][$permXrel[$column]] == '*' && $state == false){
								$newPerm[$permXrel[$column]]	= ','.implode($currentPerms).',';
							// if column has permission IDs and state is true
							}elseif(is_array($currentPerm[$forum][$permXrel[$column]]) && count($currentPerm[$forum][$permXrel[$column]]) > 0 && $state === true){
								$newPerm[$permXrel[$column]]	= $currentPerm[$forum][$permXrel[$column]];
								$newPerm[$permXrel[$column]][]	= $newPermissionSetID;
								$newPerm[$permXrel[$column]]	= ','.implode(',',$newPerm[$permXrel[$column]]).',';
							}
						}
					}
					
					// update forums (perm_type_id)
					if(is_array($newPerm) && count($newPerm) > 0){
						$this->ipbwi->ips_wrapper->DB->update('permission_index',$newPerm,'perm_type_id = "'.$forum.'"');
					}
				}
				
				return $newPermissionSetID;
			}else{
				return false;
			}
		}
	}
?>