<?php

namespace App\Http\Controllers\Api\V1\User\Invitation;

use App\Enums\HttpEnum;
use App\Enums\Invitation\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Invitation\SendInvitationRequest;
use App\Mail\Invitation;
use App\Repositories\Interfaces\DesignRepositoryInterface;
use App\Repositories\Interfaces\InvitationRepositoryInterface;
use App\Repositories\Interfaces\TeamRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;


class InvitationController extends Controller
{
    public function __construct(public InvitationRepositoryInterface $invitationRepository,
                                public DesignRepositoryInterface     $designRepository,
                                public TeamRepositoryInterface       $teamRepository,)
    {}

    public function send(SendInvitationRequest $request)
    {
        $design = $request->design_id ? $this->designRepository->find($request->design_id) : null;
        $team = $request->team_id ? $this->teamRepository->find($request->team_id) : null;

        $invitation = $this->invitationRepository->create($request->validated());
        $url = URL::temporarySignedRoute('invitation.accept', now()->addDays(2), [
            'invitation' => $invitation->id,
            'email' => $request->email,
        ]);
        $invitedResource = $design ?? $team;
        Mail::to($request->email)->send(new Invitation($url, $invitedResource));
        return Response::api();

    }

    public function accept(Request $request)
    {
        $invitationId = $request->query('invitation');
        $email = $request->query('email');
        $invitation = $this->invitationRepository->query()->where('id', $invitationId)
            ->where('email', $email)
            ->where('status', StatusEnum::PENDING)
            ->firstOrFail();
        if ($invitation->expires_at && Carbon::now()->gt($invitation->expires_at)) {
            return Response::api(
                HttpEnum::FORBIDDEN,
                message: "The invitation you attempted to use has expired.",
                errors: [
                    'invitation' => ['This invitation is no longer valid.']
                ]
            );
        }
        if ($invitation->design_id)
        {
            auth('sanctum')->user()->userDesigns()->attach($invitation->design_id);
        }
        $invitation->status = StatusEnum::ACCEPTED;
        $invitation->save();
        return redirect()->away('https://www.google.com/');
    }
}
