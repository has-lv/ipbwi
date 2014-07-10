<?php
	/**
	 * @desc			This language-file provides systemMessages from IPBWI in your foreign language.
	 * @copyright		2007-2013 Matthias Reuter
	 * @package			Languages
	 * @author			Matthias Reuter
	 * @license			http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
	 * @since			2.0
	 * @web				http://ipbwi.com
	 */

	// Define Encoding and localisation
	$liblang['encoding']	= 'ISO-8859-1';
	$liblang['local']		= 'fr_FR';

	// attachment
	$libLang['attachMimeNotFound']					= 'Le type MIME requis n\'est pas défini.';
	$libLang['attachNotFoundFS']					= 'Le fichier joint n\'a pas été trouvé dans le système de fichiers.';
	$libLang['attachNotFoundDB']					= 'Le fichier joint n\'a pas été trouvé dans la base de données.';
	$libLang['attachCreated']						= 'Le fichier a été joint avec succès.';
	$libLang['attachCreationFailed']				= 'Echec lors de l\'attachement du fichier.';
	$libLang['attachFileNotInUploadDir']			= 'Le fichier joint n\'a pas été trouvé dans le dossier adéquat.';
	$libLang['attachFileExtNotExists']				= 'Cette extension de fichier n\'existe pas dans la base de données.';
	$libLang['attachFileExtNotAllowed']				= 'Cette extension de fichier n\'est pas autorisée.';
	$libLang['attachFileTooBig']					= 'La pièce jointe est trop grosse.';
	$libLang['attachFileExceedsUserSpace']			= 'Votre quota mémoire ne vous permet pas de joindre ce fichier.';

	// captcha
	$libLang['badKey']								= 'La clé n\'existe pas.';
	$libLang['captchaWrongCode']					= 'Le texte tapé dans le captcha est incorrect.';

	// forum
	$libLang['catNotExist']							= 'Cette catégorie n\'existe pas.';
	$libLang['forumNotExist']						= 'Ce forum n\'existe pas.';

	// member
	$libLang['badMemID']							= 'Idenfiant d\'utilisateur invalide.';
	$libLang['badMemPW']							= 'Mot de passe incorrect ou invalide.';
	$libLang['cfMissing']							= 'Un ou plusieurs champs de profil requis sont manquants.';
	$libLang['cfLength']							= 'Un ou plusieurs champs de profil requis dépassent la taille autorisée.';
	$libLang['cfInvalidValue']						= 'Valeur invalide.';
	$libLang['cfMustFillIn']						= 'Le champ de profil "%s" doit être complété.';
	$libLang['cfCantEdit']							= 'Impossible d\'éditer le champ de profil "%s".';
	$libLang['cfNotExist']							= 'Le champ de profil "%s" n\'existe pas.';
	$libLang['accBanned']							= 'Ce membre a été banni.';
	$libLang['accUser']								= 'Le nom d\'utilisateur spécifié n\'existe pas.';
	$libLang['accPass']								= 'Le mot de passe spécifié est invalide.';
	$libLang['accEmail']							= 'L\'adresse email spécifiée est invalide.';
	$libLang['accTaken']							= 'Le nom d\'utilisateur ou l\'adresse email spécifié(e) est déjà utilisée.';
	$libLang['loginNoFields']						= 'Merci de spécifier votre nom d\'utilisateur et votre mot de passe.';
	$libLang['loginLength']							= 'Le nom d\'utilisateur et/ou le mot de passe spécifié(s) est(sont) trop long(s).';
	$libLang['loginMemberID']						= 'Pas d\'identifiant utilisateur.';
	$libLang['loginWrongPass']						= 'Le mot de passe est incorrect.';
	$libLang['loginNoMember']						= 'Le membre n\'existe pas.';
	$libLang['noAdmin']								= 'Des droits administrateur sont requis pour cette section.';
	$libLang['membersOnly']							= 'Cette fonctionnalité n\'est disponible qu\'aux membres enregistrés.';
	$libLang['sigTooLong']							= 'La signature est trop longue.';
	$libLang['groupIcon']							= 'Icône du groupe';
	$libLang['avatarSuccess']						= 'La mise à jour de l\'avatar a été effectuée avec succès.';
	$libLang['avatarError']							= 'La mise à jour de l\'avatar a échoué.';
	$libLang['reg_username']						= 'Username: ';
	$libLang['reg_dname']							= 'Display Name: ';
	
	// permissions
	$libLang['badPermID']							= 'Identifiant de permission invalide.';
	$libLang['noPerms']								= 'Vous n\'avez pas la permission d\'effectuer cette action.';

	// pm
	$libLang['pmFolderNotExist']					= 'Le dossier n\'existe pas.';
	$libLang['pmMsgNoMove']							= 'Impossible de déplacer le message.';
	$libLang['pmFolderNoRem']						= 'Impossible de supprimer le dossier.';
	$libLang['pmNoRecipient']						= 'Le destinataire n\'a pas été spécifié.';
	$libLang['pmTitle']								= 'Titre du message invalide.';
	$libLang['pmMessage']							= 'Message invalide.';
	$libLang['pmMemNotExist']						= 'Le membre n\'existe pas.';
	$libLang['pmMemDisAllowed']						= 'Le membre spécifié ne peut pas utiliser sa messagerie privée.';
	$libLang['pmMemFull']							= 'La boîte de réception du destinataire est pleine.';
	$libLang['pmMemBlocked']						= 'Ce membre vous a bloqué(e).';
	$libLang['pmCClimit']							= 'Vous ne pouvez pas envoyer ce message en CC à autant d\'utilisateurs.';
	$libLang['pmRecDisallowed']						= 'Un des destinataires ne peut pas utiliser sa messagerie privée.';
	$libLang['pmRecFull']							= 'Un des destinataires a sa boîte de réception pleine.';
	$libLang['pmRecBlocked']						= 'Un des destinataires vous a bloqué(e).';
	$libLang['pmCantSendToSelf']					= 'You cannot send a conversation to yourself';

	// poll
	$libLang['pollAlreadyVoted']					= 'Vous avez déjà voté dans ce sondage.';
	$libLang['pollInvalidVote']						= 'Vote invalide.';
	$libLang['pollNotExist']						= 'Ce sondage n\'existe pas.';
	$libLang['pollInvalidOpts']						= 'Vous devez spécifier entre 2 et %s options.';
	$libLang['pollInvalidQuestions']				= 'Vous devez spécifier entre 1 et %s questions.';

	// topic
	$libLang['topicNotExist']						= 'Le topic n\'existe pas.';
	$libLang['topicNoTitle']						= 'Vous devez entrer un titre pour le topic.';

	// post
	$libLang['floodControl']						= 'Tentative de flood? Patientez "%s" secondes avant d\'essayer de poster à nouveau.';
	$libLang['postNotExist']						= 'Ce message n\'existe pas.';

	// search
	$libLang['searchIDnotExist']					= 'Cet identifiant de recherche n\'existe pas.';
	$libLang['searchNoResults']						= 'Aucun résultat.';

	// skin
	$libLang['skinNotExist']						= 'Ce skin n\'existe pas.';

	// tag cloud
	$libLang['badTag']								= 'Vous devez spécifier un nom de tag valide.';
	$libLang['badDestination']						= 'Vous devez spécifier une destination valide.';
	$libLang['badTagID']							= 'Vous devez spécifier un identifiant de tag valide.';

	// wordpress
	$libLang['wpRegisterNameExists']				= 'Ce nom d\'utilisateur est déjà utilisé par un compte existant. Merci de faire un autre choix.';
	$libLang['wpRegisterEmailExists']				= 'Cette adresse email est déjà utilisée par un compte existant. Merci de faire un autre choix.';

	// months
	$libLang['month_1']								= 'janvier';
	$libLang['month_2']								= 'février';
	$libLang['month_3']								= 'mars';
	$libLang['month_4']								= 'avril';
	$libLang['month_5']								= 'mai';
	$libLang['month_6']								= 'juin';
	$libLang['month_7']								= 'juillet';
	$libLang['month_8']								= 'août';
	$libLang['month_9']								= 'septembre';
	$libLang['month_10']							= 'octobre';
	$libLang['month_11']							= 'novembre';
	$libLang['month_12']							= 'décembre';


	// system messages
	$libLang['sysMsg_Success']						= 'OK: ';
	$libLang['sysMsg_Error']						= 'Erreur: ';
	$libLang['sysMsg_Hidden']						= 'Message caché: ';

?>