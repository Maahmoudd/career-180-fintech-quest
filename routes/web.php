<?php

use App\Livewire\InstructorBalanceDashboard;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::middleware('auth')->get('/balances', InstructorBalanceDashboard::class)->name('balances');
