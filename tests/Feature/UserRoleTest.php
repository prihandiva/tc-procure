<?php

use App\Enums\UserRole;
use App\Models\User;

test('users can have different roles and helpers work as expected', function () {
    $superadmin = User::factory()->superadmin()->create();
    $spv = User::factory()->spv()->create();
    $vendor = User::factory()->vendor()->create();

    expect($superadmin->isSuperAdmin())->toBeTrue()
        ->and($superadmin->isSpv())->toBeFalse()
        ->and($superadmin->isVendor())->toBeFalse()
        ->and($superadmin->role)->toBe(UserRole::SUPERADMIN);

    expect($spv->isSpv())->toBeTrue()
        ->and($spv->isSuperAdmin())->toBeFalse()
        ->and($spv->isVendor())->toBeFalse()
        ->and($spv->role)->toBe(UserRole::SPV);

    expect($vendor->isVendor())->toBeTrue()
        ->and($vendor->isSuperAdmin())->toBeFalse()
        ->and($vendor->isSpv())->toBeFalse()
        ->and($vendor->role)->toBe(UserRole::VENDOR);
});

test('active scope returns only active users', function () {
    User::factory()->create(['is_active' => true]);
    User::factory()->create(['is_active' => false]);

    $activeUsers = User::active()->get();
    expect($activeUsers->every(fn (User $user) => $user->is_active))->toBeTrue();
});

