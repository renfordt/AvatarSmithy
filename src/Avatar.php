<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy;

class Avatar
{
    public static function engine(string $engine): AvatarBuilder
    {
        return new AvatarBuilder($engine);
    }

    public static function for(mixed $user): AvatarBuilder
    {
        $builder = new AvatarBuilder();

        if (is_object($user)) {
            if (property_exists($user, 'email') && is_string($user->email)) {
                $builder->seed($user->email);
            }
            if (property_exists($user, 'name') && is_string($user->name)) {
                $builder->name($user->name);
            }
            if (method_exists($user, 'getEmail')) {
                $email = $user->getEmail();
                if (is_string($email)) {
                    $builder->seed($email);
                }
            }
            if (method_exists($user, 'getName')) {
                $name = $user->getName();
                if (is_string($name)) {
                    $builder->name($name);
                }
            }
        } elseif (is_array($user)) {
            if (isset($user['email']) && is_string($user['email'])) {
                $builder->seed($user['email']);
            }
            if (isset($user['name']) && is_string($user['name'])) {
                $builder->name($user['name']);
            }
        }

        return $builder;
    }
}
