<?php
	/**
	 * @author			Matthias Reuter
	 * @package			bbcode
	 * @copyright		2007-2013 Matthias Reuter
	 * @link			http://ipbwi.com/examples/bbcode.php
	 * @since			2.0
	 * @license			http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
	 */

    namespace IPBWI;

	class ipbwi_bbcode extends ipbwi {
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
		 * @desc			converts BBCode to HTML using IPB's native parser.
		 * @param	string	$input bbcode-formatted string
		 * @param	bool	$smilies set to true to parse smilies, otherwise false
		 * @return	string	HTML version of input
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->bbcode->bbcode2html('[b]test[/b]',true);
		 * </code>
		 * @since			2.0
		 */
		public function bbcode2html($input, $smilies = true){
			$this->ipbwi->ips_wrapper->parser->parse_smilies = $smilies;
			$this->ipbwi->ips_wrapper->parser->parse_html = 0;
			$this->ipbwi->ips_wrapper->parser->parse_bbcode = 1;
			$this->ipbwi->ips_wrapper->parser->strip_quotes = 1;
			$this->ipbwi->ips_wrapper->parser->parse_nl2br = 0;
			$input = @$this->ipbwi->ips_wrapper->editor->process($input);
			// Leave this here in case things go pear-shaped...
			$input = $this->ipbwi->ips_wrapper->parser->display($input);
			if($smilies){
				$input	= $this->ipbwi->properXHTML($input);
			}
			return $input;
		}
		/**
		 * @desc			converts HTML to BBCode using IPB's native parser.
		 * @param	string	$input html-formatted string
		 * @return	string	BBCode version of input
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->bbcode->html2bbcode('<b>test</b>');
		 * </code>
		 * @since			2.0
		 */
		public function html2bbcode($input){
			$this->ipbwi->ips_wrapper->parser->parse_html		= 0;
			$this->ipbwi->ips_wrapper->parser->parse_nl2br		= 0;
			$this->ipbwi->ips_wrapper->parser->parse_smilies	= 1;
			$this->ipbwi->ips_wrapper->parser->parse_bbcode		= 1;
			$this->ipbwi->ips_wrapper->parser->parsing_section	= 'myapp_comment';
			$input = $this->ipbwi->ips_wrapper->parser->HtmlToBBCode($input);
			return $input;
		}
		/**
		 * @desc			List emoticons, optional limit the result to clickable emoticons only.
		 * @param	bool	$clickable set to true to list clickable emoticons only, otherwise set to false
		 * @return	array	Assoc array with Emoticons, keys 'typed', 'image'
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->bbcode->listEmoticons(true);
		 * </code>
		 * @since			2.0
		 */
		public function listEmoticons($clickable = false){
			if($clickable){
				$this->ipbwi->ips_wrapper->DB->query('SELECT typed, image FROM '.$this->ipbwi->board['sql_tbl_prefix'].'emoticons WHERE clickable="1"');
			}else{
				$this->ipbwi->ips_wrapper->DB->query('SELECT typed, image FROM '.$this->ipbwi->board['sql_tbl_prefix'].'emoticons');
			}
			$emos = array();
			while($row = $this->ipbwi->ips_wrapper->DB->fetch()){
				$emos[$row['typed']] = $row['image'];
			}
			return $emos;
		}
		/**
		 * @desc			Print IP.board's built in RichTextEditor (RTE). notice: if your form isn't formatted correctly, please check in your css declaration of tags, e.g. "ul", wether they conflict with IP.board's editor.
		 * @param	string	$post a string of content going to be displayed in editor. If empty, a blank editor will be loaded.
		 * @param	array	$settings an array of settings for the editor. More details on setting affects are described here: http://community.invisionpower.com/resources/guides.html/_/advanced-and-developers/api-methods/editor-bbcode-r146
		 * + string		type				full or mini
		 * + bool		minimize			true, default: false
		 * + int		height				Default when type=full: 500, when type=mini: 300
		 * + string		autoSaveKey			should be a string which will not be being used by any other editor simultaneously, but you will be able to retrieve later.
		 * + string		warnInfo			full or fastReply
		 * + bool		modAll				will only have effect if warnInfo is also set
		 * + bool		recover				if set to TRUE will cause the value of $_POST['Post'] to be set as the editor content. 
		 * + bool		noSmilies			if set to TRUE will cause the editor to not display the emoticons button.
		 * + bool		isHtml				should be set to TRUE if you will be allowing raw HTML to be submitted.
		 * + string		isRte				allows you to override whether the editor should display as the WYSIWG editor, or a plain text box.
		 * + bool		isTypingCallback	allows you to specify a javascript function which will be ran once the user starts typing in the editor.
		 * + bool		delayInit			if set to TRUE will cause the editor to not be initialized on page load.
		 * + bool		editorName			if set will be the HTML id of the editor, which will be necessary if you are using delayInit.
		 * + bool		input_name			sets name of the textarea form field, default: post
		 * @return		string	HTML Code of IP.board's RTE
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->bbcode->printTextEditor('post content');
		 * </code>
		 * @since			2.0
		 */
		public function printTextEditor($post='',$settings=array()){
			if(!isset($settings['type'])){ $settings['type'] = 'full'; }
			if(!isset($settings['minimize'])){ $settings['minimize'] = FALSE; }
			if(!isset($settings['height'])){ $settings['height'] = 300; }
			if(!isset($settings['autoSaveKey'])){ $settings['autoSaveKey'] = ''; }
			if(!isset($settings['warnInfo'])){ $settings['warnInfo'] = NULL; }
			if(!isset($settings['modAll'])){ $settings['modAll'] = FALSE; }
			if(!isset($settings['recover'])){ $settings['recover'] = FALSE; }
			if(!isset($settings['noSmilies'])){ $settings['noSmilies'] = FALSE; }
			if(!isset($settings['isHtml'])){ $settings['isHtml'] = FALSE; }
			if(!isset($settings['isRte'])){ $settings['isRte'] = NULL; }
			if(!isset($settings['isTypingCallback'])){ $settings['isTypingCallback'] = ''; }
			if(!isset($settings['delayInit'])){ $settings['delayInit'] = FALSE; }
			if(!isset($settings['editorName'])){ $settings['editorName'] = NULL; }
			if(!isset($settings['input_name'])){ $settings['input_name'] = 'post'; }
		
			$boardURL	= str_replace('?','',$this->ipbwi->board['url']);
			$form_hash	= md5($member['email'].'&'.$member['member_login_key'].'&'.$member['joined']);
			$supermod	= intval($this->ipbwi->member->isSuperMod());
			$admin		= intval($this->ipbwi->member->isAdmin());
			$twitter	= intval($this->ipbwi->member->myInfo['twitter_id']);
			$facebook	= intval($this->ipbwi->member->myInfo['fb_uid']);
			
			$jscript = <<<EOF_SCRIPT
	<script type="text/javascript">
		//<![CDATA[
			jsDebug			= 0; /* Must come before JS includes */
			DISABLE_AJAX	= parseInt(0); /* Disables ajax requests where text is sent to the DB; helpful for charset issues */
			inACP			= false;
			var isRTL		= false;
			var rtlIe		= '';
			var rtlFull		= '';
		//]]>
	</script>
	<script type='text/javascript' src='{$boardURL}public/min/index.php?ipbv=d8b02513a3323a31589df35f44739234&amp;g=js'></script>
	<script type='text/javascript' src='{$boardURL}public/min/index.php?ipbv=d8b02513a3323a31589df35f44739234&amp;charset=UTF-8&amp;f=public/js/ipb.js,cache/lang_cache/1/ipb.lang.js,public/js/ips.hovercard.js,public/js/ips.quickpm.js,public/js/ips.post.js,public/js/ips.facebook.js,public/js/ips.poll.js,public/js/ips.attach.js,public/js/ips.textEditor.js,public/js/ips.textEditor.bbcode.js,public/js/ips.tags.js' charset='UTF-8'></script>
	<script type='text/javascript'>
		//<![CDATA[
		/* ---- URLs ---- */
		ipb.vars['base_url'] 			= '{$boardURL}index.php?s={$this->ipbwi->member->myInfo['publicSessionID']}&';
		ipb.vars['board_url']			= '{$boardURL}';
		ipb.vars['img_url'] 			= "{$boardURL}public/style_images/master";
		ipb.vars['loading_img'] 		= '{$boardURL}public/style_images/master/loading.gif';
		ipb.vars['active_app']			= 'forums';
		ipb.vars['upload_url']			= '{$boardURL}uploads';
		/* ---- Member ---- */
		ipb.vars['member_id']			= parseInt( {$this->ipbwi->member->myInfo['member_id']} );
		ipb.vars['is_supmod']			= parseInt( {$supermod} );
		ipb.vars['is_admin']			= parseInt( {$admin} );
		ipb.vars['secure_hash'] 		= '{$form_hash}';
		ipb.vars['session_id']			= '{$this->ipbwi->member->myInfo['publicSessionID']}';
		ipb.vars['twitter_id']			= {$twitter};
		ipb.vars['fb_uid']				= {$facebook};
		ipb.vars['auto_dst']			= parseInt( {$this->ipbwi->member->myInfo['members_auto_dst']} );
		ipb.vars['dst_in_use']			= parseInt( {$this->ipbwi->member->myInfo['dst_in_use']} );
		ipb.vars['is_touch']			= false;
		ipb.vars['member_group']		= {"g_mem_info":"{$this->ipbwi->member->myInfo['member_group_id']}"}
		/* ---- cookies ----- */
		ipb.vars['cookie_id'] 			= '';
		ipb.vars['cookie_domain'] 		= '{$this->ipbwi->getBoardVar('cookie_domain')}';
		ipb.vars['cookie_path']			= '/';
		/* ---- Rate imgs ---- */
		ipb.vars['rate_img_on']			= '{$boardURL}public/style_images/master/star.png';
		ipb.vars['rate_img_off']		= '{$boardURL}public/style_images/master/star_off.png';
		ipb.vars['rate_img_rated']		= '{$boardURL}public/style_images/master/star_rated.png';
		/* ---- other ---- */
		ipb.vars['highlight_color']     = "#ade57a";
		ipb.vars['charset']				= "UTF-8";
		ipb.vars['seo_enabled']			= 1;
		
		ipb.vars['seo_params']			= {"start":"-","end":"\/","varBlock":"?","varPage":"page-","varSep":"&","varJoin":"="};
		
		/* Templates/Language */
		ipb.templates['inlineMsg']		= "";
		ipb.templates['ajax_loading'] 	= "<div id='ajax_loading'><img src='{$boardURL}public/style_images/master/ajax_loading.gif' alt='" + ipb.lang['loading'] + "' /></div>";
		ipb.templates['close_popup']	= "<img src='{$boardURL}public/style_images/master/close_popup.png' alt='x' />";
		ipb.templates['rss_shell']		= new Template("<ul id='rss_menu' class='ipbmenu_content'>#{items}</ul>");
		ipb.templates['rss_item']		= new Template("<li><a href='#{url}' title='#{title}'>#{title}</a></li>");
		
			ipb.templates['m_add_friend']	= new Template("<a href='{$boardURL}index.php?app=members&amp;module=profile&amp;section=friends&amp;do=add&amp;member_id=#{id}' title='Add as Friend' class='ipsButton_secondary'><img src='{$boardURL}public/style_images/master/user_add.png' alt='Add as Friend' /></a>");
			ipb.templates['m_rem_friend']	= new Template("<a href='{$boardURL}index.php?app=members&amp;module=profile&amp;section=friends&amp;do=remove&amp;member_id=#{id}' title='Remove Friend' class='ipsButton_secondary'><img src='{$boardURL}public/style_images/master/user_delete.png' alt='Remove Friend' /></a>");
		
		ipb.templates['autocomplete_wrap'] = new Template("<ul id='#{id}' class='ipb_autocomplete' style='width: 250px;'></ul>");
		ipb.templates['autocomplete_item'] = new Template("<li id='#{id}' data-url='#{url}'><img src='#{img}' alt='' class='ipsUserPhoto ipsUserPhoto_mini' />&nbsp;&nbsp;#{itemvalue}</li>");
		ipb.templates['page_jump']		= new Template("<div id='#{id}_wrap' class='ipbmenu_content'><h3 class='bar'>Jump to page</h3><p class='ipsPad'><input type='text' class='input_text' id='#{id}_input' size='8' /> <input type='submit' value='Go' class='input_submit add_folder' id='#{id}_submit' /></p></div>");
		ipb.templates['global_notify'] 	= new Template("<div class='popupWrapper'><div class='popupInner'><div class='ipsPad'>#{message} #{close}</div></div></div>");
		
		
		ipb.templates['header_menu'] 	= new Template("<div id='#{id}' class='ipsHeaderMenu boxShadow'></div>");
		
			ipb.global.checkDST();
		
		Loader.boot();
		//]]>
	</script>
	<script type="text/javascript" src="{$boardURL}public/js/3rd_party/ckeditor/ckeditor.js?nck=7dee50749fc66bc75b51f4ed168df91e"></script>
	<script type="text/javascript" src="{$boardURL}public/js/3rd_party/ckeditor/ips_config.js?t=C6HH5UF"></script>
	<script type="text/javascript" src="{$boardURL}public/js/3rd_party/ckeditor/skins/ips/skin.js?t=C6HH5UF"></script>
	<script type="text/javascript" src="{$boardURL}public/js/3rd_party/ckeditor/lang/ipb.js?t=C6HH5UF"></script>
	<script type="text/javascript" src="{$boardURL}public/js/3rd_party/ckeditor/plugins/styles/styles/default.js?t=C6HH5UF"></script>
EOF_SCRIPT;


			/** DISABLE LEGACY MODE **/
			$this->ips_wrapper->editor->setLegacyMode(false);

			/* Set up initial value of editor */
			$this->ips_wrapper->editor->setContent($post);

			/* Print the Editor box */
			$form = $this->ips_wrapper->editor->show( $settings['input_name'], array(
				'type'					=> $settings['type'],
				'minimize'				=> $settings['minimize'],
				'height'				=> $settings['height'],
				'autoSaveKey'			=> $settings['autoSaveKey'],
				'warnInfo'				=> $settings['warnInfo'],
				'modAll'				=> $settings['modAll'],
				'recover'				=> $settings['recover'],
				'noSmilies'				=> $settings['noSmilies'],
				'isHtml'				=> $settings['isHtml'],
				'isRte'					=> $settings['isRte'],
				'isTypingCallBack'		=> $settings['isTypingCallBack'],
				'delayInit'				=> $settings['delayInit'],
				'editorName'			=> $settings['editorName']
				) );
			
			$output = $jscript.$form;
			return 	str_replace(
					array('<#EMO_DIR#>','undefined&amp;app=forums'),
					array('default',$this->ipbwi->getBoardVar('url').'/index.php?app=forums'),
					$output);

		}
	}
?>