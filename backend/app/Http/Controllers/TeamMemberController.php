<?php

namespace App\Http\Controllers;

use App\Http\Resources\TeamMemberResource;
use App\Models\TeamMember;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TeamMemberController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TeamMemberResource::collection(
            TeamMember::query()->orderBy('id')->get()
        );
    }
}
