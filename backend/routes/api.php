<?php

use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\OnCallController;
use App\Http\Controllers\TeamMemberController;
use Illuminate\Support\Facades\Route;

Route::get('/team-members', [TeamMemberController::class, 'index'])->name('team-members.index');
Route::get('/leave-requests', [LeaveRequestController::class, 'index'])->name('leave-requests.index');
Route::post('/leave-requests', [LeaveRequestController::class, 'store'])->name('leave-requests.store');
Route::patch('/leave-requests/{leaveRequest}', [LeaveRequestController::class, 'update'])->name('leave-requests.update');
Route::delete('/leave-requests/{leaveRequest}', [LeaveRequestController::class, 'destroy'])->name('leave-requests.destroy');
Route::get('/on-call', [OnCallController::class, 'index'])->name('on-call.index');
