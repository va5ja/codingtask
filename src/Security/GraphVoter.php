<?php declare(strict_types=1);

namespace App\Security;

use App\Entity\Graph;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class GraphVoter extends AbstractEntityVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        if (
            $subject instanceof Graph &&
            in_array($attribute, [AbstractEntityVoter::ATTRIBUTE_READ, AbstractEntityVoter::ATTRIBUTE_WRITE])
        ) {
            return true;
        }

        return false;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        return $subject->getUser() === $user;
    }
}
