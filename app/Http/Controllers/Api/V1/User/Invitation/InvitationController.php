<?php

namespace App\Http\Controllers\Api\V1\User\Invitation;

use App\Enums\HttpEnum;
use App\Enums\Invitation\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Invitation\SendInvitationRequest;
use App\Jobs\SendInvitationsJob;
use App\Mail\Invitation;
use App\Repositories\Interfaces\DesignRepositoryInterface;
use App\Repositories\Interfaces\InvitationRepositoryInterface;
use App\Repositories\Interfaces\TeamRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Traits\HandlesTryCatch;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;


class InvitationController extends Controller
{
    use HandlesTryCatch;
    public function __construct(public InvitationRepositoryInterface $invitationRepository,
                                public DesignRepositoryInterface     $designRepository,
                                public TeamRepositoryInterface       $teamRepository,
                                public UserRepositoryInterface       $userRepository,
    )
    {
    }

    public function send(SendInvitationRequest $request)
    {
        $design = $request->design_id ? $this->designRepository->find($request->design_id) : null;
        $team = $request->team_id ? $this->teamRepository->find($request->team_id) : null;
        SendInvitationsJob::dispatch($team, $design, $request->emails);


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
        $invitationUser = $this->userRepository->query()->whereEmail($email)->first();
        if ($invitation->design_id) {
            $invitationUser?->designs()->syncWithoutDetaching($invitation->design_id);
        }

        if ($invitation->team_id) {
            $teamDesigns = $invitation->team->designs;
            if ($teamDesigns->isNotEmpty()) {
                $invitationUser->designs()->syncWithoutDetaching($teamDesigns);
                $invitationUser->teams()->syncWithoutDetaching($invitation->team->id);
            }
        }

        $invitation->status = StatusEnum::ACCEPTED;
        $invitation->save();
        return redirect()->away(config('services.site_url') . 'Home')->withCookie(cookie(
            name: 'token',
            value: auth('sanctum')->user()?->currentAccessToken(),
            path: '/',
            domain: '.dorraprint.com',
            secure: false,
            httpOnly: false,
            sameSite: 'Lax'
        ));
    }
}
