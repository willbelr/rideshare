window.onload = function()
{
	set_fields();
	if (activePage == 'main')
	{
		//Recherche Events
		var askDateTarget = ['askDay0','askDay1','askDay2','askMonth0','askMonth1','askMonth2','askYear0','askYear1','askYear2'];
		for (var i = 0; i < askDateTarget.length; i++)
		{
			document.getElementById(askDateTarget[i]).addEventListener('change', function()
			{
				var errors = fvalid_askDate();
				if (document.forms['askForm'].elements['askDateMode0'].checked == true) { fColor('askDate0',errors); }
				else if (document.forms['askForm'].elements['askDateMode1'].checked == true) { fColor('askDate1',errors); }
				if (errors == '') { document.forms['askForm'].elements['askConfirm'].disabled = false; }
			});
		}

		document.getElementById('askMail').addEventListener('keydown', function()
		{
			fieldColor('white','askMail');
			document.forms['askForm'].elements['askConfirm'].disabled = false;
		});

		document.getElementById('askAlert').onchange = function()
		{
			document.forms['askForm'].elements['askConfirm'].disabled = false;
			fieldColor('white','askMail');
			fieldColor('white','askDepart');
			fieldColor('white','askArrive');
			switch_alert_state();
		}

		document.getElementById('askDateMode0').onchange = function() { askDateModeSwitch('specific'); }
		document.getElementById('askDateMode1').onchange = function() { askDateModeSwitch('domain'); }
		document.getElementById('askDateMode0').onmouseover = function() { askDateModeSwitch('specific'); }
		document.getElementById('askDateMode1').onmouseover = function() { askDateModeSwitch('domain'); }
		
		//Offre Events	
		function offreEvents($a)
		{
			fieldColor('white',$a);
			document.forms['offreForm'].elements['offreConfirm'].disabled = false;
		}

		function offreDateEvents($a)
		{
			var errors = fvalid_offreDate();
			fColor('offreDate',errors);
			if (errors != '')
			{
				//# document.forms['offreForm'].elements['offreConfirm'].disabled = true;
				return false;
			}
		}

		var offreEvents_list = ['offreDay','offreMonth','offreYear','offreHr','offreMin','offreNom','offreMail','offreTel1','offreTel2','offrePrix','offreMsg'];
		for (var i = 0; i < offreEvents_list.length; i++)
		{
			if (offreEvents_list[i] == 'offreDay' || offreEvents_list[i] == 'offreMonth' || offreEvents_list[i] == 'offreYear' || offreEvents_list[i] == 'offreHr' || offreEvents_list[i] == 'offreMin')
			{
				document.getElementById(offreEvents_list[i]).addEventListener('change', function() { offreDateEvents(this.id); });
			}
			else
			{
				document.getElementById(offreEvents_list[i]).addEventListener('keydown', function() { offreEvents(this.id); });
				document.getElementById(offreEvents_list[i]).addEventListener('change', function() { offreEvents(this.id); });
			}
		}

		//Cookies Events
		function clear_field($a)
		{
			if (getCookie('_'+$a) == '0' )
			{
				setCookie('_'+$a, '1', '1');
				setCookie($a, '', '');
				document.getElementById($a).value = '';
			}
		}

		//Clear cookie value from field
		var clearCookieValue = ['offreNom','offreMail','offreTel1','offreTel2','replyNom','replyMail','askMail'];
		for (var i = 0; i < clearCookieValue.length; i++)
		{	
			if (document.getElementById(clearCookieValue[i]))
			{
				document.getElementById(clearCookieValue[i]).addEventListener('focus', function() { clear_field(this.id); });
			}
		}

		//Auto-complete Events
		function enableAndWhite($a)
		{
			if ($a == 'askDepart' || $a == 'askArrive')
			{
				document.forms['askForm'].elements['askConfirm'].disabled = false;
			}

			else if ($a == 'offreDepart' || $a == 'offreArrive')
			{
				document.forms['offreForm'].elements['offreConfirm'].disabled = false;
			}

			fieldColor('white',$a);
		}

		document.getElementById('offreDepart').addEventListener('awesomplete-selectcomplete', function() { enableAndWhite(this.id); });
		document.getElementById('offreArrive').addEventListener('awesomplete-selectcomplete', function() { enableAndWhite(this.id); });
		document.getElementById('askDepart').addEventListener('awesomplete-selectcomplete', function() { enableAndWhite(this.id); });
		document.getElementById('askArrive').addEventListener('awesomplete-selectcomplete', function() { enableAndWhite(this.id); });

		function formatStEvent($a)
		{
			formatSt($a);
			enableAndWhite($a);
		}
		document.getElementById('offreDepart').addEventListener('keydown', function() { formatStEvent(this.id); });
		document.getElementById('offreArrive').addEventListener('keydown', function() { formatStEvent(this.id); });
		document.getElementById('askDepart').addEventListener('keydown', function() { formatStEvent(this.id); });
		document.getElementById('askArrive').addEventListener('keydown', function() { formatStEvent(this.id); });
	}

	//MSG events
	else if (activePage == 'msg') 
	{
		function msgEvents($a)
		{
			fieldColor('white',$a);
			if (!botTimer) { document.forms['contactForm'].elements['contactConfirm'].disabled = false; }
		}
		
		var msgEvents_list = ['contactNom','contactMail','contactMsg'];
		for (var i = 0; i < msgEvents_list.length; i++)
		{
			document.getElementById(msgEvents_list[i]).addEventListener('keydown', function() { msgEvents(this.id); });
			document.getElementById(msgEvents_list[i]).addEventListener('change', function() { msgEvents(this.id); });
		}
	}

	//Reply events
	if (document.getElementById('replyMsg'))
	{
		function replyEvents($a)
		{
			fieldColor('white',$a);
			document.forms['replyForm'].elements['replyConfirm'].disabled = false;
		}

		if (REPLY_MODE == 'public')
		{
			document.getElementById('replyNom').addEventListener('keydown', function() { replyEvents(this.id); });
			document.getElementById('replyNom').addEventListener('change', function() { replyEvents(this.id); });
		}
		else if (REPLY_MODE == 'private')
		{
			document.getElementById('replyPass').addEventListener('keydown', function() { replyEvents(this.id); });
			document.getElementById('replyPass').addEventListener('change', function() { replyEvents(this.id); });
		}

		document.getElementById('replyMsg').addEventListener('keydown', function() { replyEvents(this.id); });
		document.getElementById('replyMsg').addEventListener('change', function() { replyEvents(this.id); });
	}
}

function set_fields() 
{
	if (activePage == 'main')
	{
		document.forms['askForm'].elements['askMail'].disabled = true;
		var today = new Date();
		var year = today.getFullYear();
		var day = today.getDate();
		var month = today.getMonth(); month++;
		document.getElementById('offreDay').value = day;
		document.getElementById('offreMonth').value = ('0' + month).slice(-2);
		document.getElementById('offreYear').value = year;

		document.getElementById('askMonth0').value = ('0' + month).slice(-2);
		document.getElementById('askMonth1').value = ('0' + month).slice(-2);
		document.getElementById('askMonth2').value = ('0' + (month+1)).slice(-2);
		document.getElementById('askYear0').value = year;
		document.getElementById('askYear1').value = year;
		document.getElementById('askYear2').value = year;

		document.getElementsByName('alertAsterisk')[0].innerHTML = '&nbsp;&nbsp;&nbsp;';
		document.getElementsByName('alertAsterisk')[1].innerHTML = '&nbsp;&nbsp;&nbsp;';
		document.getElementsByName('alertAsterisk')[2].innerHTML = '&nbsp;&nbsp;&nbsp;';
		document.getElementsByName('alertAsterisk')[3].innerHTML = '&nbsp;&nbsp;&nbsp;';
		document.getElementsByName('alertAsterisk')[4].innerHTML = '&nbsp;&nbsp;&nbsp;';

		document.forms['askForm'].elements['askDay1'].disabled = true;
		document.forms['askForm'].elements['askMonth1'].disabled = true;
		document.forms['askForm'].elements['askYear1'].disabled = true;
		document.forms['askForm'].elements['askDay2'].disabled = true;
		document.forms['askForm'].elements['askMonth2'].disabled = true;
		document.forms['askForm'].elements['askYear2'].disabled = true;
	}
	
	//Set fields from cookie
	var fieldFromCookie = ['offreNom','offreMail','offreTel1','offreTel2','replyNom','replyMail','askMail'];
	for (var i = 0; i < fieldFromCookie.length; i++)
	{
		if (document.getElementById(fieldFromCookie[i]))
		{
			document.getElementById(fieldFromCookie[i]).value = getCookie(fieldFromCookie[i]);	
			setCookie(('_'+fieldFromCookie[i]), '0', '1');
		}
	}
}

function switch_alert_state()
{
	if (document.getElementById('askAlert').checked == true)
	{
		document.forms['askForm'].elements['askMail'].disabled = false;
		document.getElementsByName('alertAsterisk')[0].innerHTML = ' *';
		document.getElementsByName('alertAsterisk')[1].innerHTML = ' *';
		document.getElementsByName('alertAsterisk')[4].innerHTML = ' *';
	}
	else
	{
		document.forms['askForm'].elements['askMail'].disabled = true;
		document.getElementsByName('alertAsterisk')[0].innerHTML = '&nbsp;&nbsp;&nbsp;';
		document.getElementsByName('alertAsterisk')[1].innerHTML = '&nbsp;&nbsp;&nbsp;';
		document.getElementsByName('alertAsterisk')[4].innerHTML = '&nbsp;&nbsp;&nbsp;';
	}
}

function askDateModeSwitch($mode)
{
	if ($mode == 'domain')
	{
		document.getElementsByName('alertAsterisk')[2].innerHTML = ' *';
		document.getElementsByName('alertAsterisk')[3].innerHTML = ' *';
		document.forms['askForm'].elements['askDateMode0'].checked = false;
		document.forms['askForm'].elements['askDateMode1'].checked = true;
		document.forms['askForm'].elements['askDay0'].disabled = true;
		document.forms['askForm'].elements['askMonth0'].disabled = true;
		document.forms['askForm'].elements['askYear0'].disabled = true;
		document.forms['askForm'].elements['askDay1'].disabled = false;
		document.forms['askForm'].elements['askMonth1'].disabled = false;
		document.forms['askForm'].elements['askYear1'].disabled = false;
		document.forms['askForm'].elements['askDay2'].disabled = false;
		document.forms['askForm'].elements['askMonth2'].disabled = false;
		document.forms['askForm'].elements['askYear2'].disabled = false;
		fieldColor('black','askDay0');
		fieldColor('black','askMonth0');
		fieldColor('black','askYear0');
		fieldColor('white','askDay1');
		fieldColor('white','askMonth1');
		fieldColor('white','askYear1');
		fieldColor('white','askDay2');
		fieldColor('white','askMonth2');
		fieldColor('white','askYear2');
	}
	else if ($mode == 'specific')
	{
		document.getElementsByName('alertAsterisk')[2].innerHTML = '&nbsp;&nbsp;&nbsp;';
		document.getElementsByName('alertAsterisk')[3].innerHTML = '&nbsp;&nbsp;&nbsp;';
		document.forms['askForm'].elements['askDateMode0'].checked = true;
		document.forms['askForm'].elements['askDateMode1'].checked = false;
		document.forms['askForm'].elements['askDay0'].disabled = false;
		document.forms['askForm'].elements['askMonth0'].disabled = false;
		document.forms['askForm'].elements['askYear0'].disabled = false;
		document.forms['askForm'].elements['askDay1'].disabled = true;
		document.forms['askForm'].elements['askMonth1'].disabled = true;
		document.forms['askForm'].elements['askYear1'].disabled = true;
		document.forms['askForm'].elements['askDay2'].disabled = true;
		document.forms['askForm'].elements['askMonth2'].disabled = true;
		document.forms['askForm'].elements['askYear2'].disabled = true;
		fieldColor('white','askDay0');
		fieldColor('white','askMonth0');
		fieldColor('white','askYear0');
		fieldColor('black','askDay1');
		fieldColor('black','askMonth1');
		fieldColor('black','askYear1');
		fieldColor('black','askDay2');
		fieldColor('black','askMonth2');
		fieldColor('black','askYear2');
	}
	var errors = fvalid_askDate();
	if (document.forms['askForm'].elements['askDateMode0'].checked == true) { fColor('askDate0',errors); }
	else if (document.forms['askForm'].elements['askDateMode1'].checked == true) { fColor('askDate1',errors); }
	if (errors == '') { document.forms['askForm'].elements['askConfirm'].disabled = false; }
}