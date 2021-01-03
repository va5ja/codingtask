<?php declare(strict_types=1);

namespace App\Security;

use App\Entity\Graph;
use App\Entity\ShortestPath;
use App\Entity\User;
use App\EntityManager\EntityManagerProvider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ShortestPathVoter extends AbstractEntityVoter
{
    /** @var EntityManagerProvider */
    private $entityManagerProvider;

    public function __construct(EntityManagerProvider $entityManagerProvider)
    {
        $this->entityManagerProvider = $entityManagerProvider;
    }

    protected function supports(string $attribute, $subject): bool
    {
        if (
            $subject instanceof ShortestPath &&
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

        $graph = $this->entityManagerProvider
            ->getManagerForClass(Graph::class)
            ->find(Graph::class, (string)$subject->getGraph()->getId());

        return $graph->getUser() === $user;
    }
}
