<?php

use App\Models\InstructorProfile;
use App\Models\User;
use App\Policies\InstructorProfilePolicy;

test('administrators can view every instructor while instructors are self scoped', function () {
    $owner = User::factory()->instructor()->create();
    $other = User::factory()->instructor()->create();
    $admin = User::factory()->admin()->create();
    $profile = InstructorProfile::factory()->for($owner, 'user')->create();
    $policy = new InstructorProfilePolicy;
    expect($policy->view($owner, $profile))->toBeTrue();
    expect($policy->view($other, $profile))->toBeFalse();
    expect($policy->view($admin, $profile))->toBeTrue();
    expect($policy->reconcile($admin))->toBeTrue();
    expect($policy->reconcile($owner))->toBeFalse();
});
