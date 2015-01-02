<?php
	/**
	 * @author			Matthias Reuter
	 * @package			report
	 * @copyright		2007-2013 Matthias Reuter
	 * @link			http://ipbwi.com/examples/report.php
	 * @since			3.4.1
	 * @license			http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
	 */

    namespace IPBWI;
    
	class ipbwi_report extends ipbwi {
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
		 * @desc			Returns number of reports for a specific forum, topic or post
		 * @param	int		$parentID ID of the forum, topic or post
		 * @param	string	$parentType name of the parent type, possible vars: "forum", "topic", "post". If no parentType is given, parentType will be set to "post"
		 * @param	mixed	$status of reports, possible vars: "new", "review", "complete" or any integer for status ID. This setting is optional
		 * @return	array	array with general information and reports
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->report->getList(55);
		 * </code>
		 * @since			3.4.1
		 */
		public function getList($parentID,$parentType='post',$status=false){
			// Check for Post Cache
			if($cache = $this->ipbwi->cache->get('reportGetList', md5($parentID.$parentType.$status))){
				return $cache;
			}else{
				$exdat1 = '';
				$exdat2 = '';
				$exdat3 = '';
				
				if($parentType == 'forum'){
					$exdat1		= 'exdat1='.intval($parentID);
					$order		= 'exdat1';
				}elseif($parentType == 'topic'){
					$exdat2		= 'exdat2='.intval($parentID);
					$order		= 'exdat2';
				}elseif($parentType == 'post'){
					$exdat3		= 'exdat3='.intval($parentID);
					$order		= 'exdat3';
				}else{
					return false;
				}
				
				if($status == 'new'){
					$sql_status = ' AND status=1';
				}elseif($status == 'review'){
					$sql_status = ' AND status=2';
				}elseif($status == 'complete'){
					$sql_status = ' AND status=3';
				}elseif($status !== false){
					$sql_status = ' AND status='.intval($status);
				}else{
					$sql_status = '';
				}

				// Grab the reports
				$query = 'SELECT i.*,r.* FROM '.$this->ipbwi->board['sql_tbl_prefix'].'rc_reports_index i LEFT JOIN '.$this->ipbwi->board['sql_tbl_prefix'].'rc_reports r ON (r.rid=i.id) WHERE i.'.$exdat1.$exdat2.$exdat3.' AND rc_class=2'.$sql_status.' ORDER BY '.$order.' ASC';
				
				$sql = $this->ipbwi->ips_wrapper->DB->query($query);
				if($this->ipbwi->ips_wrapper->DB->getTotalRows($sql) == 0){
					return false;
				}
				$i = 1;
				$reports['general']['num_reports'] = 0;
				while($row = $this->ipbwi->ips_wrapper->DB->fetch($sql)){
					if($i == 1){
						$reports['general']['uid']							= $row['uid'];
						$reports['general']['title']						= $row['title'];
						$reports['general']['url']							= $row['url'];
						$reports['general']['rc_class']						= $row['rc_class'];
						$reports['general']['updated_by']					= $row['updated_by'];
						$reports['general']['date_updated']					= $row['date_updated'];
						$reports['general']['date_created']					= $row['date_created'];
						$reports['general']['forum_id']						= $row['exdat1'];
						$reports['general']['topic_id']						= $row['exdat2'];
						$reports['general']['rid']							= $row['rid'];
					}

					if(count($reports['reports'][$row['exdat2']][$row['exdat3']]) == 0){
						$reports['general']['num_reports']						= $reports['general']['num_reports']+$row['num_reports'];
					}
					
					$reports['reports'][$row['exdat2']][$row['exdat3']][$row['id']]['id']					= $row['id'];
					$reports['reports'][$row['exdat2']][$row['exdat3']][$row['id']]['status']				= $row['status'];
					$reports['reports'][$row['exdat2']][$row['exdat3']][$row['id']]['num_comments']			= $row['num_comments'];
					$reports['reports'][$row['exdat2']][$row['exdat3']][$row['id']]['report']				= $row['report'];
					$reports['reports'][$row['exdat2']][$row['exdat3']][$row['id']]['report_by']			= $row['report_by'];
					$reports['reports'][$row['exdat2']][$row['exdat3']][$row['id']]['date_reported']		= $row['date_reported'];
					
					$i++;
				}
			
				// Save Reports In Cache and Return
				$this->ipbwi->cache->save('reportGetList', md5($parentID.$parentType.$status), $reports);
				
			}
			
			return $reports;
		}
		
		/**
		 * @desc			creates a new report
		 * @param	int		$postID ID of the reported post
		 * @param	string	$reportPost content of reporting text
		 * @return	int		integer of inserted/updated reports index row
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->report->create(55,'this is a testpost');
		 * </code>
		 * @since			3.4
		 */
		public function create($postID,$reportPost){
			$query = 'SELECT id FROM '.$this->ipbwi->board['sql_tbl_prefix'].'rc_reports_index WHERE exdat3="'.intval($postID).'" AND rc_class=2'.$sql_status.'';
			$check = $this->ipbwi->ips_wrapper->DB->query($query);
			if($this->ipbwi->ips_wrapper->DB->getTotalRows($sql) == 0){
				$postInfo = $this->ipbwi->post->info($postID);
				
				$url			= '/index.php?showtopic='.$postInfo['topic_id'].'&view=findpost&p='.$postID;
				$a_url			= str_replace("&", "&amp;", $url);
				$uid			= md5($url . '_2');
				
				// insert rc_reports_index
				$query = 'INSERT INTO '.$this->ipbwi->board['sql_tbl_prefix'].'rc_reports_index (uid, title, status, url, rc_class, updated_by, date_updated, date_created, exdat1, exdat2, exdat3, num_reports, num_comments, seoname, seotemplate) VALUES ("'.$uid.'","'.$postInfo['topic_name'].'","1","'.$a_url.'","2","'.$this->ipbwi->member->myInfo['member_id'].'","'.time().'","'.time().'","'.$postInfo['forum_id'].'","'.$postInfo['topic_id'].'","'.$postID.'","1","0","'.$postInfo['title_seo'].'","showtopic")';
				$this->ipbwi->ips_wrapper->DB->query($query);
				$indexID = $this->ipbwi->ips_wrapper->DB->getInsertId();
				
				// insert rc_reports
				$query = 'INSERT INTO '.$this->ipbwi->board['sql_tbl_prefix'].'rc_reports (rid, report, report_by, date_reported) VALUES ("'.$indexID.'","'.$this->ipbwi->ips_wrapper->DB->addSlashes($this->ipbwi->makeSafe($this->ipbwi->ips_wrapper->editor->process($reportPost))).'","'.$this->ipbwi->member->myInfo['member_id'].'","'.time().'")';
				$this->ipbwi->ips_wrapper->DB->query($query);
				$reportID = $this->ipbwi->ips_wrapper->DB->getInsertId();
				
				return $indexID;
			}else{
				$index = $this->ipbwi->ips_wrapper->DB->fetch($sql);
				
				// update rc_reports_index
				$query = 'UPDATE '.$this->ipbwi->board['sql_tbl_prefix'].'rc_reports_index SET updated_by="'.$this->ipbwi->member->myInfo['member_id'].'", date_updated="'.time().'", num_reports=num_reports+1 WHERE exdat3="'.intval($postID).'"';
				$this->ipbwi->ips_wrapper->DB->query($query);
				
				// insert rc_reports
				$query = 'INSERT INTO '.$this->ipbwi->board['sql_tbl_prefix'].'rc_reports (rid, report, report_by, date_reported) VALUES ("'.$index['id'].'","'.$this->ipbwi->ips_wrapper->DB->addSlashes($this->ipbwi->makeSafe($this->ipbwi->ips_wrapper->editor->process($reportPost))).'","'.$this->ipbwi->member->myInfo['member_id'].'","'.time().'")';
				$this->ipbwi->ips_wrapper->DB->query($query);
				
				return $index['id'];
			} 	 	
		}
	}
?>