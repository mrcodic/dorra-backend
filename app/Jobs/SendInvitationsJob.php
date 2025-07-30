<?php

namespace App\Jobs;

use App\Enums\Invitation\StatusEnum;
use App\Mail\Invitation;
use App\Models\Team;
use App\Repositories\Interfaces\InvitationRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SendInvitationsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */

    protected $emails;
    public function __construct(public Team $team, array $emails)
    {

        $this->emails = $emails;
    }

    /**
     * Execute the job.
     */
    public function handle(InvitationRepositoryInterface $repository): void
    {
        $team = $this->team;

        foreach ($this->emails as $email) {
            $repository->query()
                ->where('email', $email)
                ->where('team_id', $team->id)
                ->where('status', StatusEnum::PENDING)
                ->delete();


            $invitation = $repository->create([
                'email' => $email,
                'team_id' => $team->id,
                'status' => StatusEnum::PENDING,
            ]);


            $url = URL::temporarySignedRoute('invitation.accept', now()->addDays(2), [
                'invitation' => $invitation->id,
                'email' => $email,
            ]);


            Mail::to($email)->send(new Invitation($url, $team));
    }
}

}
