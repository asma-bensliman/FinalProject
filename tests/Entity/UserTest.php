<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserCreation(): void
    {
        $user = new User();
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setUsername('johndoe');
        $user->setEmailAddress('john@test.com');
        $user->setPassword('hashedpassword');
        $user->setStatus('actif');
        $user->setRoles(['ROLE_USER']);

        $this->assertEquals('John', $user->getFirstName());
        $this->assertEquals('Doe', $user->getLastName());
        $this->assertEquals('johndoe', $user->getUsername());
        $this->assertEquals('john@test.com', $user->getEmailAddress());
        $this->assertEquals('actif', $user->getStatus());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    public function testUserIdentifier(): void
    {
        $user = new User();
        $user->setEmailAddress('john@test.com');

        $this->assertEquals('john@test.com', $user->getUserIdentifier());
    }

    public function testEraseCredentials(): void
    {
        $user = new User();
        $user->setPassword('secret');
        $user->eraseCredentials();

        $this->assertEquals('secret', $user->getPassword());
    }

    public function testDefaultRoles(): void
    {
        $user = new User();
        $user->setRoles([]);

        $this->assertEquals([], $user->getRoles());
    }
}