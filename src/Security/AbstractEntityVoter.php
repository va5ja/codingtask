<?php declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class AbstractEntityVoter extends Voter
{
    public const ATTRIBUTE_READ = 'read';
    public const ATTRIBUTE_WRITE = 'write';
}