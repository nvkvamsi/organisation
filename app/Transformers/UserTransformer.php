<?php

namespace App\Transformers;

class UserTransformer
{
    public function transform($user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            
        ];
    }
}
