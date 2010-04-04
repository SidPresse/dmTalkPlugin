<?php
/**
 * Talk room actions
 */
class dmTalkRoomActions extends myFrontModuleActions
{

  // entry point for the application
  public function executeApplicationWidget(sfWebRequest $request)
  {
    // we have a current speaker
    if($speakerCode = $request->getParameter('s'))
    {
      $this->forward404Unless($speaker = dmDb::table('DmTalkSpeaker')->findOneByCode($speakerCode));
      $request->setParameter('dm_talk_speaker', $speaker);
    }
    // we have a current room, but no speaker
    elseif($roomCode = $request->getParameter('r'))
    {
      $this->forward404Unless($room = dmDb::table('DmTalkRoom')->findOneByCode($roomCode));

      $connectionForm = new dmTalkConnectionForm($room);
      if($request->isMethod('post'))
      {
        if($connectionForm->bindAndValid($request))
        {
          $speaker = $room->createSpeaker($connectionForm->getValue('name'))->saveGet();
          return $this->redirect($this->getHelper()->link($this->getPage())->param('s', $speaker->code)->getHref());
        }
      }
      $this->forms['DmTalkConnectionForm'] = $connectionForm;
    }
    // we have no current room nor speaker
    else
    {
      $room = dmDb::table('DmTalkRoom')->create()->saveGet();
      return $this->redirect($this->getHelper()->link($this->getPage())->param('r', $room->code)->getHref());
    }
  }

  // send a message to the room
  public function executeSay(sfWebRequest $request)
  {
    $this->forward404Unless(
          $request->isMethod('post')
      &&  ($speaker = dmDb::table('DmTalkSpeaker')->findOneByCode($request->getParameter('s')))
      &&  ($text = $request->getParameter('text'))
    );

    $speaker->say($text);

    return $this->renderText($this->getPartial('dmTalkRoom/conversation', array(
      'speaker' => $speaker,
      'room'    => $speaker->Room
    )));
  }

  // change the user nickname
  public function executeChangeNickname(sfWebRequest $request)
  {
    $this->forward404Unless(
          $request->isMethod('post')
      &&  ($speaker = dmDb::table('DmTalkSpeaker')->findOneByCode($request->getParameter('s')))
      &&  ($nickname = $request->getParameter('nickname'))
    );

    $validator  = new dmTalkConnectionValidator($speaker->Room);
    
    try
    {
      $speaker->rename($validator->clean($nickname));
      $message = 'ok';
    }
    catch(Exception $e)
    {
      $message = $this->getI18n()->__($e->getMessage());
    }

    return $this->renderText($message);
  }

  // refresh conversation and speakers
  public function executeRefresh(sfWebRequest $request)
  {
    $this->forward404Unless(
      $speaker = dmDb::table('DmTalkSpeaker')->findOneByCode($request->getParameter('s'))
    );

    $speaker->ping();

    $room = $speaker->Room;

    $response = array();

    $hash = $request->getParameter('hash');
    $newHash = $room->hash;
    if($newHash != $hash)
    {
      $response['hash'] = $newHash;
      $response['conversation'] = $this->getPartial('dmTalkRoom/conversation', array(
        'speaker' => $speaker,
        'room'    => $room
      ));
    }

    $response['speakers'] = $this->getPartial('dmTalkRoom/speakers', array(
      'speaker' => $speaker,
      'room'    => $room
    ));

    return $this->renderJson($response);
  }

}
