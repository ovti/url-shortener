<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\User;

class UserVoter extends Voter
{

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public const EDIT_PASSWORD = 'EDIT_PASSWORD';
    public const VIEW = 'VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT_PASSWORD, self::VIEW])
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
            case self::EDIT_PASSWORD:
                return $this->canAccess($subject, $user);

        }

        return false;
    }

    private function canAccess(User $subject, UserInterface $user): bool
    {
        return $subject === $user || $this->security->isGranted('ROLE_ADMIN');
    }
}
