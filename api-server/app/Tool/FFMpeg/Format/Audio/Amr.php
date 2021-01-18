<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Tool\FFMpeg\Format\Audio;

use FFMpeg\Format\Audio\DefaultAudio;

/**
 * The Amr audio format.
 */
class Amr extends DefaultAudio
{
    public function __construct()
    {
        $this->audioCodec = 'libamr_nb';
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableAudioCodecs()
    {
        return ['libamr_nb'];
    }
}
