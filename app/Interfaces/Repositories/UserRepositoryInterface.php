<?php

namespace App\Interfaces\Repositories;

interface UserRepositoryInterface{
   public function findByEmail(string $email);
   public function findById(string $userId);
   public function create(array $data);
   public function delete(string $userId);
   public function deleteTokens(object $user, string $deviceName);
}