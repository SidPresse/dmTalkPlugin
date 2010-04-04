<?php

/**
 * PluginDmTalkMessageTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class PluginDmTalkMessageTable extends myDoctrineTable
{
  public function createForSpeaker(DmTalkSpeaker $speaker, $message, DmTalkSpeaker $toSpeaker = null)
  {
    return $this->create(array(
      'room_id'       => $speaker->room_id,
      'speaker_name'  => $speaker->name,
      'text'          => $message,
      'to_speaker_id' => $toSpeaker ? $toSpeaker->get('id') : null
    ));
  }
}