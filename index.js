function fColor()
{
	console.log('fColor: ' + arguments[0] + arguments[1]);
	var errors = arguments[1].split(',');

	if (arguments[0] == 'offre') { var target = ['offreDepart','offreArrive','offreDay','offreMonth','offreYear','offreHr','offreMin','offreNom','offreMail','offreMsg','offreTel1','offreTel2','offrePrix']; }
	else if (arguments[0] == 'offreDate') { var target = ['offreDay','offreMonth','offreYear','offreHr','offreMin']; }
	else if (arguments[0] == 'ask') { var target = ['askDepart','askArrive','askDay0','askMonth0','askYear0','askDay1','askMonth1','askYear1','askDay2','askMonth2','askYear2','askMail']; }
	else if (arguments[0] == 'askDate0') { var target = ['askDay0','askMonth0','askYear0']; }
	else if (arguments[0] == 'askDate1') { var target = ['askDay1','askMonth1','askYear1','askDay2','askMonth2','askYear2']; }
	else if (arguments[0] == 'reply') { var target = ['replyNom','replyMsg','replyMail']; }
	else if (arguments[0] == 'contact') { var target = ['contactNom','contactMsg','contactMail']; }

	for(x = 0; x < target.length; x++)
	{
		match = false;
		for(y = 1; y < errors.length; y++)
		{
			if (errors[y] == target[x]) { match = true; }
		}

		if (match) { fieldColor('red',target[x]); }
		else { fieldColor('white',target[x]); }
	}

	if (arguments[0] == 'offre' || arguments[0] == 'offreDate') { document.forms['offreForm'].elements['offreConfirm'].disabled = (errors == '') ? false : true; }
	else if (arguments[0] == 'ask' || arguments[0] == 'askDate') { document.forms['askForm'].elements['askConfirm'].disabled = (errors == '') ? false : true; }
	else if (arguments[0] == 'reply') { document.forms['replyForm'].elements['replyConfirm'].disabled =  (errors == '') ? false : true; }
	else if (arguments[0] == 'contact') { document.forms['contactForm'].elements['contactConfirm'].disabled = (errors == '') ? false : true; }
}

function fieldColor()
{
	if (document.getElementById(arguments[1]))
	{
		if (arguments[0] == 'white')
		{
			document.getElementById(arguments[1]).style.border = '';
			document.getElementById(arguments[1]).style.backgroundColor=''; 
		}
		else if (arguments[0] == 'red')
		{
			document.getElementById(arguments[1]).style.border = '1px solid red';
			document.getElementById(arguments[1]).style.backgroundColor='#ff4d4d'; 
		}
		else if (arguments[0] == 'black')
		{
			document.getElementById(arguments[1]).style.border = '1px solid #555';
			document.getElementById(arguments[1]).style.backgroundColor='#555'; 
			document.getElementById(arguments[1]).style.margin='0'; 
		}
	}
}

function fvalid_replyForm()
{
	if (REPLY_MODE == 'public')
	{
		var errors =
		(
			fvalid('replyMsg','required','msg') +
			fvalid('replyMail','email')
		);
	}
	else if (REPLY_MODE == 'private')
	{
		var errors =
		(
			fvalid('replyMsg','required','msg') +
			fvalid('replyPass','required')
		);	
	}

	if (errors != '')
	{
		fColor('reply',errors);
		//# document.forms['replyForm'].elements['replyConfirm'].disabled = true;
		return false;
	}
	else
	{ 
		if (document.getElementById('replyNom'))
		{
			setCookie('replyNom', document.getElementById('replyNom').value, '365');
			setCookie('offreNom', document.getElementById('replyNom').value, '365');
		}
		
		if (document.getElementById('replyMail'))
		{
			setCookie('askMail', document.getElementById('replyMail').value, '365');
			setCookie('offreMail', document.getElementById('replyMail').value, '365');
			setCookie('replyMail', document.getElementById('replyMail').value, '365');
		}
	}
}

function fvalid_contactForm()
{
	var errors =
	(
		fvalid('contactNom','required','nom') +
		fvalid('contactMail','required','email') +
		fvalid('contactMsg','required','msg')
	);

	if (errors != '')
	{
		fColor('contact',errors);
		//# document.forms['contactForm'].elements['contactConfirm'].disabled = true;
		return false;
	}
}

function fvalid_offreForm()
{
	var errors =
	(
		fvalid('offreArrive','required','ville') +
		fvalid('offreDepart','required','ville') +
		fvalid('offreHr','required') +
		fvalid('offreMin','required') +
		fvalid('offreNom','required','nom') +
		fvalid('offreTel1','telephone','minlength_10') +
		fvalid('offreTel2','telephone','minlength_10') +
		fvalid('offreMail','email','required') +
		fvalid('offrePrix','numeric') +
		fvalid('offreMsg','msg') +
		valid_location('offreDepart','offreArrive') +
		fvalid_offreDate()
	);

	if (errors != '')
	{
		fColor('offre',errors);
		//# document.forms['offreForm'].elements['offreConfirm'].disabled = true;
		return false;
	}
	else
	{ 
		setCookie('offreNom', document.getElementById('offreNom').value, '365');
		setCookie('replyNom', document.getElementById('offreNom').value, '365');
		setCookie('askMail', document.getElementById('offreMail').value, '365');
		setCookie('offreMail', document.getElementById('offreMail').value, '365');
		setCookie('replyMail', document.getElementById('offreMail').value, '365');
		setCookie('offreTel1', document.getElementById('offreTel1').value, '365');
		setCookie('offreTel2', document.getElementById('offreTel2').value, '365');
	}
}

function fvalid_offreDate()
{
	errors = '';
	var today = new Date();
	var hr = (document.getElementById('offreHr').value != '') ? 'offreHr' : 23;
	var min = (document.getElementById('offreMin').value != '') ? 'offreMin' : 59;
	var dateCompare = getDateObject('offreYear','offreMonth','offreDay',hr,min);
	
	if (today > dateCompare)
	{
		console.log(today + ' > ' + dateCompare);
		errors = (',' + 'offreDay');
		errors += (',' + 'offreMonth');
		if (today >= getDateObject('offreYear','offreMonth','offreDay'))
		{
			errors += (',' + 'offreHr');
			errors += (',' + 'offreMin');
		}
	}
	return errors;
}

function fvalid_askForm()
{
	var errors = fvalid_askDate();

	//Notifications
	if (document.getElementById('askAlert').checked == true)
	{
		errors +=
		(
			fvalid('askDepart','required','ville') +
			fvalid('askArrive','required','ville') +
			fvalid('askMail','required','email') +
			valid_location('askDepart','askArrive')
		);
	}
	else
	{
		errors +=
		(
			fvalid('askDepart','ville') +
			fvalid('askArrive','ville') +
			is_location('askDepart') +
			is_location('askArrive') +
			valid_location('askDepart','askArrive')
		);
	}

	if (errors != '')
	{
		fColor('ask',errors);
		//# document.forms['askForm'].elements['askConfirm'].disabled = true;
		return false;
	}
	else if (document.getElementById('askForm'))
	{
		document.forms['askForm'].elements['askConfirm'].disabled = false;
		setCookie('askMail', document.getElementById('askMail').value, '365');
		setCookie('offreMail', document.getElementById('askMail').value, '365');
		setCookie('replyMail', document.getElementById('askMail').value, '365');
	}
}

function fvalid_askDate()
{
	errors = '';

	//Entre le DATE1 et le DATE2
	if (document.forms['askForm'].elements['askDateMode'].value == 'domain')
	{
		var errors =
		(
			fvalid('askMonth1','required') +
			fvalid('askYear1','required') +
			fvalid('askMonth2','required') +
			fvalid('askYear2','required')
		);

		var day1 = (document.getElementById('askDay1').value != '') ? document.getElementById('askDay1').value : 31;
		var day2 = (document.getElementById('askDay2').value != '') ? document.getElementById('askDay2').value : 31;

		var dateStartLow = getDateObject('askYear1','askMonth1',day1);
		var dateEndLow = getDateObject('askYear2','askMonth2',day2);
		var dateEndHigh = getDateObject('askYear2','askMonth2',day2,'23','59');
		var dateStartHigh = getDateObject('askYear1','askMonth1',day1,'23','59');
		var today = new Date();

		if (today >= dateStartHigh)
		{
			errors += (',' + 'askDay1');
			errors += (',' + 'askMonth1');
		}
		else if (dateStartLow >= dateEndHigh || dateStartLow >= dateEndLow || today >= dateEndHigh)
		{
			errors += (',' + 'askDay2');
			errors += (',' + 'askMonth2');
		}
		console.log('startLOW: '+dateStartLow);
		console.log('endLOW: '+dateEndLow);
	}

	//Le DATE0
	else if (document.forms['askForm'].elements['askDateMode'].value == 'specific')
	{
		if (document.getElementById('askDay0').value != '' || document.getElementById('askMonth0').value != '')
		{
			var today = new Date();
			if (document.getElementById('askDay0').value != '') { day = document.getElementById('askDay0').value; } else { day = '31'; }

			dateCompare = getDateObject('askYear0','askMonth0',day,'23','59');

			if (today > dateCompare)
			{
				console.log(today + ' > ' + dateCompare);
				errors = (',' + 'askDay0');
				errors += (',' + 'askMonth0');
			}
		}
	}
	
	return errors;
}

function fvalid()
{
	var err = '';
	var field = document.getElementById(arguments[0]);
	for(var i = 1; i < arguments.length; i++)
	{
		var nom_regex = /[^A-z\\\s-.'éâêîôûäëïöüÿàèìòù]/;
		var msg_regex = /[^A-z0-9\\\s-.,';$@%!?+=)(éâêîôûäëïöüÿàèìòù]/;
		var ville_regex = /[^A-ÿ\\\s-.')(]/;
		var email_regex = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
		var telephone_regex = /^\(?(\d{3})\)?[- ]?(\d{3})[- ]?(\d{4})$/;

		if 
		(
			((arguments[i] == 'required') && (field.value == ''))
			|| ((arguments[i] == 'numeric') && (isNaN(field.value)))
			|| ((arguments[i] == 'nom') && (field.value.match(nom_regex)))
			|| ((arguments[i] == 'msg') && ((field.value != '') && (field.value.match(msg_regex))))
			|| ((arguments[i] == 'ville') && ((field.value != '') && (field.value.match(ville_regex))))
			|| ((arguments[i] == 'email') && ((field.value != '') && (!field.value.match(email_regex))))
			|| ((arguments[i] == 'telephone') && ((field.value != '') && (!field.value.match(telephone_regex))))
			|| ((arguments[i].substr(0,10) == 'minlength_') && ((field.value != '') && (field.value.length < arguments[i].substr(10))))
		)
		{
			err += ',' + arguments[0];
			break;
		}
	}

	return err;
}

function getDateObject()
{
	var min = 59; var hr = 0; x = 0; 
	while(arguments[x])
	{
		if (isNaN(arguments[x])) { var xVal = document.getElementById(arguments[x]).value; }
		else if (!isNaN(arguments[x])) { var xVal = arguments[x]; }
		else { xVal = 0; }

		switch (x)
		{
			case 0: var year = xVal; break;
			case 1: var month = xVal; break;
			case 2: var day = xVal; break;
			case 3: hr = xVal; break;
			case 4: min = xVal; break;
		}
		x++;
	}

	return new Date(year+','+month+','+day+' '+hr+':'+min);
}

function valid_location(dep, arr)
{
	var depart = document.getElementById(dep);
	var arrive = document.getElementById(arr);
	var errors = '';
	
	if (depart.value || arrive.value)
	{
		if ((depart.value && is_location(dep) != '') || (depart.value == arrive.value)) { errors += (',' + dep); }
		if ((depart.value && is_location(arr) != '') || (depart.value == arrive.value)) { errors += (',' + arr); }
	}
	return errors;
}

function is_location(field)
{
	if (document.getElementById(field).value != '')
	{
		var evaluate = homostr(document.getElementById(field).value);
		console.log('eval: '+evaluate);
		var ul = document.getElementById('villes');
		var li_arr = ul.getElementsByTagName('li');
		var match = false;
		for (var i=0; i<li_arr.length; i++)
		{
			var compare = ul.getElementsByTagName('li')[i].innerHTML;
			compare = homostr(compare);
			if (compare == evaluate)
			{
				console.log('match: '+ul.getElementsByTagName('li')[i].innerHTML)
				document.getElementById(field).value = ul.getElementsByTagName('li')[i].innerHTML;
				document.getElementById(field).style.border = '';
				document.getElementById(field).style.backgroundColor = ''; 
				match = true;
				break;
			}
		}
		if (match == false)
		{
			return (',' + field);
		}
	}
	return '';
}

function homostr(ville)
{
	ville = ville.toLowerCase();
	ville = ville.replace(/\(.*?\)/, '');
	ville = ville.replace(/[éèêë]/g,'e');
	ville = ville.replace(/[ôòö]/g,'o');
	ville = ville.replace(/[îïì]/g,'i');
	ville = ville.replace(/[ûüù]/g,'u');
	ville = ville.replace(/[âäà]/g,'a');
	ville = ville.replace(/[ÿ]/g,'y');
	ville = ville.replace('st-','st!');
	ville = ville.replace('st ','st!');
	ville = ville.replace('ste-','st!');
	ville = ville.replace('ste ','st!');
	ville = ville.replace('saint-','st!');
	ville = ville.replace('saint ','st!');
	ville = ville.replace('sainte-','st!');
	ville = ville.replace('sainte ','st!');
	ville = ville.replace(/[-'\s]/g,'');
	return(ville);
}

function formatSt(field)
{
	var field = document.getElementById(field);
	if (field.value.length < 5)
	{
		var replace = false;
		fvalue = field.value.toLowerCase();
		if (fvalue == 'st-') { field.value = 'Saint-'; replace = true; }
		if (fvalue == 'ste-') { field.value = 'Sainte-'; replace = true; }
		if (replace == true)
		{
			var awesome = new Awesomplete(field);
			awesome.evaluate();
			field.focus();
		}
	}
}

function setCookie(cname, cvalue, exdays)
{
	var d = new Date();
	d.setTime(d.getTime() + (exdays*24*60*60*1000));
	var expires = 'expires='+d.toUTCString();
	document.cookie = cname + '=' + cvalue + '; ' + expires;
}

function getCookie(cname)
{
	var name = cname + '=';
	var ca = document.cookie.split(';');
	for(var i=0; i<ca.length; i++)
	{
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1);
		if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
	}
	return '';
}