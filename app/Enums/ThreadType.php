<?php

namespace App\Enums;

enum ThreadType: string
{
    case CHAT = 'chat';
    case IMAGE_GENERATION = 'image_generation';
}
