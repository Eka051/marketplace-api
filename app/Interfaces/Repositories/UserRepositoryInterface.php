<?php

namespace App\Interfaces\Repositories;

interface UserRepositoryInterface{
   public function findByEmail(string $email);
   public function create(array $data);
   public function deleteTokens(object $user, string $deviceName);
}