<?php

use Illuminate\Support\Facades\Route;
use Flux\Flux;
use App\Livewire\Contracts\ViewContract;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('/users', 'users.index')->name('users.index');
    Route::livewire('/users/{user}', 'users.view')->name('users.show');
    Route::livewire('/contract-types', 'contract-types.index')->name('contractTypes.index');

    Route::livewire('/contracts', 'contracts.index')->name('contracts.index');
    Route::livewire('/contracts/{contract}','contracts.view')->name('contracts.view');

});

require __DIR__.'/settings.php';
