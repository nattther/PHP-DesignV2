<?php

declare(strict_types=1);

namespace Design\Auth;

enum AuthMode: string
{
    case Sso = 'sso';
    case Local = 'local';
    case Public = 'public';
}
