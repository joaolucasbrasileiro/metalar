<?php

namespace App\Enums;

enum UserRole: string 
{
    case COMMON = 'common';
    case MODERATOR = 'moderator';
    case ADMIN = 'admin';
}