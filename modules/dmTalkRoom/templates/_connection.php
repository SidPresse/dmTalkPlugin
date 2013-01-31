<?php

// echo _tag('div.dm_talk', _tag('div.dm_talk_connection_form',
//   _tag('p.dm_talk_connection_message', __('Please enter your nickname')).
//   $form->open().
//   $form['name']->error()->field().
//   $form->submit(__('Enter')).
//   $form->renderHiddenFields().
//   $form->close()
// ));



// new version

if (sfContext::getInstance()->getUser()->can('talk'))
{
	// all the rooms
	$roomsList = '';
	$roomsListNotIn ='';
	$roomsListIn ='';	
    $rooms = dmDb::table('DmTalkRoom')->getRoomsSince24H();
	foreach ($rooms as $room) {
		$humans = $room->getHumanSpeakers();
		$humansArray = explode(',', implode(',', $humans));

		if (count($humans)){
			if (!in_array(sfContext::getInstance()->getUser(), $humansArray)){
				$roomsListNotIn .= _tag('dd', _link(sfContext::getInstance()->getHelper()->link(sfContext::getInstance()->getPage())->param('r', $room->code)->getAbsoluteHref())->text(__('Speakers').": ".implode(', ', $humans)));
			} else {
				$roomsListIn    .= _tag('dd', _link(sfContext::getInstance()->getHelper()->link(sfContext::getInstance()->getPage())->param('r', $room->code)->getAbsoluteHref())->text(__('Speakers').": ".implode(', ', $humans)));
			}
		}
		
	}
	if ($roomsListNotIn != ''){
		$roomsList .= _tag('dlt',_tag('dt',__('Rooms without you since 24h').$roomsListNotIn));
	}
	if ($roomsListIn != ''){
		$roomsList .= _tag('dlt',_tag('dt',__('Rooms with you since 24h').$roomsListIn));
	}

	// La room a-t-elle des humains?
	$currentRoom = dmDb::table('DmTalkRoom')->findOneByCode($_GET['r']);
	if (!$currentRoom->getHumanSpeakers()){ // on crée la room
		echo _tag('div.dm_talk', _tag('div.dm_talk_connection_form',
		  _tag('p.dm_talk_connection_message', __('Welcome in private chat room')).
		  $form->open().
		  $form['name']->error()->field().
		  $form->submit(__('Create a new chat room')).
		  $form->renderHiddenFields().
		  $form->close().
		  $roomsList      // on affiche la liste des rooms disponibles
		));
	} else { // on rejoint la room
		$formName = 'talk'.md5(date('Ymdhhmmss'));
		$autoSubmitForm = '<script type="text/javascript">
			//<![CDATA[
			    document.'.$formName.'.submit();
			//]]>
			</script>';
		echo _tag('div.dm_talk', _tag('div.dm_talk_connection_form',
		  _tag('p.dm_talk_connection_message', __('Welcome in private chat room')).
		  $form->open(array('name' => $formName)).
		  $form['name']->error()->field().
		  $form->submit(__('Join a chat room')).
		  $form->renderHiddenFields().
		  $autoSubmitForm.
		  $form->close()
		));
	}

	// echo _tag('div.dm_talk', _tag('div.dm_talk_connection_form',
	//   _tag('p.dm_talk_connection_message', __('Welcome in private chat room')." ".sfContext::getInstance()->getUser()).
	//   $form->open().
	//   $form['name']->error()->field().
	//   $form->submit(__('Create a new chat room')).
	//   $form->renderHiddenFields().
	//   $form->close().
	//   $roomsList
	// ));

}





