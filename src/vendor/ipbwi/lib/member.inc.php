<?php
	/**
	 * @author			Matthias Reuter
	 * @package			member
	 * @copyright		2007-2013 Matthias Reuter
	 * @link			http://ipbwi.com/examples/member.php
	 * @since			2.0
	 * @license			http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
	 */

    namespace IPBWI;

	class ipbwi_member extends ipbwi {
		private $ipbwi			= null;
		private $loggedIn		= null;
		public	$myInfo			= null;
		/**
		 * @desc			Loads and checks different vars when class is initiating
		 * @author			Matthias Reuter
		 * @since			2.0
		 * @ignore
		 */
		public function __construct($ipbwi){
			// loads common classes
			$this->ipbwi = $ipbwi;
			
			if(defined('IPBWIboardDB') && $this->ipbwi->board['sql_tbl_prefix_ipbwi_updated'] != true){
				$this->ipbwi->board['sql_tbl_prefix']	= IPBWIboardDB.'.'.$this->ipbwi->board['sql_tbl_prefix'];
			}

			// checks if the current user is logged in
			if($this->ipbwi->ips_wrapper->loggedIn == 0){
				$this->loggedIn = false;
			}else{
				$this->loggedIn = true;
			}
			
			$this->myInfo = $this->ipbwi->ips_wrapper->myInfo();
		}
		/**
		 * @desc			Returns whether a member can access the board's Admin CP.
		 * @param	int		$userID User ID. If $userID is ommited, the last known member id is used.
		 * @return	bool	Whether currently logged in member can access ACP
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->isAdmin(5);
		 * </code>
		 * @since			2.0
		 */
		public function isAdmin($userID=false){
			return $this->ipbwi->permissions->has('g_access_cp',$userID);
		}
		/**
		 * @desc			Returns whether a member is a super moderator.
		 * @param	int		$userID User ID. If $userID is ommited, the last known member id is used.
		 * @return	bool	Whether currently logged in member is a Super Moderator
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->isSuperMod(5);
		 * </code>
		 * @since			2.0
		 */
		public function isSuperMod($userID=false){
			return $this->ipbwi->permissions->has('g_is_supmod',$userID);
		}
		/**
		 * @desc			Returns whether a member is logged in.
		 * @param	int		$userID User ID. If $userID is ommited, the last known member id is used.
		 * @return	bool	Whether currently logged in member is a Super Moderator
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->isLoggedIn(5);
		 * </code>
		 * @since			2.0
		 */
		public function isLoggedIn($userID=false){
			if($userID){
				if(in_array($userID,$this->listOnlineMembers())){
					return true;
				}else{
					return false;
				}
			}else{
				return $this->loggedIn;
			}
		}
		/**
		 * @desc			Grabs detailed information of a member.
		 * @param	int		$userID User ID. If $userID is ommited, the last known member id is used.
		 * @return	array	Member Information, or false on failure
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->info(5);
		 * </code>
		 * @since			2.0
		 */
		public function info($userID = false){
			if(!$userID){
				if($this->isLoggedIn()){
					// No UID? Return current user info
					$userID = $this->myInfo['member_id'];
				}else{
					// Return guest group info
					$sql = $this->ipbwi->ips_wrapper->DB->query('SELECT * FROM '.$this->ipbwi->board['sql_tbl_prefix'].'groups WHERE g_id="2"');
					if($this->ipbwi->ips_wrapper->DB->getTotalRows($sql) == 0){
						return false;
					}else{
						$info = $this->ipbwi->ips_wrapper->DB->fetch($sql);
						
						$this->ipbwi->ips_wrapper->parser->parse_smilies			= 1;
						$this->ipbwi->ips_wrapper->parser->parse_html				= 0;
						$this->ipbwi->ips_wrapper->parser->parse_nl2br				= 1;
						$this->ipbwi->ips_wrapper->parser->parse_bbcode				= 1;
						$this->ipbwi->ips_wrapper->parser->parsing_section			= 'topics';
						
						$allowedRichText = array('signature', 'pp_about_me');
						
						foreach($allowedRichText as $allowedRichText_field){
							if(isset($info[$allowedRichText_field])){
								$info[$allowedRichText_field]	= $this->ipbwi->ips_wrapper->parser->display($info[$allowedRichText_field]);
								$info[$allowedRichText_field]	= $this->ipbwi->ips_wrapper->DB->addSlashes($this->ipbwi->makeSafe($info[$allowedRichText_field]));
							}
						}
						
						$this->ipbwi->cache->save('memberInfo', $userID, $info);
						return $info;
					}
				}
			}
			// Check for cache - if exists don't bother getting it again
			if($cache = $this->ipbwi->cache->get('memberInfo',$userID)){
				return $cache;
			}else{
				// Return user info if UID given
				$info = \IPSMember::load($userID,'all');
				
				$this->ipbwi->cache->save('memberInfo', $userID, $info);
				return $info;
			}
		}
		/**
		 * @desc			Returns the HTML code to show a member's avatar.
		 * @param	int		$userID User ID. If $userID is ommited, the last known member id is used.
		 * @return	string	HTML Code for member's avatar, or false on failure
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->avatar(5);
		 * </code>
		 * @since			2.0
		 */
		public function avatar($userID = false){
			// No Member ID specified? Go for the current users UID.
			$member = $this->info($userID);
			$avatar = \IPSMember::buildAvatar($member);
			return $avatar;
		}
		/**
		 * @desc			Returns HTML code for member's photo.
		 * @param	int		$userID User ID. If $userID is ommited, the last known member id is used.
		 * @param	bool	$thumb true to activate thumbnail, otherwise false (default)
		 * @return	string	HTML code for member photo
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->photo(5,true);
		 * </code>
		 * @since			2.0
		 */
		public function photo($userID = false, $thumb = false, $hideDefault = false){
			$member	= $this->info($userID);
			$photo	= \IPSMember::buildProfilePhoto($member);

			if($hideDefault == false || ($hideDefault == true && (strpos($photo['pp_thumb_photo'],'default_large.png') === false && $photo['pp_thumb_photo'] != ''))){
				if($thumb === true && $photo['pp_thumb_photo'] != ''){
					$photohtml = '<img src="'.$photo['pp_thumb_photo'].'" width="'.$photo['pp_thumb_width'].'" height="'.$photo['pp_thumb_height'].'" alt="'.$this->id2displayname($userID).'" />';
				}elseif($photo['pp_main_photo'] != ''){
					$photohtml = '<img src="'.$photo['pp_main_photo'].'" width="'.$photo['pp_main_width'].'" height="'.$photo['pp_main_height'].'" alt="'.$this->id2displayname($userID).'" />';
				}else{
					return false;
				}
				return $photohtml;
			}else{
				return false;
			}
		}
		/**
		 * @desc			Gets the Member ID associated with a Member Name.
		 * @param	mixed	$names If you pass an array with names, the function also returns an array with each name beeing the key and the ID as its value. If a member name could not be found, the value will be set to false.
		 * @return	mixed	Single Member ID, assoc. array with id/name pairs, or false if the name(s) could not be found
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->name2id('name');
		 * $ipbwi->member->name2id(array('name1','name2'));
		 * </code>
		 * @since			2.0
		 */
		public function name2id($names){
			if(is_array($names)){
				foreach($names as $i => $j){
					$sql = $this->ipbwi->ips_wrapper->DB->query('SELECT member_id FROM '.$this->ipbwi->board['sql_tbl_prefix'].'members WHERE LOWER(name)="'.$this->ipbwi->ips_wrapper->DB->addSlashes($this->ipbwi->makeSafe(strtolower(trim($names)))).'"');
					if($row = $this->ipbwi->ips_wrapper->DB->fetch($sql)){
						$ids[$i] = $row['member_id'];
					}else{
						$ids[$i] = false;
					}
				}
				return $ids;
			}else{
				$sql = $this->ipbwi->ips_wrapper->DB->query('SELECT member_id FROM '.$this->ipbwi->board['sql_tbl_prefix'].'members WHERE LOWER(name)="'.$this->ipbwi->ips_wrapper->DB->addSlashes($this->ipbwi->makeSafe(strtolower(trim($names)))).'"');
				if($row = $this->ipbwi->ips_wrapper->DB->fetch($sql)){
					return $row['member_id'];
				}else{
					return false;
				}
			}
		}
		/**
		 * @desc			Gets the Member Name associated with a Member ID.
		 * @param	mixed	$userIDs Member Ids. If you pass an array with IDs, the function also returns an array with each ID beeing the key and the member name as its value. If a member ID could not be found, the value will be set to false.
		 * @return	mixed	Single member name, assoc. array with name/id pairs, or false if the ID(s) could not be found
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->id2name(55);
		 * $ipbwi->member->id2name(array(55,22,77));
		 * </code>
		 * @since			2.0
		 */
		public function id2name($userIDs){
			if(is_array($userIDs)){
				foreach($userIDs as $i => $j){
					$sql = $this->ipbwi->ips_wrapper->DB->query('SELECT name FROM '.$this->ipbwi->board['sql_tbl_prefix'].'members WHERE member_id="'.$this->ipbwi->ips_wrapper->DB->addSlashes($this->ipbwi->makeSafe(trim($userIDs))).'"');
					if($row = $this->ipbwi->ips_wrapper->DB->fetch($sql)){
						$names[$i] = $row['name'];
					}else{
						$names[$i] = false;
					}
				}
				return $ids;
			}else{
				$sql = $this->ipbwi->ips_wrapper->DB->query('SELECT name FROM '.$this->ipbwi->board['sql_tbl_prefix'].'members WHERE member_id="'.$this->ipbwi->ips_wrapper->DB->addSlashes($this->ipbwi->makeSafe(trim($userIDs))).'"');
				if($row = $this->ipbwi->ips_wrapper->DB->fetch($sql)){
					return $row['name'];
				}else{
					return false;
				}
			}
		}
		/**
		 * @desc			Gets the Member Name associated with a Member ID.
		 * @param	mixed	$userIDs Member Ids. If you pass an array with IDs, the function also returns an array with each ID beeing the key and the member name as its value. If a member ID could not be found, the value will be set to false.
		 * @return	mixed	Single member name, assoc. array with name/id pairs, or false if the ID(s) could not be found
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->id2name(55);
		 * $ipbwi->member->id2name(array(55,22,77));
		 * </code>
		 * @since			2.0
		 */
		public function id2seoname($userIDs){
			if(is_array($userIDs)){
				foreach($userIDs as $i => $j){
					$sql = $this->ipbwi->ips_wrapper->DB->query('SELECT members_seo_name FROM '.$this->ipbwi->board['sql_tbl_prefix'].'members WHERE member_id="'.$this->ipbwi->ips_wrapper->DB->addSlashes($this->ipbwi->makeSafe(trim($userIDs))).'"');
					if($row = $this->ipbwi->ips_wrapper->DB->fetch($sql)){
						$names[$i] = $row['members_seo_name'];
					}else{
						$names[$i] = false;
					}
				}
				return $ids;
			}else{
				$sql = $this->ipbwi->ips_wrapper->DB->query('SELECT members_seo_name FROM '.$this->ipbwi->board['sql_tbl_prefix'].'members WHERE member_id="'.$this->ipbwi->ips_wrapper->DB->addSlashes($this->ipbwi->makeSafe(trim($userIDs))).'"');
				if($row = $this->ipbwi->ips_wrapper->DB->fetch($sql)){
					return $row['members_seo_name'];
				}else{
					return false;
				}
			}
		}
		/**
		 * @desc			Gets the Member ID associated with a Display Name.
		 * @param	mixed	$names Member Names. If you pass an array with names, the function also returns an array with each name beeing the key and the ID as its value. If a member name could not be found, the value will be set to false.
		 * @return	mixed	Single Member ID, assoc. array with id/name pairs, or false if the name(s) could not be found
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->displayname2id('displayname');
		 * $ipbwi->member->displayname2id(array('displayname2','displayname2','displayname3'));
		 * </code>
		 * @since			2.0
		 */ 
		public function displayname2id($names){
			if(is_array($names)){
				foreach($names as $i => $j){
					$sql = $this->ipbwi->ips_wrapper->DB->query('SELECT member_id FROM '.$this->ipbwi->board['sql_tbl_prefix'].'members WHERE LOWER(members_display_name)="'.$this->ipbwi->ips_wrapper->DB->addSlashes($this->ipbwi->makeSafe(strtolower(trim($j)))).'"');
					if($row = $this->ipbwi->ips_wrapper->DB->fetch($sql)){
						$ids[$i] = $row['member_id'];
					}else{
						$ids[$i] = false;
					}
				}
				return $ids;
			}else{
				$sql = $this->ipbwi->ips_wrapper->DB->query('SELECT member_id FROM '.$this->ipbwi->board['sql_tbl_prefix'].'members WHERE LOWER(members_display_name)="'.$this->ipbwi->ips_wrapper->DB->addSlashes($this->ipbwi->makeSafe(strtolower(trim($names)))).'"');
				if($row = $this->ipbwi->ips_wrapper->DB->fetch($sql)){
					return $row['member_id'];
				}else{
					return false;
				}
			}
		}
		/**
		 * @desc			Gets the Member Display Name associated with a Member ID.
		 * @param	mixed	$userIDs Member IDs. If you pass an array with IDs, the function also returns an array with each ID beeing the key and the member name as its value. If a member ID could not be found, the value will be set to false.
		 * @return	mixed	Single member name, assoc. array with name/id pairs, or false if the ID(s) could not be found
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->id2displayname(55);
		 * $ipbwi->member->id2displayname(55,77,99));
		 * </code>
		 * @since			2.0
		 */
		public function id2displayname($userIDs){
			if(is_array($userIDs)){
				foreach($userIDs as $i => $j){
					$sql = $this->ipbwi->ips_wrapper->DB->query('SELECT members_display_name FROM '.$this->ipbwi->board['sql_tbl_prefix'].'members WHERE member_id="'.$this->ipbwi->ips_wrapper->DB->addSlashes($this->ipbwi->makeSafe(trim($userIDs))).'"');
					if($row = $this->ipbwi->ips_wrapper->DB->fetch($sql)){
						$names[$i] = $row['members_display_name'];
					}else{
						$names[$i] = false;
					}
				}
				return $ids;
			}else{
				$sql = $this->ipbwi->ips_wrapper->DB->query('SELECT members_display_name FROM '.$this->ipbwi->board['sql_tbl_prefix'].'members WHERE member_id="'.$this->ipbwi->ips_wrapper->DB->addSlashes($this->ipbwi->makeSafe(trim($userIDs))).'"');
				if($row = $this->ipbwi->ips_wrapper->DB->fetch($sql)){
					return $row['members_display_name'];
				}else{
					return false;
				}
			}
		}
		/**
		 * @desc			Gets the Member ID associated with a Member Email.
		 * @param	mixed	$emails Member Emails. If you pass an array with emails, the function also returns an array with each email beeing the key and the ID as its value. If a member email could not be found, the value will be set to false.
		 * @return	mixed	Single Member ID, assoc. array with id/email pairs, or false if the email(s) could not be found
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->email2id('email');
		 * $ipbwi->member->email2id(array('email1','email2','email3'));
		 * </code>
		 * @since			2.0
		 */
		public function email2id($emails){
			if(is_array($emails)){
				foreach($emails as $i => $j){
					$sql = $this->ipbwi->ips_wrapper->DB->query('SELECT member_id FROM '.$this->ipbwi->board['sql_tbl_prefix'].'members WHERE LOWER(email)="'.strtolower($j).'"');
					if($row = $this->ipbwi->ips_wrapper->DB->fetch($sql)){
						$ids[$i] = $row['member_id'];
					}else{
						$ids[$i] = false;
					}
				}
				return $ids;
			}else{
				$sql = $this->ipbwi->ips_wrapper->DB->query('SELECT member_id FROM '.$this->ipbwi->board['sql_tbl_prefix'].'members WHERE LOWER(email)="'.strtolower($emails).'"');
				if($row = $this->ipbwi->ips_wrapper->DB->fetch($sql)){
					return $row['member_id'];
				}else{
					return false;
				}
			}
		}
		/**
		 * @desc			Creates a new account and returns the member ID for further processing.
		 * @param	string	$userName Username
		 * @param	string	$password In plain text. Will be encrypted with md5()
		 * @param	string	$email Mail
		 * @param	array	$customFields Optional values for the (existing) custom profile fields.
		 * @param	boolean	$validate possible values: user, admin, admin_user, default: board settings
		 * @param	string	$displayName Display name, default: false (username = displayname)
		 * @param	boolean	$allowAdminMail Whether to allow emails from admins, default: true
		 * @param	boolean	$allowMemberMail Whether to allow emails from other members, default: false
		 * @param	boolean	$captchaCheck Decide if you want to protect registrations through captcha check. You have to use methods of antispam-class if you have captcha check enabled. Possible values: none, default (GD based), recaptcha. standard settings: board settings
		 * @return	long	New Member ID or false on failure
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->create('name', 'password', 'email@foo.com');
		 * $ipbwi->member->create('name', 'password', 'email@foo.com', array('field_1' => 'content of field 1', 'field_2' => 'content of field 2'), true, 'displayname', true);
		 * </code>
		 * @since			2.0
		 */
		public function create($userName, $password, $email, $customFields = array(), $validate = false, $displayName = false, $allowAdminMail = true, $allowMemberMail = false, $captchaCheck = false){
			$member							= $customFields;
			$member['UserName']				= $userName;
			$member['PassWord']				= $password;
			$member['PassWord_Check']		= $password;
			$member['EmailAddress']			= $email;
			$member['EmailAddress_two']		= $email;
			$member['members_display_name']	= $displayName ? $displayName : $userName;
			$member['allow_admin_mail']		= $allowAdminMail;
			$member['allow_member_mail']	= $allowMemberMail;
			if($validate !== false){
				$member['reg_auth_type']	= $validate;
			}else{
				$member['reg_auth_type']	= $this->ipbwi->getBoardVar('reg_auth_type');
			}
			$member['bot_antispam_type']	= (($captchaCheck != '') ? $captchaCheck : 'none');
			$member['agree_tos']			= 1;
			$member['qanda_id']				= $_POST['qanda_id'];
			$member['qa_answer']			= $_POST['qa_answer'];
			$this->ipbwi->ips_wrapper->register->create($member);

			if(isset($this->ipbwi->ips_wrapper->register->errors) && is_array($this->ipbwi->ips_wrapper->register->errors) && count($this->ipbwi->ips_wrapper->register->errors) > 0){
				foreach($this->ipbwi->ips_wrapper->register->errors as $field => $error){
					if($error[0] != null){
						$field = $this->ipbwi->getLibLang('reg_'.$field);
						$this->ipbwi->addSystemMessage('Error',$field.$error[0],'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					}elseif(is_array($error)){
						foreach($error as $text){
							$this->ipbwi->addSystemMessage('Error',$text,'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
						}
					}else{
						$this->ipbwi->addSystemMessage('Error',$error,'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					}
				}
				return false;
			}else{
				$this->myInfo = $this->info($this->email2id($member['EmailAddress']));
				return $this->email2id($member['EmailAddress']);
			}
		}
		/**
		 * @desc			Deletes a Member.
		 * @param	mixed	$userIDs Member(s) to be deleted. int for single member id, or array for a list of ids
		 * @param	string	$password Plaintext password of currently logged in member for more security
		 * @return	bool	true on success, false on failure
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->delete(55);
		 * $ipbwi->member->delete(array(55,22,77));
		 * </code>
		 * @since			2.0
		 */
		public function delete($userIDs=false,$password=false){
			$this->ipbwi->ips_wrapper->memberDelete($userIDs);
		}
		/**
		 * @desc			Gets the value of a custom profile field for a given member. If $userID is ommitted, the last known member id is used.
		 * @param	int		$fieldID Field ID (number) to retrieve.
		 * @param	int		$userID Member ID to read the custom profile field from.
		 * @return	string	Value of memberid's custom profile field field-id
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->customFieldValue(3,55);
		 * </code>
		 * @since			2.0
		 */
		public function customFieldValue($fieldID, $userID = false){
			$info = $this->info($userID);
			if(isset($info['field_' . $fieldID]) && $info['field_' . $fieldID]){
				$sql = $this->ipbwi->ips_wrapper->DB->query('SELECT field_'.intval($fieldID).' FROM '.$this->ipbwi->board['sql_tbl_prefix'].'pfields_content WHERE member_id="'.$info['member_id'].'"');
				if($field_info = $this->ipbwi->ips_wrapper->DB->fetch($sql)){
					return $info['field_'.$fieldID];
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		/**
		 * @desc			Updates the value of a custom profile field.
		 * @param	int		$ID Custom Profile field's ID
		 * @param	string	$newValue New Value for the field
		 * @param	bool	$bypassPerms Default: false=use board permissions to allow update, true=bypass permissions
		 * @param	bool	$memberID Member ID where the custom profile field should be updated. If no ID is delivered, the currently logged in user will be updated.
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter (make it possible to update other member custom pfields) <public@pc-intern.com> http://pc-intern.com | http://straightvisions.com
		 * @sample
		 * <code>
		 * $ipbwi->member->updateCustomField(2,'new value);
		 * $ipbwi->member->updateCustomField(1,'new value,true,55);
		 * </code>
		 * @since			2.0
		 */
		public function updateCustomField($ID, $newValue, $bypassPerms = false, $memberID = false){
			if(empty($memberID)){
				$memberID = $this->ipbwi->member->myInfo['member_id'];
			}
			$fieldinfo = $this->listCustomFields($memberID);
			if($info = $fieldinfo['field_' . $ID]){
				if($info['pf_member_edit'] OR $bypassPerms){
					if($info['pf_type'] == 'drop'){
						$allowed = array();
						$i = explode ('|', $info['pf_content']);
						foreach($i as $j){
							$k = explode ('=', $j);
							$allowed[] = $k['0'];
						}
						if(!in_array($newValue, $allowed)){
							$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('cfInvalidValue'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
							return false;
						}
					}
					if($info['pf_not_null'] AND !$newValue){
						$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('cfMustFillIn'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
						return false;
					}
					$this->ipbwi->ips_wrapper->DB->query('UPDATE '.$this->ipbwi->board['sql_tbl_prefix'].'pfields_content SET field_'.$ID.'="'.$newValue.'" WHERE member_id="'.$memberID.'"');
					return true;
				}else{
					$this->ipbwi->addSystemMessage('Error',sprintf($this->ipbwi->getLibLang('cfCantEdit'), $ID),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
			}else{
				$this->ipbwi->addSystemMessage('Error',sprintf($this->ipbwi->getLibLang('cfNotExist'), $ID),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
		}
		/**
		 * @desc			Gets a list of members who have defined custom profile field with a given value.
		 * @param	int		$fieldID Field ID (number) to retrieve.
		 * @param	string	$value Value we're looking for.
		 * @return	array	Array with members ID's
		 * @author			Mariusz Tarnaski
		 * @sample
		 * <code>
		 * $ipbwi->member->findMemberCustomFieldValue(5,'example');
		 * </code>
		 * @since			3.1.4
		 */
		public function findMemberCustomFieldValue($fieldID, $value = ''){
			$customFields = $this->listCustomFields();

			if(isset($customFields['field_' . $fieldID]) && $customFields['field_' . $fieldID]){
				$query = 'SELECT member_id FROM '.$this->ipbwi->board['sql_tbl_prefix'].'pfields_content WHERE field_'.intval($fieldID).'="'.$this->ipbwi->ips_wrapper->DB->addSlashes($this->ipbwi->makeSafe($value)).'"';
				$sql = $this->ipbwi->ips_wrapper->DB->query($query);
				$members = array();
				while($row = $this->ipbwi->ips_wrapper->DB->fetch($sql)){
					$members[] = $row['member_id'];
				}
				return $members;
			}else{
				return false;
			}
		}
		/**
		 * @desc			Grab a list of custom profile fields, and their properties.
		 * @return	array	custom profile fields and properties, otherwise false
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->listCustomFields();
		 * </code>
		 * @since			2.0
		 */
		public function listCustomFields(){
			// Check for cache...
			if($cache = $this->ipbwi->cache->get('listCustomFields', 1)){
				return $cache;
			}else{
				$sql = $this->ipbwi->ips_wrapper->DB->query('SELECT * FROM '.$this->ipbwi->board['sql_tbl_prefix'].'pfields_data ORDER BY pf_id');
				if($this->ipbwi->ips_wrapper->DB->getTotalRows($sql) == 0){
					return false;
				}else{
					while($info = $this->ipbwi->ips_wrapper->DB->fetch($sql)){
						$fields['field_'.$info['pf_id']] = $info;
					}
					$this->ipbwi->cache->save('listCustomFields', 1, $fields);
					return $fields;
				}
			}
		}
		/**
		 * @desc			Update properties of a member's record.
		 * @param	array	$update Associative array with fieldnames and values to update
		 * The following fields can be used in the $update array:
		 * + members_display_name
		 * + avatar_location
		 * + avatar_type
		 * + avatar_size
		 * + aim_name
		 * + icq_number
		 * + location
		 * + signature
		 * + website
		 * + yahoo
		 * + interests
		 * + msnname
		 * + integ_msg
		 * + title
		 * + allow_admin_mails
		 * + hide_email
		 * + email_pm
		 * + skin
		 * + language
		 * + view_sigs
		 * + view_img
		 * + view_avs
		 * + view_pop
		 * + bday_day
		 * + bday_month
		 * + bday_year
		 * + dst_in_use
		 * + email
		 * + pp_member_id
		 * + pp_profile_update
		 * + pp_bio_content
		 * + pp_last_visitors
		 * + pp_comment_count
		 * + pp_rating_hits
		 * + pp_rating_value
		 * + pp_rating_real
		 * + pp_friend_count
		 * + pp_main_photo
		 * + pp_main_width
		 * + pp_main_height
		 * + pp_thumb_photo
		 * + pp_thumb_width
		 * + pp_thumb_height
		 * + pp_gender
		 * + pp_setting_notify_comments
		 * + pp_setting_notify_friend
		 * + pp_setting_moderate_comments
		 * + pp_setting_moderate_friends
		 * + pp_setting_count_friends
		 * + pp_setting_count_comments
		 * + pp_setting_count_visitors
		 * + pp_profile_views
		 * @param	int		$userID The Member ID to update
		 * @param	int		$bypassPerms Default: false=use board permissions to allow update, true=bypass permissions
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->updateMember(array('website' => 'http://ipbwi.com', 'title' => 'mytitle'));
		 * $ipbwi->member->updateMember(array('website' => 'http://ipbwi.com'), 55, true);
		 * </code>
		 * @since			2.0
		 */
		public function updateMember($update = array(), $userID = false, $bypassPerms = false){
			// Do we have a member to update or not?
			if(!$userID){
				$userID = $this->myInfo['member_id'];
			}
			$userID = intval($userID);
			// Check we are logged in and can update profiles
			$info = $this->info($userID);
			if((!$this->isLoggedin() OR !$info['g_edit_profile']) AND !$bypassPerms){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			if(isset($update['members_display_name'])){
				$update['members_l_display_name'] = $update['members_display_name'];
			}
			if(isset($update['icq_number']) && strlen($update['icq_number']) > 9){
				$update['icq_number'] = false;
			}
			
			// make richtext fine for db save
			$this->ipbwi->ips_wrapper->parser->parse_bbcode		= $row['use_ibc'];
			$this->ipbwi->ips_wrapper->parser->strip_quotes		= 0;
			$this->ipbwi->ips_wrapper->parser->parse_nl2br		= 0;
			$this->ipbwi->ips_wrapper->parser->parse_html		= 0;
			$this->ipbwi->ips_wrapper->parser->parse_smilies	= 1;
			
			// Array of allowed array keys in $update we can update
			$allowed				= array('members_display_name','members_l_display_name','title','allow_admin_mails','hide_email', 'email_pm', 'skin','language','view_sigs', 'view_img', 'view_avs', 'view_pop', 'bday_day', 'bday_month', 'bday_year', 'dst_in_use','email');
			$ppAllowed				= array('signature', 'avatar_location', 'avatar_type', 'avatar_size', 'pp_member_id', 'pp_profile_update', 'pp_about_me', 'pp_last_visitors', 'pp_comment_count', 'pp_rating_hits', 'pp_rating_value', 'pp_rating_real', 'pp_friend_count', 'pp_main_photo', 'pp_main_width', 'pp_main_height', 'pp_thumb_photo', 'pp_thumb_width', 'pp_thumb_height', 'pp_gender', 'pp_setting_notify_comments', 'pp_setting_notify_friend', 'pp_setting_moderate_comments', 'pp_setting_moderate_friends', 'pp_setting_count_friends', 'pp_setting_count_comments', 'pp_setting_count_visitors', 'pp_profile_views');
			// Init
			$ppSQLupdate			= false;
			$ppsqlInsert['fields']	= false;
			$ppsqlInsert['values']	= false;
			$sql					= false;
			$ppSQL					= false;
			// If we have something to update
			if(count($update) > 0){
				foreach($update as $i => $j){
					if(in_array($i, $allowed)){
						// We can do this!!!!
						$update[$i] = $this->ipbwi->ips_wrapper->DB->addSlashes($this->ipbwi->makeSafe($j));
						$cache_update[$i] = $j;
						if($sql){
							$sql .= ','.$i.'="'.$update[$i].'"';
						}else{
							$sql .= $i.'="'.$update[$i].'"';
						}
					}
					if(in_array($i, $ppAllowed)){
						// We can do this!!!!
						$ppUpdate[$i] = $this->ipbwi->ips_wrapper->DB->addSlashes($this->ipbwi->makeSafe($j));
						$cache_ppUpdate[$i] = $j;
						if(isset($ppSQLupdate) && $ppSQLupdate != ''){
							$ppSQLupdate .= ','.$i.'="'.$ppUpdate[$i].'"';
						}else{
							$ppSQLupdate = $i.'="'.$ppUpdate[$i].'"';
						}
						$ppsqlInsert['fields'] .= ','.$i;
						$ppsqlInsert['values'] .= ',"'.$ppUpdate[$i].'"';
					}
				}
				// Check we have something to do again
				if($sql || $meSQL || $ppsqlInsert){
					// Update in Database
					if($sql){
						$query = 'UPDATE '.$this->ipbwi->board['sql_tbl_prefix'].'members SET '.$sql.' WHERE member_id="'.$userID.'"';
						$this->ipbwi->ips_wrapper->DB->query($query);
					}
					if($ppsqlInsert && $ppSQLupdate){
						$query = 'INSERT INTO '.$this->ipbwi->board['sql_tbl_prefix'].'profile_portal (pp_member_id'.$ppsqlInsert['fields'].') VALUES("'.$userID.'"'.$ppsqlInsert['values'].') ON DUPLICATE KEY UPDATE '.$ppSQLupdate;
						$this->ipbwi->ips_wrapper->DB->query($query);
					}
					// Update in memberInfo() cache.
					if(isset($cache_update)) $info = array_merge($info, $cache_update);
					if(isset($ppUpdate)) $info = array_merge($info, $cache_ppUpdate);
					$this->ipbwi->cache->save('memberInfo', $userID, $info);
					return true;
				}
			}
			return false;
		}
		/**
		 * @desc			Changes a user's password.
		 * @param	string	$newPass The new Member's password
		 * @param	string	$userID The Member's ID. If not set, the currently logged in member will be updated.
		 * @param	string	$currentPass Current password check for more security
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->updatePassword('new password');
		 * $ipbwi->member->updatePassword('new password',55,'old password');
		 * </code>
		 * @since			2.0
		 */
		public function updatePassword($newPass, $userID = false, $currentPass = false){
			// Do we have a member to update or not?
			if($userID){
				$userID = intval($userID);
	 		}else{
				$userID = $this->myInfo['member_id'];
			}
			// Check we are logged in
			$info = $this->info($userID);
			if(!$this->isLoggedIn() && empty($userID)){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			if(empty($newPass) OR strlen($newPass) < 3 OR strlen($newPass) > 32){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('accPass'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			// update password
			if($this->ipbwi->ips_wrapper->changePW($newPass,$info,$currentPass)){
				return true;
			}else{
				return false;
			}
		}
		/**
		 * @desc			Update the current member's signature
		 * @param	string	$newSig New signature text. HTML allowed as per board settings.
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->updateSig('[b]my sig[/b]');
		 * </code>
		 * @since			2.0
		 */
		public function updateSig($newSig){
			if(!$this->isLoggedIn()){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('membersOnly'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			if(strlen(strip_tags($newSig)) > $this->ipbwi->ips_wrapper->settings['signature_line_length']){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('sigTooLong'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			if($this->ipbwi->ips_wrapper->settings['sig_allow_ibc']){
				$this->ipbwi->ips_wrapper->parser->parse_html		= $this->ipbwi->ips_wrapper->settings['sig_allow_html'];
				$this->ipbwi->ips_wrapper->parser->parse_bbcode		= $this->ipbwi->ips_wrapper->settings['sig_allow_ibc'];
				$this->ipbwi->ips_wrapper->parser->strip_quotes		= 1;
				$this->ipbwi->ips_wrapper->parser->parse_nl2br		= 1;
				$newSig = $this->ipbwi->ips_wrapper->editor->process(stripslashes($newSig));
			}
			$this->ipbwi->ips_wrapper->DB->query('UPDATE '.$this->ipbwi->board['sql_tbl_prefix'].'profile_portal SET signature="'.$this->ipbwi->ips_wrapper->DB->addSlashes($this->ipbwi->makeSafe($newSig)).'" WHERE pp_member_id="'.$this->ipbwi->member->myInfo['member_id'].'"');
			return true;
		}
		/**
		 * @desc			Update current member's photograph. $_POST field 'upload_photo' must contain uploaded photo file
		 * @param	int		optional, member id
		 * @return	array  	[ error (error message), status (status message [ok/fail] ) ]
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->updatePhoto(); // uploads a new profile picture for currently logged in member
		 * $ipbwi->member->updatePhoto(55); // set member id
		 * </code>
		 * @since			2.0
		 */
		public function updatePhoto($memberID=false){
			if(!empty($_FILES['upload_photo'])){
				if(!$memberID && !$this->isLoggedIn()){
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('membersOnly'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}elseif(!$memberID){
					$member['member_id'] = $this->ipbwi->member->myInfo['member_id'];
				}else{
					$member['member_id'] = $memberID;
				}
				
				try{
					$result				= $this->ipbwi->ips_wrapper->photo->save($member,'custom');
				}
				catch(Exception $e){
					$result				= $e->getMessage();
				}
				
				return $result;
			}else{
				return false;
			}
		}
		/**
		 * @desc			Remove current member's photograph.
		 * @param	int		optional, member id
		 * @return	bool  	true
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->removePhoto(); // removes a profile picture for currently logged in member
		 * $ipbwi->member->removePhoto(55); // set member id
		 * </code>
		 * @since			2.0
		 */
		public function removePhoto($memberID=false){
			if(!$memberID){
				$memberID = $this->myInfo['member_id'];
			}
			return $this->ipbwi->ips_wrapper->photo->remove($memberID);
		}
		
		/**
		 * @desc			Get member's sig in BBCode
		 * @param	int		$userID Member ID to read the signature from.
		 * @return	string	Member Code in BBCode.
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->rawSig(55);
		 * </code>
		 * @since			2.0
		 */
		public function rawSig($userID = false){
			if(!$userID){
				$userID = $this->ipbwi->member->myInfo['member_id'];
			}
			if($info = $this->info($userID)){
				$this->ipbwi->ips_wrapper->parser->parse_nl2br			= 1;
				$this->ipbwi->ips_wrapper->parser->parse_smilies		= 0;
				$this->ipbwi->ips_wrapper->parser->parsing_signature	= 1;
				$this->ipbwi->ips_wrapper->parser->parse_html			= $this->ipbwi->ips_wrapper->vars['sig_allow_html'];
				$this->ipbwi->ips_wrapper->parser->parse_bbcode		= $this->ipbwi->ips_wrapper->vars['sig_allow_ibc'];
				return $this->ipbwi->ips_wrapper->parser->pre_edit_parse($info['signature']);
			}else{
				return false;
			}
		}
		/**
		 * @desc			Returns the number of new posts of the currently logged in member since its last visit.
		 * @return	int		Number of posts since last visit
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->numNewPosts();
		 * </code>
		 * @since			2.0
		 */
		public function numNewPosts(){
			if(!$this->isLoggedIn()){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('membersOnly'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			$sql = $this->ipbwi->ips_wrapper->DB->query('SELECT COUNT(pid) AS new FROM '.$this->ipbwi->board['sql_tbl_prefix'].'posts WHERE post_date > "'.$this->myInfo['last_visit'].'"');
			if($posts = $this->ipbwi->ips_wrapper->DB->fetch($sql)){
				return $posts['new'];
			}else{
				return false;
			}
		}
		/**
		 * @desc			Returns the amount of pips a member has.
		 * @param	int		$ID Member's ID
		 * @return	int		Member Pips Count
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->pips(55);
		 * </code>
		 * @since			2.0
		 */
		public function pips($ID = false){
			if($info = $this->info($ID)){
				// Grab Pips
				$pips = '0';
				$sql = $this->ipbwi->ips_wrapper->DB->query('SELECT * FROM '.$this->ipbwi->board['sql_tbl_prefix'].'titles ORDER BY pips ASC');
				// Loop through pip numbers checking which is good
				while($row = $this->ipbwi->ips_wrapper->DB->fetch($sql)){
					if(isset($info['posts']) && $row['posts'] <= $info['posts']){
						$pips = $row['pips'];
					}
				}
				return $pips;
			}else{
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('badMemID'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
		}
		/**
		 * @desc			Returns a member's icon in HTML
		 * @param	int		$ID Member's ID
		 * @return	string	HTML for member's icon
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->icon(55);
		 * </code>
		 * @since			2.0
		 */
		public function icon($userID = false){
			if($info = $this->info($userID)){
				$skinInfo = $this->ipbwi->skin->info($this->ipbwi->skin->id());
				if($info['g_icon']){
					// Use Group Icon
					if(substr($info['g_icon'],0,7) == 'http://'){
						$info['g_icon'] = '<img src="' . $info['g_icon'] . '" alt="'.$this->ipbwi->getLibLang('groupIcon').'" />';
						$skinInfo = $this->ipbwi->skin->info($this->ipbwi->skin->id());
						$skinInfo['set_image_dir'] = $skinInfo['set_image_dir'] ? $skinInfo['set_image_dir'] : '1';
						$info['g_icon'] = str_replace("<#IMG_DIR#>",$skinInfo['set_image_dir'],$info['g_icon']);
						return $info['g_icon'];
					}else{
						$skinInfo['set_image_dir'] = $skinInfo['set_image_dir'] ? $skinInfo['set_image_dir'] : '1';
						$url = '<img src="'.$this->ipbwi->getBoardVar('url').$info['g_icon'].'" alt="'.$this->ipbwi->getLibLang('groupIcon').'" />';
						$url = str_replace('<#IMG_DIR#>',$skinInfo['set_image_dir'],$url);
						return $url;
					}
				}else{
					// Use Pips
					$pips = $this->pips($userID);
					$pipsc = '';
					while($pips > 0){
						$skinInfo['set_image_dir'] = $skinInfo['set_image_dir'] ? $skinInfo['set_image_dir'] : '1';
						$pipsc .= '<img src="'.$this->ipbwi->getBoardVar('url').'style_images/'.$skinInfo['set_image_dir'].'/pip.gif" alt="*" />';
						$pips = $pips - '1';
					}
					return $pipsc;
				}
			}else{
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('badMemID'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
		}
		/**
		 * @desc			Login a user without checking the credentials. Use this function with care!
		 * @param	string	$userName Member's Username
		 * @param	bool	$cookie Default: true=Use cookie to save login session, false=no cookies
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->loginWithoutCheckingCredentials(55);
		 * $ipbwi->member->loginWithoutCheckingCredentials(55,false);
		 * </code>
		 * <b>Important</b><br>
		 * Cookie Settings of your Board<br>
		 * These Settings should be choosed to make a login on your website possible:<br>
		 * Cookie Domain: .your-domain.com<br>
		 * Cookie Name Prefix: {blank}<br>
		 * Cookie Path: {blank}<br>
		 * If you want to get the login work on subdomains, you have to turn off "Create a stronghold auto-log in cookie" in your Cookie-Settings of your Board.<br>
		 * This function sends http-headers, so you have to call it before any output is sent to the browser.
		 * @since			3.4.3
		 */
		public function loginWithoutCheckingCredentials($user,$cookie=true){
			if($this->ipbwi->ips_wrapper->login->doLoginWithoutCheckingCredentials($user,$cookie)){
				$this->loggedIn = true;
				$info = \IPSMember::load($user);
				$this->ipbwi->cache->save('memberInfo', $user, $info);
				$this->myInfo = $info;
				return true;
			}else{
				return false;
			}
		}
		/**
		 * @desc			Login a user.
		 * @param	string	$userName Member's Username
		 * @param	string	$password Member's Password
		 * @param	bool	$cookie Default: true=Use cookie to save login session, false=no cookies
		 * @param	integer	$anon Default: false=Keep user anonymous on forums, true=keep anon.
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->login(55,'password');
		 * $ipbwi->member->login(55,'password',true,false,true);
		 * </code>
		 * <b>Important</b><br>
		 * Cookie Settings of your Board<br>
		 * These Settings should be choosed to make a login on your website possible:<br>
		 * Cookie Domain: .your-domain.com<br>
		 * Cookie Name Prefix: {blank}<br>
		 * Cookie Path: {blank}<br>
		 * If you want to get the login work on subdomains, you have to turn off "Create a stronghold auto-log in cookie" in your Cookie-Settings of your Board.<br>
		 * This function sends http-headers, so you have to call it before any output is sent to the browser.
		 * @since			2.0
		 */
		public function login($user=false,$pw=false,$cookie=true,$anon=false){
			$login_method = $this->ipbwi->ips_wrapper->cache->getCache('login_methods');
			
			// login via username
			$internal_found = false;
			foreach($login_method as $findinternal){
				if($findinternal['login_folder_name'] == 'internal'){
					$internal_found = true;
					if($findinternal['login_user_id'] == 'username' && $this->name2id($user)){
						$_POST['ips_username'] = $user;
						$this->ipbwi->ips_wrapper->request['ips_username'] = $user;
						$userID = $this->name2id($user);
					// or via email
					}elseif($findinternal['login_user_id'] == 'email' && $this->email2id($user)){
						$_POST['ips_username'] = $user;
						$this->ipbwi->ips_wrapper->request['ips_username'] = $user;
						$userID = $this->email2id($user);
					// or both possible
					}elseif($findinternal['login_user_id'] == 'either' && ($this->email2id($user) || $this->name2id($user))){
						if($this->email2id($user)){
							$email = true;
							$_POST['ips_username'] = $user;
							$this->ipbwi->ips_wrapper->request['ips_username'] = $user;
							$userID = $this->email2id($user);
						}else{
							$_POST['ips_username'] = $user;
							$this->ipbwi->ips_wrapper->request['ips_username'] = $user;
							$userID = $this->name2id($user);
						}
					}else{
						$this->ipbwi->addSystemMessage('Error', 'Login failed - member not found', 'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
						return false;
					}
				}
			}
			if($internal_found == false){
				$this->ipbwi->addSystemMessage('Error', 'Login failed - no internal login method found.', 'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			
			if($cookie) {
				$this->ipbwi->ips_wrapper->request['rememberMe'] = 1;
			}
			if(isset($pw)){
				$_POST['ips_password'] = $pw;
				$this->ipbwi->ips_wrapper->request['ips_password'] = \IPSText::parseCleanValue( urldecode($pw));
			}

			$status = $this->ipbwi->ips_wrapper->login->doLogin();
			if(isset($status[2])){
				$this->loggedIn = false;
				$this->ipbwi->addSystemMessage('Error', $status[2], 'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}elseif($status[0] != ''){
				$this->loggedIn = true;
				$info = \IPSMember::load($userID);
				$this->ipbwi->cache->save('memberInfo', $userID, $info);
				$this->myInfo = $info;
				$this->ipbwi->addSystemMessage('Success', $status[0], 'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return true;
			}else{
				$this->loggedIn = false;
				$this->ipbwi->addSystemMessage('Error', 'Login failed but no error was send out.', 'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
		}
		/**
		 * @desc			Logout a user.
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->logout();
		 * </code>
		 * @since			2.0
		 */
		public function logout(){
			$status = $this->ipbwi->ips_wrapper->login->doLogout(false); // @ todo: check notices from ip.board
			if(is_array($status) && count($status) > 0){
				$this->loggedIn = false;
				return true;
			}else{
				return false;
			}
		}
		/**
		 * @desc			Lists the board's members.
		 * @param	array	$options Overwrites default behaviour of SQL query.
		 * The following options can be used to overwrite the default query results.
		 * <br>'order' default: 'asc'
		 * <br>'start' default: '0' start with first record
		 * <br>'limit' default: '30' no. of members per page
		 * <br>'orderby' default: 'name' other keys see below
		 * <br>'group' default: '*' all groups. You can specifiy a number or list of numbers
		 * <br>'extra_groups' default: false no extra groups. Turn to true to get extra-groups, too.
		 *
		 * Sort keys: any field from '.$this->ipbwi->board['sql_tbl_prefix'].'members or '.$this->ipbwi->board['sql_tbl_prefix'].'groups.
		 * To avoid trouble ordering by a field 'xxx', use <b>m.XXX</b> or <b>g.XXX</b> as
		 * the full qualified fieldname, not just 'xxx'.
		 * @return	array	Members
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->getList();
		 * $ipbwi->member->getList(array('order' => 'asc', 'start' => '0', 'limit' => '30', 'orderby' => 'name', 'group' => '*'));
		 * </code>
		 * @since			2.0
		 */
		public function getList($options = array('select' => 'm.*, g.*, cf.*', 'order' => 'asc', 'orderby' => 'name', 'group' => '*', 'extra_groups' => false)) {
			// Ordering
			$orders = array('id', 'name', 'posts', 'joined');
			if(!in_array($options['orderby'], $orders)){
				$options['orderby'] = 'name';
			}
			// Order By
			$options['order'] = ($options['order'] == 'desc') ? 'DESC' : 'ASC';
			// Start and Limit
			if((intval($options['start']) > 0) || (intval($options['limit']) > 0)){
				$filter = 'LIMIT '.intval($options['start']).','.intval($options['limit']);
			}else{
				$filter = '';
			}
			// Grouping
			$where = '';
			if(is_array($options['group']) AND $options['group'] != '*'){
				foreach($options['group'] as $i){
					$i = (int)$i;
					if($i > 0){
						if($where){
							$where .= 'OR m.member_group_id="'.$i.'" ';
						}else{
							$where .= 'm.member_group_id="'.$i.'" ';
						}
						if ($options['extra_groups'])
						{
							$where .= 'OR FIND_IN_SET('.$i.', m.mgroup_others) ';
						}
					}
				}
			}
			if($where){
				$where = 'WHERE m.member_id != "0" AND ('.$where.')';
			}else{
				$where = 'WHERE m.member_id != "0"';
			}
			
			$query = 'SELECT '.$options['select'].' FROM '.$this->ipbwi->board['sql_tbl_prefix'].'members m LEFT JOIN '.$this->ipbwi->board['sql_tbl_prefix'].'groups g ON (m.member_group_id=g.g_id) LEFT JOIN '.$this->ipbwi->board['sql_tbl_prefix'].'pfields_content cf ON (cf.member_id=m.member_id) '.$where.' ORDER BY '.$options['orderby'].' '.$options['order'].' '.$filter;
			$sql = $this->ipbwi->ips_wrapper->DB->query($query);
			
			$return = array();
			while($row = $this->ipbwi->ips_wrapper->DB->fetch($sql)){
				$return[$row['member_id']] = $row;
			}
			return $return;
		}
		/**
		 * @desc			Get an array of online members.
		 * @param	bool	$detailed if true, function returns multi-dimensional array containing the result of get_advinfo() for each member. Default false - simple list.
		 * @param	bool	$formatted if true, function will return an html list (string) of display names, each linked to each member's personal profile. Default false - returns array.
		 * @param	bool	$show_anon if true, function will ignore logged-in member's anonymity choice. Default false - normal board action.
		 * @param	string	$order_by choose what to order the results by - choose from 'member_name', 'member_id', 'running_time', 'location'. Default "running_time".
		 * @param	string	$order choose what order to order the results in. Options are ascending; 'ASC', or descending; 'DESC'. Default 'DESC'.
		 * @param	string	$separator - if $formatted set to true, this string will go between each linked display name. Default ', '.
		 * @return	array	online member list
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->listOnlineMembers();
		 * $ipbwi->member->listOnlineMembers(true,true,true,'member_name','ASC',' - ');
		 * </code>
		 * @since			2.0
		 */
		public function listOnlineMembers($detailed = false, $formatted = false, $show_anon = false, $order_by = 'running_time', $order = 'DESC', $separator = ', '){
			// Grab the cut-off length in minutes from the board settings
			$cutoff = intval($this->ipbwi->getBoardVar('au_cutoff') ? $this->ipbwi->getBoardVar('au_cutoff') : '15');
			// Create a timestamp for the current time, and subtract the cut-off length to get a timestamp in the past
			$timecutoff = time()-($cutoff * 60);
			if($formatted !== false){
				// the $formatted param is true, so let's return an HTML list of display name links, separated by $separator
				// if this function has already been run and has saved a cache, return the cached value from database for speed
				if($online = $this->ipbwi->cache->get('listOnlineMembers', 'formatted') && isset($online) && is_array($online) && count($online) > 0){
					// For each key in the $online array we just read from the database, set the value to the html formatted display name link
					foreach($online as $key => $value){
						// Grab advanced info for the member so we have the display name, prefix and suffix
						$member = $this->info($value);
						// Create the html-formatted string
						if($this->photo($value,true, true) != ''){
							if($formatted == 'avatars'){
								$link = '<div class="ipbwi-online-user"><a href="'.$this->ipbwi->getBoardVar('url').'user/'.$value.'-'.$member['members_seo_name'].'/" title="'.$member['members_display_name'].'">'.$this->photo($value,true, true).'</a></div>';
							}else{
								$link = '<a href="'.$this->ipbwi->getBoardVar('url').'user/'.$value.'-'.$member['members_seo_name'].'/">'.$member['prefix'].$member['members_display_name'].$member['suffix'].'</a>';
							}
							$online[$key] = $link;
						}
					}
					// Now we have an array full of html links... But that isn't very helpful to a PHP newbie. Lets just return an html string. Implode the array with $separator
					$online = implode($separator,$online);
					return $online;
				}
				// if we are happy to ignore logged-in members' requests to be anonymous, we need a slightly different database query.
				if($show_anon){
					$query = 'SELECT member_id FROM '.$this->ipbwi->board['sql_tbl_prefix'].'sessions s WHERE s.member_id != "0" AND s.running_time > "'.$timecutoff.'" ORDER BY '.$order_by.' '.$order;
					$sql = $this->ipbwi->ips_wrapper->DB->query($query);
				}else{
					// ok so this is the normal database query which should return the member IDs of all logged-in members. It does not return guests as they have no member ID :)
					$query = 'SELECT member_id FROM '.$this->ipbwi->board['sql_tbl_prefix'].'sessions s WHERE s.login_type != "1" AND s.member_id != "0" AND s.running_time > "'.$timecutoff.'" ORDER BY '.$order_by.' '.$order;
					$sql = $this->ipbwi->ips_wrapper->DB->query($query);
				}
				// For each result from the MySQL query, add the member's ID to the $options array with the key and value both equal to the member's ID
				while($row = $this->ipbwi->ips_wrapper->DB->fetch($sql)){
					$ID = $row['member_id'];
					$online[$ID] = $ID;
				}
				// We didn't do all that just to have to do it again next time. Cache the result to the database for speed next time.
				$this->ipbwi->cache->save('listOnlineMembers', 'formatted', $online);
				// For each key in the $online array we just cached to the database, set the value to the html formatted display name link
				if(isset($online) && is_array($online) && count($online) > 0){
					foreach($online as $key => $value){
						// Grab advanced info for the member so we have the display name, prefix and suffix
						$member = $this->info($value);
						// Create the html-formatted string
						if($this->photo($value,true, true) != ''){
							if($formatted == 'avatars'){
								$link = '<div class="ipbwi-online-user"><a href="'.$this->ipbwi->getBoardVar('url').'user/'.$value.'-'.$member['members_seo_name'].'/" title="'.$member['members_display_name'].'">'.$this->photo($value,true, true).'</a></div>';
							}else{
								$link = '<a href="'.$this->ipbwi->getBoardVar('url').'user/'.$value.'-'.$member['members_seo_name'].'/">'.$member['prefix'].$member['members_display_name'].$member['suffix'].'</a>';
							}
							
							$onlinenew[$key] = $link;
						};
					}
					$online = $onlinenew;
					// Now we have an array full of html links... But that isn't very helpful to a PHP newbie. Lets just return an html string. Implode the array with $separator
					$online = implode($separator,$online);
					// Finally, return the array
					return $online;
				} else{
					return false;
				}
			}
			// if the $detailed param is true, return extra info :)
			/*if($detailed){
				// if this function has already been run and has saved a cache, return the cached value from database for speed
				if($online = $this->ipbwi->cache->get('listOnlineMembers', 'nodetail') && isset($online) && is_array($online) && count($online) > 0){
					// For each key in the $online array we just read from the database, set the value to the result of get_advinfo(value)
					foreach($online as $key => $value){
						$online[$key] = $this->info($value);
					}
					// Return the array which now has extra info :)
					return $online;
				}
				// if we are happy to ignore logged-in members' requests to be anonymous, we need a slightly different database query.
				if($show_anon){
					$this->ipbwi->ips_wrapper->DB->query('SELECT member_id FROM '.$this->ipbwi->board['sql_tbl_prefix'].'sessions s WHERE s.member_id != "0" AND s.running_time > "'.$timecutoff.'" ORDER BY '.$order_by.' '.$order);
				}else{
					// ok so this is the normal database query which should return the member IDs of all logged-in members. It does not return guests as they have no member ID :)
					$this->ipbwi->ips_wrapper->DB->query('SELECT member_id FROM '.$this->ipbwi->board['sql_tbl_prefix'].'sessions s WHERE s.login_type != "1" AND s.member_id != "0" AND s.running_time > "'.$timecutoff.'" ORDER BY '.$order_by.' '.$order);
				}
				// For each result from the MySQL query, add the member's ID to the $options array with the key and value both equal to the member's ID
				while($row = $this->ipbwi->ips_wrapper->DB->fetch()){
					$ID = $row['member_id'];
					$online[$ID] = $ID;
				}
				// We didn't do all that just to have to do it again next time. Cache the result to the database for speed next time.
				$this->ipbwi->cache->save('listOnlineMembers', 'nodetail', $online);
				// For each key in the $online array we just cached to the database, set the value to the result of get_advinfo(value)
				if(isset($online) && is_array($online) && count($online) > 0){
					foreach($online as $key => $value){
						$online[$key] = $this->info($value);
					}
					// Finally, return the array
					return $online;
				}else{
					 return false;
				}
			}
			// neither $detailed or $formatted are true, so return a simple list
			// if this function has already been run and has saved a cache, return the cached value from database for speed
			if($online = $this->ipbwi->cache->get('listOnlineMembers', 'simple')){
				return $online;
			}
			// if we are happy to ignore logged-in members' requests to be anonymous, we need a slightly different database query.
			if($show_anon){
				$this->ipbwi->ips_wrapper->DB->query('SELECT member_id FROM '.$this->ipbwi->board['sql_tbl_prefix'].'sessions s WHERE s.member_id != "0" AND s.running_time > "'.$timecutoff.'" ORDER BY '.$order_by.' '.$order);
			}else{
				// ok so this is the normal database query which should return the member IDs of all logged-in members. It does not return guests as they have no member ID :)
				$this->ipbwi->ips_wrapper->DB->query('SELECT member_id FROM '.$this->ipbwi->board['sql_tbl_prefix'].'sessions s WHERE s.login_type != "1" AND s.member_id != "0" AND s.running_time > "'.$timecutoff.'" ORDER BY '.$order_by.' '.$order);
			}
			// For each result from the MySQL query, add the member's ID to the $options array with the key and value both equal to the member's ID
			while($row = $this->ipbwi->ips_wrapper->DB->fetch()){
				$ID = $row['member_id'];
				$online[$ID] = $ID;
			}
			// We didn't do all that just to have to do it again next time. Cache the result to the database for speed next time.
			$this->ipbwi->cache->save('listOnlineMembers', 'simple', $online);
			// Finally, return the array
			return $online;*/
		}
		/**
		 * @desc			Get an array of random members.
		 * @param	int		$limit How many Member should be returned? default: 5
		 * @return	array	Random Members
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->listRandomMembers();
		 * $ipbwi->member->listRandomMembers(5);
		 * </code>
		 * @since			2.0
		 */
		public function listRandomMembers($limit = 5){
			$query = 'SELECT * FROM '.$this->ipbwi->board['sql_tbl_prefix'].'members ORDER BY RAND() LIMIT 0,'.intval($limit);
			$this->ipbwi->ips_wrapper->DB->query($query);
			$random = array();
			while($row = $this->ipbwi->ips_wrapper->DB->fetch()){
				$random[$row['member_id']] = $row;
			}
			return $random;
		}
		/**
		 * @desc			Removes a friend
		 * @param	int		$userID Member ID to be deleted
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->removeFriend(55);
		 * </code>
		 * @since			2.0
		 */
		public function removeFriend($userID){
			if($this->isLoggedIn()){
				$sql = $this->ipbwi->ips_wrapper->DB->query('DELETE FROM '.$this->ipbwi->board['sql_tbl_prefix'].'profile_friends WHERE friends_friend_id="'.intval($userID).'" AND friends_member_id="'.$this->ipbwi->member->myInfo['member_id'].'"');
				if($this->ipbwi->ips_wrapper->DB->getTotalRows($sql) == 0){
					return false;
				}else{
					// recache
					$this->ipbwi->ips_wrapper->pack_and_update_member_cache($this->ipbwi->member->myInfo['member_id'], array('friends' => $this->friendsList()));
					$this->ipbwi->ips_wrapper->pack_and_update_member_cache(intval($userID), array('friends' => $this->friendsList(false,$userID)));
					return true;
				}
			}else{
				return false;
			}
		}
		/**
		 * @desc			Adds a friend
		 * @param	int		$userID Member ID to be added
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->addFriend(55);
		 * </code>
		 * @since			2.0
		 */
		public function addFriend($userID){
			if($this->isLoggedIn()){
				// Check user exists
				if(!$userID OR !$this->info(intval($userID))){
					return false;
				}
				// o_O. Firstly check if there is already an entry.
				$this->ipbwi->ips_wrapper->DB->query('SELECT * FROM '.$this->ipbwi->board['sql_tbl_prefix'].'profile_friends WHERE friends_friend_id="'.intval($userID).'"AND friends_member_id="'.$this->ipbwi->member->myInfo['member_id'].'"');
				if($row = $this->ipbwi->ips_wrapper->DB->fetch()){
					return true;
				}else{
					// We can just add an entry because theres nothing there.
					$friend = $this->info($userID);
					// support for moderate_friends-field have to be added (including sending confirmation message)
					//if($friend['pp_setting_moderate_friends']) $friends_approved = 0; else $friends_approved = 1;
					$friends_approved = 1;
					if($this->ipbwi->ips_wrapper->DB->query('INSERT INTO '.$this->ipbwi->board['sql_tbl_prefix'].'profile_friends VALUES ("", "'.$this->ipbwi->member->myInfo['member_id'].'","'.intval($userID).'","'.$friends_approved.'", "'.time().'")')){
						// recache
						$this->ipbwi->ips_wrapper->pack_and_update_member_cache($this->ipbwi->member->myInfo['member_id'], array('friends' => $this->friendsList()));
						$this->ipbwi->ips_wrapper->pack_and_update_member_cache(intval($userID), array('friends' => $this->friendsList(false,$userID)));
					}
					return true;
				}
			}else{
				return false;
			}
		}
		/**
		 * @desc			Returns information on the current user's contacts.
		 * @param	bool	$userID Member-ID to get friends of this member. If not set, friends of currently logged in member will be listed.
		 * @param	bool	$details Detailed Member Information, default: false
		 * @param	bool	$unapproved List unapproved friends, default: false
		 * @return	array	Friends Informations
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->friendsList();
		 * $ipbwi->member->friendsList(55,true,true);
		 * </code>
		 * @since			2.0
		 */
		public function friendsList($userID = false,$details = false,$unapproved = false){
			// check for memberid
			if(is_string($userID)) {
				$member = intval($userID);
			}elseif($this->isLoggedIn()){
				$member = $this->ipbwi->member->myInfo['member_id'];
			}else{
				return false;
			}
			// check if unapproved
			if(empty($unapproved)){
				$approved = ' AND friends_approved="1"';
			}
			$this->ipbwi->ips_wrapper->DB->query('SELECT * FROM '.$this->ipbwi->board['sql_tbl_prefix'].'profile_friends WHERE friends_member_id="'.$member.'"'.$approved);
			$friends = array();
			while($row = $this->ipbwi->ips_wrapper->DB->fetch()){
				$friends[$row['friends_id']] = $row;
			}
			// check for details
			if($details === true){
				foreach($friends as $friend){
					$friends[$friend['friends_id']]['details'] = $this->info($friend['friends_friend_id']);
				}
			}
			return $friends;
		}
	}
?>