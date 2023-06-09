<?php
/**
 * User voter.
 */

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\User;

/**
 * Class UserVoter.
 */
class UserVoter extends Voter
{
    /**
     * Security.
     */
    private Security $security;

    /**
     * UserVoter constructor.
     *
     * @param Security $security Security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public const EDIT_USER_DATA = 'EDIT_USER_DATA';
    public const VIEW = 'VIEW';

    /**
     * Supports.
     *
     * @param string $attribute Attribute
     * @param mixed  $subject   Subject
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT_USER_DATA, self::VIEW])
            && $subject instanceof User;
    }

    /**
     * Vote on attribute.
     *
     * @param string         $attribute Attribute
     * @param mixed          $subject   Subject
     * @param TokenInterface $token     Token
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
            case self::EDIT_USER_DATA:
                return $this->canAccess($subject, $user);
        }

        return false;
    }

    /**
     * Can access.
     *
     * @param User          $subject User
     * @param UserInterface $user    User
     */
    private function canAccess(User $subject, UserInterface $user): bool
    {
        return $subject === $user || $this->security->isGranted('ROLE_ADMIN');
    }
}
