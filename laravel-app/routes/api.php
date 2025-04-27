<?php

use App\Http\Controllers\AudioController;
use Illuminate\Support\Facades\Route;

// routes/api.php
Route::post('/upload-audio', [AudioController::class, 'transcribe']);
Route::post('/summarize', [AudioController::class, 'summarize']);
