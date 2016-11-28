<!DOCTYPE html>
<?php
//General settings
define('REPLY_MODE','private');
//define('REPLY_MODE','public');
define('ADMIN_EMAIL','admin@null.com');

//Max field length
define('NOM_ML', 35);
define('MSG_OFFRE_ML', 100);
define('MSG_REPLY_ML', 120);
define('TEL_ML', 14);
define('EMAIL_ML', 50);
define('PRIX_ML', 4);
define('PASS_ML', 20);
define('MSG_ML', 400);

//REGEX
define('NOM_REGEX', "/[^A-z\\s-.,'éâêîôûäëïöüÿàèìòù]/");
define('MSG_REGEX', "/[^A-z0-9\\s-.,';$@%!?+=)(éâêîôûäëïöüÿàèìòù]/");
define('VILLE_REGEX', "/[^A-ÿ\s')(-]/");

echo 
('
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8"/>
	<meta property="og:url" content="http://www.caleche.bazaroccidental.org/" />
	<meta property="og:title" content="Calèche Express"/>
	<meta property="og:image" content="http://www.caleche.bazaroccidental.org/img/icon.png"/>
	<meta property="og:description" content="Service de covoiturage gratuit et sans inscription"/>
	<meta name="description" content="Service de covoiturage gratuit et sans inscription"/>
	<link rel="icon" type="image/png" href="http://www.caleche.bazaroccidental.org/img/favicon.png"/>
	
	<title>Calèche Express - Service de covoiturage gratuit</title>
	<link rel="stylesheet" type="text/css" href="index.css">
	<script type="text/javascript" src="awesomplete.js"></script>
</head>
');

echo '<body>';

if (isset($_POST['cancelButton']))
{
	$liftId = json_decode($_POST['cancelButton']);
	
	echo
	('
		<form method="post" action="?" name="cancelForm">
		<div class="msgBox cancelBox">
			<div style="vertical-align:middle; margin: 0 30px">
				Entrez le mot de passe pour annuler l\'annonce:<br><b>' . getLiftInfos($liftId) . '</b>
			</div>
			<div style="vertical-align:middle;">
				<input type="password" name="cancelPass" maxlength="'. PASS_ML .'" id="cancelPass" style="padding: 5px 16px; margin: 2px 0; border: 1px solid #555;">
				<button type="submit" name="cancelConfirm" value="' . $liftId . '">Confirmer</button>
			</div>
		</div></form>
	');
}

if (isset($_POST['cancelConfirm']))
{
	$liftId = json_decode($_POST['cancelConfirm']);
	$db = simplexml_load_file(dirname(__FILE__) . '/db/offre.xml') or die('Error: Cannot create object');
	echo '<div class="msgBox">';
	if (password_verify($_POST['cancelPass'], $db->offre[$liftId]->password))
	{
		$db = simplexml_load_file(dirname(__FILE__) . '/db/offre.xml') or die('Error: Cannot create object');
		unset($db->offre[$liftId]);
		dbWrite('offre', $db);
		echo 'Votre annonce a bien été annulée';
	}
	else
	{
		echo '<img src="img/warning.png" alt="Attention" style="vertical-align: middle">&nbsp;&nbsp;Le mot de passe est invalide';
	}
	echo '</div>';	
}

if(isset($_GET['cancel'])) // ./?cancel=token;email;all
{
	$params = explode(";", $_GET['cancel']); //[0]=token, [1]=email
	
	//VPHP
	if (strlen($params[0]) == 32 && !preg_match("/[^a-f0-9]/",$params[0]) && preg_match("/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/",$params[1]))
	{
		$db = simplexml_load_file(dirname(__FILE__) . '/db/ask.xml') or die('Error: Cannot create object');

		for ($i = (count($db)-1); $i >= 0; $i--)
		{ 
			if ($params[0] == $db->ask[$i]->token && $params[1] == $db->ask[$i]->email)
			{
				$valid_token = true;
				unset($db->ask[$i]);
				dbWrite('ask', $db);
				break;
			}
		}

		$txt = '<div class="msgBox">';
		$txt .= 'Votre alerte de covoiturage a bien été annulée';
		$txt .= '</div>';
		
		if (isset($params[2]) && $params[2] == 'all' && isset($valid_token))
		{
			for ($i = (count($db)-1); $i >= 0; $i--)
			{ 
				if ($params[1] == $db->ask[$i]->email)
				{
					unset($db->ask[$i]);
					dbWrite('ask', $db);
				}
			}
			$txt = '<div class="msgBox">';
			$txt .= 'Vos alertes de covoiturage ont bien été annulées';
			$txt .= '</div>';

		}
		
		if (isset($valid_token))
		{
			echo $txt;
		}
	}
}

if (isset($_POST['replyButton']))
{
	$liftId = json_decode($_POST['replyButton']);
	echo
	('
		<form method="post" id="replyForm" name="replyForm" action="?">
			<div class="msgBox replyForm">
				<div style="vertical-align:middle; margin: 6px 30px">
					Ajouter un commentaire pour l\'annonce:<br><b>' . getLiftInfos($liftId) . '</b>
					<br><button type="submit" name="replyConfirm" value="' . $liftId . '" onClick="return fvalid_replyForm();" style="margin-top: 10px">Confirmer</button>
				</div>
			<div style="vertical-align:middle; padding-left:25px;" >	
				<table class="menu">
	');

	if (REPLY_MODE == "public")
	{
		echo
		('
					<script>var REPLY_MODE = "public";</script>
					<tr>
						<td>Nom</td>
						<td><input type="text" name="replyNom" maxlength="'. NOM_ML .'" id="replyNom">
						<span style="color: red;"> *</span></td>
					</tr>
					<tr>
						<td>Commentaire&nbsp;&nbsp;</td>
						<td><input type="text" name="replyMsg" maxlength="'. MSG_REPLY_ML .'" id="replyMsg">
						<span style="color: red;"> *</span></td>
					</tr>
					<tr>
						<td>Courriel</td>
						<td><input type="text" name="replyMail" maxlength="'. EMAIL_ML .'" id="replyMail"></td>
					</tr>
		');
	}

	else if (REPLY_MODE == 'private')
	{
		$db = simplexml_load_file(dirname(__FILE__) . '/db/offre.xml') or die('Error: Cannot create object');
		echo
		('
					<script>var REPLY_MODE = "private";</script>
						<tr>
							<td>Nom</td>
							<td>' . $db->offre[$liftId]->nom . '</td>
						</tr>
						<tr>
							<td>Commentaire&nbsp;&nbsp;</td>
							<td><input type="text" name="replyMsg" maxlength="'. MSG_REPLY_ML .'" id="replyMsg"><span style="color: red;"> *</span></td>
						</tr>
						<tr>
							<td>Mot de passe</td>
							<td><input type="password" name="replyPass" maxlength="'. PASS_ML .'" id="replyPass"><span style="color: red;"> *</span></td>
						</tr>
		');
	}

	echo
	('
					</table>
				</div>
			</div>
		</form>
	');
}

if (isset($_POST['replyConfirm']))
{
	$liftId = json_decode($_POST['replyConfirm']);
	$db = simplexml_load_file(dirname(__FILE__) . '/db/offre.xml') or die('Error: Cannot create object');
	$timestamp = new DateTime('',new DateTimeZone('America/Montreal'));
	$r = $db->offre[$liftId]->reply->count();
	echo '<div class="msgBox">';

	if (REPLY_MODE == 'public')
	{
		$die = fvalid(array('replyNom','required','nom','maxlength_' . NOM_ML));
		$die .= fvalid(array('replyMsg','required','msg','maxlength_' . MSG_REPLY_ML));
		$die .= fvalid(array('replyMail','email','maxlength_' . EMAIL_ML));
		if ($die) { echo errorMsg($die,false); }
		else
		{
			$db->offre[$liftId]->reply[$r]['timestamp'] = $timestamp->format('j M, H:i');
			$db->offre[$liftId]->reply[$r]['ip'] = $_SERVER['REMOTE_ADDR'];
			$db->offre[$liftId]->reply[$r]['nom'] = $_POST['replyNom'];
			if ($_POST['replyMail']) { $db->offre[$liftId]->reply[$r]['email'] = $_POST['replyMail']; }
			$db->offre[$liftId]->reply[$r] = $_POST['replyMsg'];
			dbWrite('offre', $db);
			echo 'Votre commentaire a bien été ajouté';
		}
	}

	else if (REPLY_MODE == 'private')
	{
		$die = fvalid(array('replyMsg','required','msg','maxlength_' . MSG_REPLY_ML));
		if (!password_verify($_POST['replyPass'], $db->offre[$liftId]->password)) { echo errorMsg('Le mot de passe est invalide',false); }
		else if ($die) { echo errorMsg($die,false); }
		else
		{
			$db->offre[$liftId]->reply[$r]['timestamp'] = $timestamp->format('j M, H:i');
			$db->offre[$liftId]->reply[$r]['ip'] = $_SERVER['REMOTE_ADDR'];
			$db->offre[$liftId]->reply[$r]['nom'] = $db->offre[$liftId]->nom;
			if ($db->offre[$liftId]->email) { $db->offre[$liftId]->reply[$r]['email'] = $db->offre[$liftId]->email; }
			$db->offre[$liftId]->reply[$r] = $_POST['replyMsg'];
			dbWrite('offre', $db);
			echo 'Votre commentaire a bien été ajouté';
		}
	}
	echo '</div>';	
}



if (isset($_POST['offreConfirm']))
{
	//VPHP
	$die = fvalid(array('offreDepart','required'));
	$die .= fvalid(array('offreArrive','required'));
	$die .= fvalid(array('offreHr','required'));
	$die .= fvalid(array('offreMin','required'));
	$die .= fvalid(array('offreNom','required','nom','maxlength_' . NOM_ML));
	$die .= fvalid(array('offreMail','required','email','maxlength_' . EMAIL_ML));
	$die .= fvalid(array('offreMsg','msg','maxlength_' . MSG_OFFRE_ML));
	$die .= fvalid(array('offrePrix','numeric','maxlength_' . PRIX_ML));
	$die .= fvalid(array('offreTel1','telephone','maxlength_' . TEL_ML));
	$die .= fvalid(array('offreTel2','telephone','maxlength_' . TEL_ML));
	$die .= fvalid(array('date','offreYear','offreMonth','offreDay','offreHr','offreMin'));
	$die .= fvalid(array('trajet','offreDepart','offreArrive'));

	if ($die) { echo errorMsg($die,true); }
	else
	{
		$tel1 = str_replace(str_split(' -()'), '', $_POST['offreTel1']);
		$tel2 = str_replace(str_split(' -()'), '', $_POST['offreTel2']);
		$db = simplexml_load_file(dirname(__FILE__) . '/db/offre.xml') or die('Error: Cannot create object');
		$z = $db->count();
		$db->offre[$z]->trajet['depart'] = is_location('offreDepart');
		$db->offre[$z]->trajet['arrive'] = is_location('offreArrive');
		$db->offre[$z]->date['day'] = $_POST['offreDay'];
		$db->offre[$z]->date['month'] = $_POST['offreMonth'];
		$db->offre[$z]->date['year'] = $_POST['offreYear'];
		$db->offre[$z]->date['heure'] = $_POST['offreHr'];
		$db->offre[$z]->date['minute'] = $_POST['offreMin'];
		$db->offre[$z]->nom = $_POST['offreNom'];
		if ($_POST['offreTel1']) { $db->offre[$z]->telephone[] = $tel1; }
		if ($_POST['offreTel2']) { $db->offre[$z]->telephone[] = $tel2; }
		if ($_POST['offrePrix']) { $db->offre[$z]->prix = $_POST['offrePrix']; }
		if ($_POST['offreMail']) { $db->offre[$z]->email = $_POST['offreMail']; }
		if ($_POST['offrePass']) { $db->offre[$z]->password = password_hash($_POST['offrePass'], PASSWORD_BCRYPT); }
		if ($_POST['offreMsg']) { $db->offre[$z]->msg = $_POST['offreMsg']; }

		$timestamp = new DateTime('',new DateTimeZone('America/Montreal'));
		$db->offre[$z]->log['timestamp'] = $timestamp->format('Y-m-d H:i');
		$db->offre[$z]->log['ip'] = $_SERVER['REMOTE_ADDR'];
		$db->offre[$z]->log['browser'] = getBrowser();
		dbWrite('offre', $db);

		$z = $db->count() -1;
		dbMatch($_POST['offreDepart'],$_POST['offreArrive'],$_POST['offreDay'],$_POST['offreMonth'],$_POST['offreYear'], $z);
	}
}

if (isset($_POST['askConfirm']))
{
	$die = false;
	if ($_POST['askDateMode'] == 'specific')
	{
		$day = $_POST['askDay0'] != '' ? 'askDay0' : '31';
		$die .= fvalid(array('date','askYear0','askMonth0',$day));
		
		if (isset($_POST['askAlert']))
		{
			$die .= fvalid(array('askMonth0','required'));
			$die .= fvalid(array('askYear0','required'));
		}
	}
	else if ($_POST['askDateMode'] == 'domain')
	{
		$die .= fvalid(array('askMonth1','required'));
		$die .= fvalid(array('askYear1','required'));
		$die .= fvalid(array('askMonth2','required'));
		$die .= fvalid(array('askYear2','required'));

		if (!$die)
		{
			$day = $_POST['askDay1'] ? 'askDay1' : false;
			$die .= fvalid(array('date','askYear1','askMonth1',$day));
			$day = $day ? $_POST['askDay1'] : '31';
			$dateStart = new DateTime($_POST['askYear1'] . '-' . $_POST['askMonth1'] . '-' . $day,new DateTimeZone('America/Montreal'));

			$day = $_POST['askDay2'] != '' ? 'askDay2' : false; //false -> day=31;
			$die .= fvalid(array('date','askYear2','askMonth2',$day));
			$day = !$day ? '31' : $_POST['askDay2'];
			$dateEnd = new DateTime($_POST['askYear2'] . '-' . $_POST['askMonth2'] . '-' . $day,new DateTimeZone('America/Montreal'));

			if ($dateStart >= $dateEnd) { $die .= 'Date invalide ('. $dateStart->format('Y-m-d') .' >= '. $dateEnd->format('Y-m-d') .')<br>'; }
		}
	}

	if (isset($_POST['askAlert']))
	{
		$die .= fvalid(array('askDepart','required'));
		$die .= fvalid(array('askArrive','required'));
		$die .= fvalid(array('askArrive','required'));
		$die .= fvalid(array('askMail','required','email','maxlength_' . EMAIL_ML));
		$die .= fvalid(array('trajet','askDepart','askArrive'));
	}

	if ($die) { echo errorMsg($die,true); }
	else if (isset($_POST['askAlert']))
	{
		$db = simplexml_load_file(dirname(__FILE__) . '/db/ask.xml') or die('Error: Cannot create object');
		$z = $db->count();
		
		if ($_POST['askDepart']) { $db->ask[$z]->trajet['depart'] = is_location('askDepart'); }
		if ($_POST['askArrive']) { $db->ask[$z]->trajet['arrive'] = is_location('askArrive'); }
		$db->ask[$z]->email = $_POST['askMail'];
		$db->ask[$z]->token = bin2hex(mcrypt_create_iv(16, MCRYPT_DEV_RANDOM));

		if ($_POST['askDateMode'] == 'specific') 
		{
			if ($_POST['askDay0']) { $db->ask[$z]->date0['day'] = $_POST['askDay0']; }
			if ($_POST['askMonth0']) { $db->ask[$z]->date0['month'] = $_POST['askMonth0']; }
			if ($_POST['askYear0']) { $db->ask[$z]->date0['year'] = $_POST['askYear0']; }
		}
		
		else if ($_POST['askDateMode'] == 'domain')
		{  
			if ($_POST['askDay1']) { $db->ask[$z]->date1['day'] = $_POST['askDay1']; }
			if ($_POST['askMonth1']) { $db->ask[$z]->date1['month'] = $_POST['askMonth1']; }
			if ($_POST['askYear1']) { $db->ask[$z]->date1['year'] = $_POST['askYear1']; }
			if ($_POST['askDay2']) { $db->ask[$z]->date2['day'] = $_POST['askDay2']; }
			if ($_POST['askMonth2']) { $db->ask[$z]->date2['month'] = $_POST['askMonth2']; }
			if ($_POST['askYear2']) { $db->ask[$z]->date2['year'] = $_POST['askYear2']; }

		}

		$timestamp = new DateTime('',new DateTimeZone('America/Montreal'));
		$db->ask[$z]->log['timestamp'] = $timestamp->format('Y-m-d H:i');
		$db->ask[$z]->log['ip'] = $_SERVER['REMOTE_ADDR'];
		$db->ask[$z]->log['browser'] = getBrowser();
			
		dbWrite('ask', $db);
		echo ('<div class="msgBox"><b>Votre recherche a bien été enregistrée.</b><br>Vous recevrez un courriel lorsque de nouvelles offres correspondant à ces critères seront ajoutées.</div>');
	}
}

	//<img src="img/pig.png" class="lien pigIcon" onClick="window.open(\'https://www.paypal.me/caleche\', \'_blank\');" alt="Dons">

if (isset($_POST['contactConfirm']))
{
	//VPHP
	$die = fvalid(array('contactNom','required','nom','maxlength_' . NOM_ML));
	$die .= fvalid(array('contactMail','required','email','maxlength_' . EMAIL_ML));
	$die .= fvalid(array('contactMsg','required','msg','maxlength_' . MSG_ML));
	if (isset($_POST['botFlag'])) { $die .= "Vous devez activer le javascript pour utiliser ce formulaire"; }

	if ($die)
	{
		echo errorMsg($die,true);
	}
	else
	{
		echo '<script>var activePage = "sentMsg";</script>';
		$db = simplexml_load_file(dirname(__FILE__) . '/db/offre.xml') or die('Error: Cannot create object');
		
		$txt = $_POST['contactMsg'] . '<br>';
		if ($_POST['contactConfirm'] == 'caleche')
		{
			$to = ADMIN_EMAIL;
			$subject = 'Calèche Express: Contact form';
			$timestamp = new DateTime('',new DateTimeZone('America/Montreal'));
			$txt .= '<br>Timestamp: ' . $timestamp->format('Y-m-d H:i');
			$txt .= '<br>Browser: ' . getBrowser();
			$txt .= '<br>IP: ' . $_SERVER['REMOTE_ADDR'];
		}

		else
		{
			$liftId = json_decode($_POST['liftId']);

			if ($_POST['contactConfirm'] == 'reply')
			{
				$replyId = json_decode($_POST['replyId']);
				$to = $db->offre[$liftId]->reply[$replyId]['email'];
			}
			else if ($_POST['contactConfirm'] == 'lift')
			{
				$to = $db->offre[$liftId]->email;
			}
			
			$subject = 'Calèche: ';
			$subject .= isset($_POST['replyId']) ? 'Réponse à votre commentaire' : getLiftInfos($liftId);
		}

		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		$headers .= 'From: '.$_POST['contactNom'].' <'.$_POST['contactMail'].'>' . "\r\n";
		mail($to,$subject,$txt,$headers);

		$timestamp = new DateTime('',new DateTimeZone('America/Montreal'));
		$db = simplexml_load_file(dirname(__FILE__) . '/db/output-log.xml') or die('Error: Cannot create object');
		$z = $db->count();
		$db->output[$z]->log['timestamp'] = $timestamp->format('Y-m-d H:i');
		$db->output[$z]->log['ip'] = $_SERVER['REMOTE_ADDR'];
		$db->output[$z]->log['from'] = $_POST['contactNom'];
		$db->output[$z]->log['to'] = $to;
		$db->output[$z]->log['subject'] = $subject;
		$db->output[$z]->content = $_POST['contactMsg'];
		dbWrite('output-log', $db);

		echo
		('
			<div class="msgBox">
			 Votre message a bien été envoyé
			<script>var activePage = "main";</script>
			</div>
		');
	}
}

echo
('
	<div class="main">
	<a href="?msg"><img src="img/contact.png" class="lien contactIcon" alt="Contact"></a>
	<div class="logo"><a id="logo" href="?"></a>(beta)<br></div><br>
');

		
if (isset($_GET['msg']))
{
	echo 
	('
		<script>var activePage = "msg";</script>
		<form method="post" name="contactForm" action="?">
			<div class="msgBox" style="border:0; justify-content: center; background: transparent">
	');

	$liftId = (isset($_GET['msg'])) ? json_decode($_GET['msg']) : false;
	$replyId = (isset($_GET['replyButton'])) ? json_decode($_GET['replyButton']) : false;
	$db = simplexml_load_file(dirname(__FILE__) . '/db/offre.xml') or die('Error: Cannot create object');

	if (is_numeric($liftId) && $liftId > -1 && $liftId < $db->count()) //valid lift request
	{
		echo '<input type="text" id="liftId" name="liftId" value="' . $liftId . '" style="display: none">';
		
		if (is_numeric($replyId) && $replyId > -1 && $replyId < $db->offre[$liftId]->reply->count()) //valid reply request
		{
			$nom = $db->offre[$liftId]->reply[$replyId]['nom'];
			$msgTo = 'reply';
			echo '<span style="font-size: large;">Contactez <b>' . $nom . '</b> concernant le commentaire :<br><br><b>"' . $db->offre[$liftId]->reply[$replyId] . '"</b></span>';
			echo '<input type="text" id="replyId" name="replyId" value="' . $replyId . '" style="display: none">';
		}
		
		else //no or invalid reply request, but valid liftId
		{
			$nom = $db->offre[$liftId]->nom;
			$msgTo = 'lift';
			echo '<span style="font-size: large;">Contactez <b>' . $nom . '</b> concernant l\'annonce :<br><br><b>' . getLiftInfos($liftId) . '</b></span>';
		}
	}

	else //invalid request; show help form
	{
		echo '<span style="font-size: large;">Problème? Bug? Suggestion?<br><br><b>Contactez Calèche Express !</b></span>';
		$msgTo = 'caleche';
	}

	echo
	('
		<br><br><br><input type="text" placeholder="Votre Nom" name="contactNom" maxlength="'. NOM_ML .'" id="contactNom" style="width:450px; margin: 1px 0;">
		<br><input type="text" placeholder="Votre Email" name="contactMail" maxlength="'. EMAIL_ML .'" id="contactMail" style="width:450px; margin: 1px 0;">
		<br><textarea rows="8" cols="57" placeholder="Message" name="contactMsg" maxlength="'. MSG_ML .'" id="contactMsg" style="width:450px; margin: 1px 0;"></textarea>
		<br><br><button type="submit" name="contactConfirm" id="contactConfirm" value="' . $msgTo . '" onClick="return fvalid_contactForm();" disabled="true">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Envoyer&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</button>

		<input type="text" id="botFlag" name="botFlag" class="hors-de-vue">
		<noscript>
			<br><br><br>
			<div style="text-align:center; font-size: small;">
				Par mesure de sécurité, vous devez activer le javascript afin d\'utiliser ce formulaire. Merci de votre compréhension.
				<br>
			</div>
		</noscript>
		<script type="text/javascript">
			botTimer = 4
			document.getElementById("contactConfirm").innerHTML = "&nbsp;&nbsp;" + (botTimer+1) + "&nbsp;&nbsp;";
			var botInterval = setInterval(function()
			{
				document.getElementById("contactConfirm").innerHTML = "&nbsp;&nbsp;" + botTimer + "&nbsp;&nbsp;";
				botTimer--;
				if (botTimer < 0)
				{
					document.getElementById("botFlag").disabled = true;
					document.getElementById("contactConfirm").innerText = "Envoyer";
					document.getElementById("contactConfirm").disabled = false;
					botTimer = false;
					clearInterval(botInterval);
				}
			},1000);
		</script>
	');

	echo
	('
			</div>
		</form>
	');
}
	
if (isset($_POST['askConfirm']))
{
	if ($die) { dbRead(false,false,false,false,false,false,false,false); }
	else
	{
		if (!isset($_POST['askDepart'])) { $depart = false; } else { $depart = is_location('askDepart'); }
		if (!isset($_POST['askArrive'])) { $arrive = false; } else { $arrive = is_location('askArrive'); }

		if ($_POST['askDateMode'] == 'specific') 
		{
			if (!isset($_POST['askDay0'])) { $day = false; } else { $day = $_POST['askDay0']; }
			if (!isset($_POST['askMonth0'])) { $month = false; } else { $month = $_POST['askMonth0']; }
			if (!isset($_POST['askYear0'])) { $year = false; } else { $year = $_POST['askYear0']; }
			dbRead($depart,$arrive,$day,$month,$year,false,false,false);
		}
		else if ($_POST['askDateMode'] == 'domain')
		{  
			if (!isset($_POST['askDay1'])) { $day = false; } else { $day = $_POST['askDay1']; }
			if (!isset($_POST['askMonth1'])) { $month = false; } else { $month = $_POST['askMonth1']; }
			if (!isset($_POST['askYear1'])) { $year = false; } else { $year = $_POST['askYear1']; }
			if (!isset($_POST['askDay2'])) { $_day = false; } else { $_day = $_POST['askDay2']; }
			if (!isset($_POST['askMonth2'])) { $_month = false; } else { $_month = $_POST['askMonth2']; }
			if (!isset($_POST['askYear2'])) { $_year = false; } else { $_year = $_POST['askYear2']; }
			dbRead($depart,$arrive,$day,$month,$year,$_day,$_month,$_year);
		}
	}
} else if (!isset($_GET['msg']) && !isset($_POST['askConfirm'])) { dbRead(false,false,false,false,false,false,false,false); }

function showForm()
{
	$today = new DateTime('');
	$y = $today->format('Y');
	$currentYears = '<option value="' . $y . '">' . $y . '</option><option value="' . ($y+1) . '">' . ($y+1) . '</option>';

	echo '<ul id="villes" style="display:none; visibility:hidden">'; require_once('db/villes.ul'); echo '</ul>';
	echo
	('
	<script>var activePage = "main";</script>
	<div style="display:flex; justify-content:center;">
		<form method="post" name="offreForm" action="?">
			<table class="menu">
				<tr>
					<td colspan="2"><span style="font-size: xx-large;">Offrir un lift</span></td>
				</tr>
				<tr>
					<td colspan="2"><br></td>
				</tr>
				<tr>
					<td>Lieu de départ</td>
					<td><input class="awesomplete" data-list="#villes" type="text" name="offreDepart" id="offreDepart"><span style="color: red;"> *</span></td>
				</tr>
				<tr>
					<td>Lieu d\'arrivée</td>
					<td><input class="awesomplete" data-list="#villes" type="text" name="offreArrive" id="offreArrive"><span style="color: red;"> *</span></td>
				</tr>
				<tr>
					<td>Date</td>
					<td><select name="offreDay" id="offreDay" class="small">
					<option value="01">01</option><option value="02">02</option><option value="03">03</option><option value="04">04</option><option value="05">05</option><option value="06">06</option><option value="07">07</option><option value="08">08</option><option value="09">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option>
						</select>
					<select name="offreMonth" id="offreMonth" class="large">
					<option value="01">Janvier</option><option value="02">Février</option><option value="03">Mars</option><option value="04">Avril</option><option value="05">Mai</option><option value="06">Juin</option><option value="07">Juillet</option><option value="08">Août</option><option value="09">Septembre</option><option value="10">Octobre</option><option value="11">Novembre</option><option value="12">Décembre</option>
					</select>
					<select name="offreYear" id="offreYear" class="medium">
					' . $currentYears . '
					</select>
					<span style="color: red;"> *</span></td>
				</tr>
				<tr>
					<td>Heure de départ&nbsp;&nbsp;</td>
					<td><select name="offreHr" id="offreHr" class="small">
					<option value=""></option><option value="00">00</option><option value="01">01</option><option value="02">02</option><option value="03">03</option><option value="04">04</option><option value="05">05</option><option value="06">06</option><option value="07">07</option><option value="08">08</option><option value="09">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option>
					</select>
					:
					<select name="offreMin" id="offreMin" class="small">
					<option value=""></option><option value="00">00</option><option value="10">10</option><option value="20">20</option><option value="30">30</option><option value="40">40</option><option value="50">50</option>
					</select>
					<span style="color: red;"> *</span></td>
				</tr>
				<tr>
					<td>Nom</td>
					<td><input type="text" name="offreNom" maxlength="'. NOM_ML .'" id="offreNom">
					<span style="color: red;"> *</span></td>
				</tr>
				<tr>
					<td>Courriel</td>
					<td><input type="text" name="offreMail" maxlength="'. EMAIL_ML .'" id="offreMail">
					<span style="color: red;"> *</span></td>
				</tr>
				<tr>
					<td>Mot de passe</td>
					<td><input type="password" name="offrePass" maxlength="'. PASS_ML .'" id="offrePass" placeholder="Recommandé"> *</td>
				</tr>
				<tr>
					<td colspan="2">
					<span style="font-size: small;">*Le mot de passe permet d\'annuler l\'annonce avant son échéance</span>
					</td>
				</tr>
				
				<tr>
					<td colspan="2"><br><hr></td>
				</tr>
				
				<tr>
					<td>Commentaire</td>
					<td><input type="text" maxlength="'. MSG_OFFRE_ML .'" placeholder="Facultatif" name="offreMsg" value="" id="offreMsg"></td>
				</tr>
				<tr>
					<td>Téléphone 1</td>
					<td><input type="text" placeholder="Facultatif" maxlength="'. TEL_ML .'" name="offreTel1" id="offreTel1"></td>
				</tr>
				<tr>
					<td>Téléphone 2</td>
					<td><input type="text" placeholder="Facultatif" maxlength="'. TEL_ML .'" name="offreTel2" id="offreTel2"></td>
				</tr>
				<tr>
					<td>Contribution</td>
					<td><input type="text" maxlength="'. PRIX_ML .'" placeholder="Gratuit" name="offrePrix" class="prix" value="" id="offrePrix">&nbsp;$</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td style="text-align:right"><input type="submit" name="offreConfirm" value="&nbsp;&nbsp;Envoyer&nbsp;&nbsp;" onClick="return fvalid_offreForm();" style="margin-right: 12px;"></td>
				</tr>
			</table>
		</form>

		<span style="min-width:100px;"></span>

		<form method="post" name="askForm" id="askForm" action="?">
			<table class="menu">
				<tr>
					<td colspan="3"><span style="font-size: xx-large;">Trouver un lift</span></td>
				</tr>
				<tr>
					<td colspan="3"><br></td>
				</tr>
				<tr>
					<td colspan="2">Lieu de départ&nbsp;&nbsp;</td>
					<td><input class="awesomplete" data-list="#villes" type="text" placeholder="Tous" name="askDepart" id="askDepart"><span style="color: red;" name="alertAsterisk"></span></td>
				</tr>
				<tr>
					<td colspan="2">Lieu d\'arrivée</td>
					<td><input class="awesomplete" data-list="#villes" type="text" placeholder="Tous" name="askArrive" id="askArrive"><span style="color: red;" name="alertAsterisk"></span></td>
				</tr>
				<tr>
					<td colspan="3"><br></td>
				</tr>
				<tr>
					<td>
						<input type="radio" name="askDateMode" id="askDateMode0" value="specific" checked="true"></td>
						<td>Le</td>
						<td><select name="askDay0" id="askDay0" class="small">
						<option value="">*</option><option value="1">01</option><option value="2">02</option><option value="3">03</option><option value="4">04</option><option value="5">05</option><option value="6">06</option><option value="7">07</option><option value="8">08</option><option value="9">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option>
						</select>
						<select name="askMonth0" id="askMonth0" class="large">
						<option value="">*</option><option value="01">Janvier</option><option value="02">Février</option><option value="03">Mars</option><option value="04">Avril</option><option value="05">Mai</option><option value="06">Juin</option><option value="07">Juillet</option><option value="08">Août</option><option value="09">Septembre</option><option value="10">Octobre</option><option value="11">Novembre</option><option value="12">Décembre</option>
						</select>
						<select name="askYear0" id="askYear0" class="medium">
						' . $currentYears . '
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<input type="radio" name="askDateMode" id="askDateMode1" value="domain"></td>
						<td>Entre le</td>
						<td><select name="askDay1" id="askDay1" class="small">
						<option value="">*</option><option value="1">01</option><option value="2">02</option><option value="3">03</option><option value="4">04</option><option value="5">05</option><option value="6">06</option><option value="7">07</option><option value="8">08</option><option value="9">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option>
						</select>
						<select name="askMonth1" id="askMonth1" class="large">
						<option value=""></option><option value="01">Janvier</option><option value="02">Février</option><option value="03">Mars</option><option value="04">Avril</option><option value="05">Mai</option><option value="06">Juin</option><option value="07">Juillet</option><option value="08">Août</option><option value="09">Septembre</option><option value="10">Octobre</option><option value="11">Novembre</option><option value="12">Décembre</option>
						</select>
						<select name="askYear1" id="askYear1" class="medium">
						' . $currentYears . '
						</select>
						<span style="color: red;" name="alertAsterisk"></span>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>et le</td>
					<td>
						<select name="askDay2" id="askDay2" class="small">
						<option value="">*</option><option value="1">01</option><option value="2">02</option><option value="3">03</option><option value="4">04</option><option value="5">05</option><option value="6">06</option><option value="7">07</option><option value="8">08</option><option value="9">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option>
						</select>
						<select name="askMonth2" id="askMonth2" class="large">
						<option value=""></option><option value="01">Janvier</option><option value="02">Février</option><option value="03">Mars</option><option value="04">Avril</option><option value="05">Mai</option><option value="06">Juin</option><option value="07">Juillet</option><option value="08">Août</option><option value="09">Septembre</option><option value="10">Octobre</option><option value="11">Novembre</option><option value="12">Décembre</option>
						</select>
						<select name="askYear2" id="askYear2" class="medium">
						' . $currentYears . '
						</select>
						<span style="color: red;" name="alertAsterisk"></span>
					</td>
				</tr>
				<tr>
					<td colspan="3"><br></td>
				</tr>
				<tr>
					<td colspan="2"><input type="checkbox" name="askAlert" id="askAlert" style="margin-right:10px;"></td>
					<td style="align:justify; font-size:small">Recevoir une notification pour les <br>
					nouvelles offres correspondantes</td>
				</tr>
				<tr>
					<td colspan="2">Courriel</td>
					<td><input type="text" name="askMail" maxlength="'. EMAIL_ML .'" id="askMail"><span style="color: red;" name="alertAsterisk"></span></td>
				</tr>
				<tr>
					<td colspan="3"><br></td>
				</tr>
				<tr>
					<td colspan="2"></td>
					<td><input type="submit" name="askConfirm" value="Rechercher" onClick="return fvalid_askForm()"></td>
				</tr>
			</table>
		</form>
	</div>
	');
}

function dbRead($depart,$arrive,$day,$month,$year,$_day,$_month,$_year)
{
	dbClean('offre');

	$db = simplexml_load_file(dirname(__FILE__) . '/db/offre.xml') or die('Error: Cannot create object'); //$_SERVER['DOCUMENT_ROOT'] . 
	$info = false;
	$child_id = 0;
	foreach($db->children() as $child)
	{
		$z = $y = 0;
		
		if ($depart) { $y++; }
		if ($arrive) { $y++; }
		if ($day) { $y++; }
		if ($month) { $y++; }
		if ($year) { $y++; }
		if ($_day) { $y++; }
		if ($_month) { $y++; }
		if ($_year) { $y++; }

		if ($child->trajet['depart'] == $depart) { $z++; }
		if ($child->trajet['arrive'] == $arrive) { $z++; }

		if (isset($_POST['askDateMode']) && $_POST['askDateMode'] == 'domain')
		{
			$entryDate = new DateTime($child->date['year'] . '-' . $child->date['month'] . '-' . $child->date['day'],new DateTimeZone('America/Montreal'));
			if ($month && $year)
			{
				$dayStart = $_POST['askDay1'] != '' ? $_POST['askDay1'] : '01';
				$dateStart = new DateTime($_POST['askYear1'] . '-' . $_POST['askMonth1'] . '-' . $dayStart,new DateTimeZone('America/Montreal'));

				if ($entryDate >= $dateStart)
				{
					if ($day) { $z++; }
					if ($month) { $z++; }
					if ($year) { $z++; }
				}
			}
			if ($_month && $_year)
			{
				$dayEnd = $_POST['askDay2'] != '' ? $_POST['askDay2'] : '31';
				$dateEnd = new DateTime($_POST['askYear2'] . '-' . $_POST['askMonth2'] . '-' . $dayEnd,new DateTimeZone('America/Montreal'));

				if ($entryDate <= $dateEnd)
				{
					if ($_day) { $z++; }
					if ($_month) { $z++; }
					if ($_year) { $z++; }
				}
			}
		}

		else
		{
			if ($child->date['day'] == $day) { $z++; }
			if ($child->date['month'] == $month) { $z++; }
			if ($child->date['year'] == $year) { $z++; }
		}
		
		if ($z == $y) //La requete correspond à  l'entrée
		{	
			$date = $child->date['day'] . '/' . $child->date['month'] . '/' . $child->date['year'];
			$info = $info . $date . ':' . $child_id . '<br>';
		}
		$child_id++;

	}

	echo ('
		<div><br></div>
		<form method="post" action="?">
		<table class="lift">
		<span style="font-size: large;">
		');

	$month_txt = array('Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre');
	$txt = 'Départs';
	
	if ($depart || $arrive)
	{
		if ($depart) { $txt .= ' de <b>' . $depart . '</b>'; }
		if ($arrive) { $txt .= ' vers <b>' . $arrive . '</b>'; }
	}

	if ($day && $month && $year && $_day && $_month && $_year)
	{
		$txt .= ' entre le ' . $day . ' ' . $month_txt[($month-1)] . ' ' . $year;
		$txt .= ' et le ' . $_day . ' ' . $month_txt[($_month-1)] . ' ' . $_year;
	}
	else if (!$day && $month && $year && $_day && $_month && $_year)
	{
		$txt .= ' entre ' . $month_txt[($month-1)] . ' ' . $year . ' et le ' . $_day . ' ' . $month_txt[($_month-1)] . ' ' . $_year;
	}
	else if ($day && $month && $year && !$_day && $_month && $_year)
	{
		$txt .= ' entre le ' . $day . ' ' . $month_txt[($month-1)] . ' ' . $year . ' et ' . $month_txt[($_month-1)] . ' ' . $_year;
	}
	else if (!$day && $month && $year && !$_day && $_month && $_year)
	{
		$txt .= ' entre ' . $month_txt[($month-1)] . ' ' . $year . ' et ' . $month_txt[($_month-1)] . ' ' . $_year;
	}
	else if ($day || $month || $year)
	{
		if ($day) { $txt .= ' le '; }
		else if ($month) { $txt .= ' en '; }

		if ($day) { $txt .= $day; }
		if ($month) { $txt .= ' ' . $month_txt[($month-1)]; }
		if ($year && !$month && !$day) { $txt .= ' en ' . $year; }
		else if ($year) { $txt .= ' ' . $year; }
	}

	if ($txt != 'Départs') { echo '<p align="left">'. $txt .' :</b></p>'; }
	echo '</span>';
	if ($info) { dbSort($info); }

	else
	{
		if ($year) { echo '<p align="center"><br><i>Aucun résultat ne correspond à  votre recherche</i></p>'; }
		else { echo '<p align="center"><br><i>Aucune offre n\'est disponible pour le moment</i></p>'; }
	}

	echo 
		('
			</table></form>
			<div style="margin-top: 50px">
				<br><hr><br>
			</div>
		');

	showForm();
	echo 
	('
		<br><br><br>
		<div style="text-align:center; font-size: small;">
			Calèche Express est en développement, s\'il vout plaît signalez toutes anomalies en utilisant <a href="?msg">ce formulaire</a>. Merci de votre participation :-)
			<br>
		</div>
	');

}

function dbSort($info) 
{
	$rows = explode("<br>", $info);
	foreach($rows as $row => $data) // sauvegarde des infos dans une matrice ligne/colonne
	{
		if ($data) 
		{
			$row_data = preg_split("/(.*)(:.*)/", $data, -1, PREG_SPLIT_DELIM_CAPTURE);
			$ligne[$row]['date'] = $row_data[1];
			$ligne[$row]['infos'] = $row_data[2];
		}
	}
	usort($ligne, 'date_compare');
	$x = false;
	foreach($ligne as $row) 
	{
		$month_txt = array('Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre');
		$mh = $month_txt[(substr($row['date'], 3, 2)-1)];
		$yr = substr($row['date'], 6, 4);
		$dy = substr($row['date'], 0, 2);
		$liftId = json_decode(substr($row['infos'], 1));
		$db = simplexml_load_file(dirname(__FILE__) . '/db/offre.xml') or die('Error: Cannot create object');
		if ($db->offre[$liftId]->prix) { $prix = $db->offre[$liftId]->prix . '$'; } else { $prix = '<span style="color:red;">Gratuit !</span>'; }
		
		echo '<tr><td>';
		
		if (REPLY_MODE == 'public' || (REPLY_MODE == 'private' && $db->offre[$liftId]->password))
		{
			echo '<button type="submit" name="replyButton" value="' . $liftId . '" class="listButton"><img class="lien" src="img/ic_edit_black_24dp_1x.png" alt="Ajouter un commentaire">&nbsp;</button>';
		}	
		
		if ($db->offre[$liftId]->password)
		{ 
			echo '<button type="submit" name="cancelButton" value="' . $liftId . '" class="listButton"><img class="lien" src="img/ic_delete_black_24dp_1x.png" alt="Annuler l\'entrée">&nbsp;</button>';
		}
		echo '</td>';
		
		echo '<td>' . $dy . ' ' . $mh . ' ' . $yr . '</td>';
		echo '<td><b>' . $db->offre[$liftId]->trajet['depart'] . '</b> vers <b>' . $db->offre[$liftId]->trajet['arrive'] . '</b></td>';
		echo '<td>';
	
		if ($db->offre[$liftId]->email)
		{
			echo 'Départ à <b>' . $db->offre[$liftId]->date['heure'] . 'h' . $db->offre[$liftId]->date['minute'] . '</b> avec <a href="?msg=' . $liftId . '">' . $db->offre[$liftId]->nom . '</a>';
		} else {
			echo 'Départ à <b>' . $db->offre[$liftId]->date['heure'] . 'h' . $db->offre[$liftId]->date['minute'] . '</b> avec ' . $db->offre[$liftId]->nom;
		}
		
		if ($db->offre[$liftId]->msg) { echo '<div class="liftCommentaire"><br>' . $db->offre[$liftId]->msg .'</div>'; }
		echo '</td><td>';
		if ($x == false) { echo '<noscript style="font-size:small; text-align:center; width:100%">Numéros de téléphone masqués,<br>vous devez activer le javascript</noscript>'; }
		
		if ($db->offre[$liftId]->telephone[0])
		{
			$tel1 = substr($db->offre[$liftId]->telephone[0], 0, 3) . '-' . substr($db->offre[$liftId]->telephone[0], 3, 3) . '-' . substr($db->offre[$liftId]->telephone[0], 6, 4);
			echo '<b>' . munge($tel1) . '</b>';
		}
		
		if ($db->offre[$liftId]->telephone[1])
		{
			$tel2 = substr($db->offre[$liftId]->telephone[1], 0, 3) . '-' . substr($db->offre[$liftId]->telephone[1], 3, 3) . '-' . substr($db->offre[$liftId]->telephone[1], 6, 4);
			echo '<br><b>' . munge($tel2) . '</b>';
		}
		
		echo '</td><td>' . $prix . '</td></tr>';
	
		$r = 0;
		while($r < $db->offre[$liftId]->reply->count())
		{
			$replyNom = ($db->offre[$liftId]->reply[$r]['email']) ? '<a href="?msg=' . $liftId . '&reply=' . $r . '">' . $db->offre[$liftId]->reply[$r]['nom'] . '</a>' : $db->offre[$liftId]->reply[$r]['nom'];
			echo '<tr class="noHover"><td><span style="font-size: small;">' . $db->offre[$liftId]->reply[$r]['timestamp'] . '</span><br></td>
				<td colspan="5"><span style="font-size: small;">' . $replyNom .': '. $db->offre[$liftId]->reply[$r] . '</span><br></td></tr>';
			
			$r++;
		}
		if ($r > 0) { echo '<tr class="noHover"><td style="border:0"></td></tr>'; }
		
		$x = true;
	}

}

function date_compare($b, $a)
{
	$t1 = date_create_from_format("j/m/Y", $a['date'])->getTimestamp();
	$t2 = date_create_from_format("j/m/Y", $b['date'])->getTimestamp();
	return $t2 - $t1; //Plus récent au plus ancien
}


function fvalid($rules)
{
	$die = false;

	//Synthax: fvalid('trajet',depart,arrive)
	if ($rules[0] == 'trajet')
	{
		$depart = $_POST[$rules[1]];
		$arrive = $_POST[$rules[2]];
		if (!strcmp($depart,$arrive)) { $die .= 'Trajet invalide<br>'; }
		if (preg_match(VILLE_REGEX, $depart) || preg_match(VILLE_REGEX, $arrive)) { $die .= 'Nom de ville invalide<br>'; }

		if ($depart && is_location($rules[1]) == false) { $die .= 'Depart introuvable<br>'; }
		if ($arrive && is_location($rules[2]) == false) { $die .= 'Destination introuvable<br>'; }
	}

	//Synthax: fvalid('date',année1,mois2,jour3,heure4,min5)
	else if ($rules[0] == 'date')
	{
		for ($x=1; $x<count($rules); $x++)
		{
			if ($rules[$x])
			{
				if (is_numeric($rules[$x])) { $xVal = $rules[$x]; }
				else { $xVal = $_POST[$rules[$x]]; }

				switch ($x)
				{
					case 1: $year = $xVal; break;
					case 2: $month = $xVal; break;
					case 3: $day = $xVal; break;
					case 4: $hr = $xVal; break;
					case 5: $min = $xVal; break;
				}
			}
		}

		$month = isset($month) && $month ? $month : '12';
		$day = isset($day) && $day ? $day : '31';
		$hr = isset($hr) && $hr ? $hr : '23';
		$min = isset($min) && $min ? $min : '59';

		$dateCompare = new DateTime($year . '-' . $month . '-' . $day . ' ' . $hr . ':' . $min, new DateTimeZone('America/Montreal'));
		$today = new DateTime('',new DateTimeZone('America/Montreal'));
		if (isset($dateCompare) && $today > $dateCompare) { $die .= 'Date dépassée ('. $dateCompare->format('Y-m-d') .')<br>'; }
	}
			
	//Synthax: fvalid(field,rules)
	else if ($rules)
	{
		$field = $rules[0];
		$i = 1;
		if (isset($_POST[$field]))
		{
			foreach ($rules as $rule)
			{
				if ($rule == 'required' && $_POST[$field] == '') { $die .= 'Incomplet (' . $field . '), '; }
				if ($_POST[$field] != '')
				{
					if ($rule == 'nom' && preg_match(NOM_REGEX, $_POST[$field])) { $die .= 'Nom invalide, '; }
					if ($rule == 'msg' && preg_match(MSG_REGEX, $_POST[$field])) { $die .= 'Commentaire invalide, '; }
					if ($rule == 'email' && !filter_var($_POST[$field], FILTER_VALIDATE_EMAIL)) { $die .= 'Email invalide, '; }
					if ($rule == 'numeric' && !is_numeric($_POST[$field])) { $die .= 'Caractères non-numériques (' . $field . '), '; }
					if ($rule == 'telephone')
					{
						$tel = str_replace(str_split(' -()'), '', $_POST[$field]);
						if (!is_numeric($tel) || strlen($tel) != 10) { $die .= 'Téléphone invalide (' . $field . '), '; }
					}
					if (substr($rule,0,10) == 'minlength_' && strlen($_POST[$field]) < substr($rule,10)) { $die .= 'Entrée trop courte (' . $field . '), '; }
					if (substr($rule,0,10) == 'maxlength_' && strlen($_POST[$field]) > substr($rule,10)) { $die .= 'Entrée trop longue (' . $field . '), '; }
					}
				$i++;
			}
		}
	}

	return $die;
}

function is_location($field)
{
	if ($_POST[$field] != '')
	{
		$evaluate = homostr($_POST[$field]);
		$html = new DOMDocument();
		$html->loadHTMLFile("db/villes.ul");
		$match = false;
		foreach($html->getElementsByTagName('li') as $li)
		{
			
			$compare = utf8_decode($li->nodeValue);
			$compare = homostr($compare);
			if ($compare == $evaluate)
			{
				return utf8_decode($li->nodeValue);
				break;
			}
		}
	} else { return false; }
	if ($match == false) { return false; }
}

function homostr($ville)
{
	mb_internal_encoding('UTF-8');
	$ville = mb_strtolower($ville);
	$ville = preg_replace('/\(.*?\)/', '', $ville);
	$ville = str_replace(array('é','è','ê','ë'), 'e', $ville);
	$ville = str_replace(array('ô','ò','ö'), 'o', $ville);
	$ville = str_replace(array('î','ï','ì'), 'i', $ville);
	$ville = str_replace(array('û','ü','ù'), 'u', $ville);
	$ville = str_replace(array('â','à','ä'), 'a', $ville);
	$ville = str_replace('ÿ', 'y', $ville);
	$ville = str_replace(array('sainte-','sainte ','saint-','saint ','ste-','ste ','st-','st '), 'st!', $ville);
	$ville = preg_replace("/[-'\s]/", "", $ville);
	return($ville);	
}

function getLiftInfos($liftId)
{
	$db = simplexml_load_file(dirname(__FILE__) . '/db/offre.xml') or die('Error: Cannot create object');
	$month_txt = array('Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre');
	
	return
	(
		$db->offre[$liftId]->date['day'] . ' ' . $month_txt[($db->offre[$liftId]->date['month']-1)] . ' ' . $db->offre[$liftId]->date['year']
		. ', ' . $db->offre[$liftId]->trajet['depart'] . ' vers ' . $db->offre[$liftId]->trajet['arrive'] 
		. ' à ' . $db->offre[$liftId]->date['heure'] . 'h' . $db->offre[$liftId]->date['minute']
	);
}

function errorMsg($msg,$box)
{
	if ($box) { $txt = '<div class="msgBox">'; }
	else { $txt = '<div class="transparentBox">'; }
	
	$txt .=
	('
		<img src="img/warning.png" alt="Attention" style="vertical-align: middle">&nbsp;
		'.$msg.'<noscript><br>Il est recommandé d\'activer le javascript pour profiter de la validation en temps réel</noscript>
		</div>
	');
	return $txt;
}

function dbWrite($db_name, $db)
{
	$dom = new DOMDocument('1.0');
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	$dom->loadXML($db->asXML());
	$dom->save(dirname(__FILE__) . '/db/'. $db_name .'.xml');
	
	$date = new DateTime('',new DateTimeZone('America/Montreal'));
	$today_zip = 'db/archive/' . $date->format('j_M_Y') . '.zip';
	if (!file_exists($today_zip)) {
		$zip = new ZipArchive();
		$zip->open($today_zip, ZIPARCHIVE::CREATE);
		$zip->addFile(dirname(__FILE__) . '/db/offre.xml', 'offre.xml');
		$zip->addFile(dirname(__FILE__) . '/db/ask.xml', 'ask.xml');
		$zip->addFile(dirname(__FILE__) . '/index.php', 'index.php');
		$zip->addFile(dirname(__FILE__) . '/index.js', 'index.js');
		$zip->addFile(dirname(__FILE__) . '/events.js', 'events.js');
		$zip->addFile(dirname(__FILE__) . '/index.css', 'index.css');
		$zip->close();
	}
}

function dbClean($db_name)
{
	$db = simplexml_load_file(dirname(__FILE__) . '/db/'. $db_name .'.xml') or die('Error: Cannot create object');
	$i = count($db) - 1;
	$today = new DateTime('',new DateTimeZone('America/Montreal'));
	$date = false;
	
	for ($i; $i >= 0; $i--)
	{
		if ($db->$db_name[$i]->date['year']) { $date = 'date'; }
		else if ($db->$db_name[$i]->date0['year']) { $date = 'date0'; }
		else if ($db->$db_name[$i]->date2['year']) { $date = 'date2'; }

		if ($date)
		{
			$year = $db->$db_name[$i]->$date['year'];
			$month = $db->$db_name[$i]->$date['month'] ? $db->$db_name[$i]->$date['month'] : '12';
			$day = $db->$db_name[$i]->$date['day'] ? $db->$db_name[$i]->$date['day'] : '31';
			$hr = $db->$db_name[$i]->$date['heure'] ? $db->$db_name[$i]->$date['heure'] : '23';
			$min = $db->$db_name[$i]->$date['minute'] ? $db->$db_name[$i]->$date['minute'] : '59';

			$dbDate = new DateTime($year . '-' . $month . '-' . $day . ' ' . $hr . ':' . $min, new DateTimeZone('America/Montreal'));
			if ($today > $dbDate)
			{
				//echo '<br>deleted: '. $dbDate->format('Y-m-d') . ' (' . $date . ')' . $db_name . '.xml:' . $i;
				unset($db->$db_name[$i]);
				dbWrite($db_name, $db);
			}
			/*else
			{
				echo '<br>kept: '. $dbDate->format('Y-m-d') . ' (' . $date . ')' . $db_name . '.xml:' . $i;

			}*/
		}
	}
}

function dbMatch($offreDepart,$offreArrive,$offreDay,$offreMonth,$offreYear,$liftId)
{

	dbClean('ask');
	$db = simplexml_load_file(dirname(__FILE__) . '/db/ask.xml') or die('Error: Cannot create object');
	$child_id = -1;
	foreach($db->children() as $child)
	{
		$z=0;
		$y=0;
		$child_id++;			
		
		if ($child->trajet['depart'] != '') { $y++; if ($child->trajet['depart'] == $offreDepart) { $z++; } }
		if ($child->trajet['arrive'] != '') { $y++; if ($child->trajet['arrive'] == $offreArrive) { $z++; } }

		if ($child->date0['year'] != '')
		{
			if ($child->date0['day'] != '') { $y++; if ($child->date0['day'] == $offreDay) { $z++; } }
			if ($child->date0['month'] != '') { $y++; if ($child->date0['month'] == $offreMonth) { $z++; } }
			if ($child->date0['year'] != '') { $y++; if ($child->date0['year'] == $offreYear) { $z++; } }
		}

		else if ($child->date1['year'] != '')
		{
			$dateChild = new DateTime($offreYear . '-' . $offreMonth . '-' . $offreDay,new DateTimeZone('America/Montreal'));
		
			$dayStart = $child->date1['day'] ? $child->date1['day'] : '01';
			$dayEnd = $child->date2['day'] ? $child->date2['day'] : '31';
			$dateStart = new DateTime($child->date1['year'] . '-' . $child->date1['month'] . '-' . $dayStart,new DateTimeZone('America/Montreal'));
			$dateEnd = new DateTime($child->date2['year'] . '-' . $child->date2['month'] . '-' . $dayEnd,new DateTimeZone('America/Montreal'));
			$y += 2;

			if ($dateChild >= $dateStart) { $z++; }
			if ($dateChild <= $dateEnd) { $z++; }
		}

		if ($z == $y) //La requete correspond à  l'entrée
		{
			$db_offre = simplexml_load_file(dirname(__FILE__) . "/db/offre.xml") or die("Error: Cannot create object");
			
			$month_txt = array('Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre');
			$txt = '<html><body style="font-family: "Times New Roman", Times, serif; font-size:15px;">
			Le ' . $db_offre->offre[$liftId]->date['day'] . ' ' . $month_txt[($db_offre->offre[$liftId]->date['month']-1)] . ' ' . $db_offre->offre[$liftId]->date['year'] . ', départ de ' . $db_offre->offre[$liftId]->trajet['depart'] . ' vers ' . $db_offre->offre[$liftId]->trajet['arrive']
			. ' à ' . $db_offre->offre[$liftId]->date['heure'] . 'h' . $db_offre->offre[$liftId]->date['minute']
			. ' avec ' . $db_offre->offre[$liftId]->nom;
			if ($db_offre->offre[$liftId]->telephone[0]) { $txt .= '<br>Téléphone #1: ' . substr($db_offre->offre[$liftId]->telephone[0], 0, 3) . '-' . substr($db_offre->offre[$liftId]->telephone[0], 3, 3) . '-' . substr($db_offre->offre[$liftId]->telephone[0], 6, 4); }
			if ($db_offre->offre[$liftId]->telephone[1]) { $txt .= '<br>Téléphone #2: ' . substr($db_offre->offre[$liftId]->telephone[1], 0, 3) . '-' . substr($db_offre->offre[$liftId]->telephone[1], 3, 3) . '-' . substr($db_offre->offre[$liftId]->telephone[1], 6, 4); }
			if ($db_offre->offre[$liftId]->prix) { $txt .= '<br>Prix demandé: ' . $db_offre->offre[$liftId]->prix . ' $'; }
			if ($db_offre->offre[$liftId]->msg) { $txt .= '<br>Commentaire: ' . $db_offre->offre[$liftId]->msg; }
			
			$txt .= '<br><br>Visitez le site de <a href="http://caleche.bazaroccidental.org">Calèche Express</a> pour plus de détails concernant cette annonce';
			$txt .= '<br><br><hr><span style="font-size: small !important;">Vous recevez ce courriel en lien avec votre recherche de covoiturage pour: ';

			$txt .= '<br>Départs de ' . $child->trajet['depart'] . ' vers ' . $child->trajet['arrive'];

			if ($child->date1['year'] != '')
			{
				$txt .= ' entre le ' . $child->date1['day'] . ' ' . $month_txt[($child->date1['month']-1)] . ' ' . $child->date1['year'];
				$txt .= ' et le ' . $child->date2['day'] . ' ' . $month_txt[($child->date2['month']-1)] . ' ' . $child->date2['year'];
			}
			else if ($child->date0['year'] != '')
			{
				$year = ($child->date0['year'] != '') ? $child->date0['year'] : false;
				$month = ($child->date0['month'] != '') ? $child->date0['month'] : false;
				$day = ($child->date0['day'] != '') ? $child->date0['day'] : false;

				//Departs de X vers X
				if ($day) { $txt .= ' le '; }
				else if ($month) { $txt .= ' en '; }

				if ($day) { $txt .= $d_day; }
				if ($month) { $txt .= ' ' . strtolower($month_txt[($d_month-1)]); }
				if ($year && !$d_month && !$day) { $txt .= ' en ' . $year; }
				else if ($year) { $txt .= ' ' . $year; }
			}

			$txt .= 
			('
				<br><br><a href="http://caleche.bazaroccidental.org/?cancel=' . $child->token . ';' . $child->email . '">Suivez ce lien pour ne plus recevoir d\'alertes concernant ces critères</a>
				<br><a href="http://caleche.bazaroccidental.org/?cancel=' . $child->token . ';' . $child->email . ';all' . '">Suivez ce lien pour désactiver toutes les alertes</a></span>
			');
			$txt .= '</body></html>';
			$subject = "Un nouveau départ correspond à votre recherche";
			$headers = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			$headers .= 'From: Calèche Express <caleche@bazaroccidental.org>' . "\r\n";
			mail($child->email,$subject,$txt,$headers);
			//echo $txt;
		}
	}
}

echo 
	('
	<script type="text/javascript" src="index.js"></script>
	<script type="text/javascript" src="events.js"></script>
	</div></body>
	');
	
// Email obfuscator script 2.1 by Tim Williams, University of Arizona\n".
// Random encryption key feature by Andrew Moulden, Site Engineering Ltd\n".
// PHP version coded by Ross Killen, Celtic Productions Ltd\n".
function munge($address)
{
	$address = strtolower($address);
	$coded = "";
	$unmixedkey = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-";
	$inprogresskey = $unmixedkey;
	$mixedkey="";
	$unshuffled = strlen($unmixedkey);
	for ($i = 0; $i <= (strlen($unmixedkey)-1); $i++)
	{
		$ranpos = rand(0,$unshuffled-1);
		$mixedkey .= $inprogresskey{$ranpos};
		$before = substr($inprogresskey,0,$ranpos);
		$after = substr($inprogresskey,$ranpos+1,$unshuffled-($ranpos+1));
		$inprogresskey = $before.''.$after;
		$unshuffled -= 1;
	}
	$cipher = $mixedkey;
	$shift = strlen($address);

	for ($j=0; $j<strlen($address); $j++)
	{
		if (strpos($cipher,$address{$j}) == -1 )
		{
			$chr = $address{$j};
			$coded .= $address{$j};
		}
		else
		{
			$chr = (strpos($cipher,$address{$j}) + $shift) % strlen($cipher);
			$coded .= $cipher{$chr};
		}
	}

	return
	('
	<script type="text/javascript">
	coded = "' . $coded . '"
	key = "'.$cipher.'"
	shift=coded.length
	link=""
	for (i=0; i<coded.length; i++)
	{
		if (key.indexOf(coded.charAt(i))==-1)
		{
			ltr = coded.charAt(i)
			link += (ltr)
		}
		else
		{
			ltr = (key.indexOf(coded.charAt(i))-shift+key.length) % key.length
			link += (key.charAt(ltr))
		}
	}
	document.write(""+link+"")
	</script>
	');
}

function getBrowser()
{
    $ua = $_SERVER['HTTP_USER_AGENT'];
    if (strpos(strtolower($ua), 'safari/') && strpos(strtolower($ua), 'opr/')) { $res = 'Opera'; }
	else if (strpos(strtolower($ua), 'safari/') && strpos(strtolower($ua), 'chrome/')) { $res = 'Chrome'; }
	else if ( strpos(strtolower($ua), 'msie') || strpos(strtolower($ua), 'trident/')) { $res = 'Internet Explorer'; }
    elseif (strpos(strtolower($ua), 'firefox/')) { $res = 'Firefox'; }
    elseif (strpos(strtolower($ua), 'safari/') && (strpos(strtolower($ua), 'opr/') === false) && (strpos(strtolower($ua), 'chrome/') === false)) {  $res = 'Safari'; }
    else { $res = 'Unknown'; }
    return $res;
}
?>