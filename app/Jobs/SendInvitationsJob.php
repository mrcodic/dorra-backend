<?php

namespace App\Jobs;

use App\Enums\Invitation\StatusEnum;
use App\Mail\Invitation;
use App\Models\Design;
use App\Models\Team;
use App\Repositories\Interfaces\InvitationRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Traits\HandlesTryCatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SendInvitationsJob implements ShouldQueue
{
    use Queueable, HandlesTryCatch;

    /**
     * Create a new job instance.
     */

    protected $emails;

    public function __construct(public ?Team $team = null, public ?Design $design = null, array $emails)
    {

        $this->emails = $emails;
    }

    /**
     * Execute the job.
     */
    public function handle(InvitationRepositoryInterface $repository): void
    {
        $team = $this->team;
        $design = $this->design;

        foreach ($this->emails as $email) {

            $invitation = $this->handleTransaction(function () use ($team, $design, $email, $repository) {
                $repository->query()
                    ->where('email', $email)
                    ->where('team_id', $team?->id)
                    ->where('design_id', $design?->id)
                    ->where('status', StatusEnum::PENDING)
                    ->delete();


                return $repository->create([
                    'email' => $email,
                    'team_id' => $team?->id,
                    'design_id' => $design?->id,
                    'status' => StatusEnum::PENDING,
                ]);

            });

            $user = app(UserRepositoryInterface::class)->findByEmail($email);
            if ($user->is_email_notifications_enabled
                && $user->notificationTypes()
                    ->where('name', 'Added to a new team')
                    ->exists())
            {
                $url = URL::temporarySignedRoute('invitation.accept', now()->addDays(2), [
                    'invitation' => $invitation->id,
                    'email' => $email,
                ]);

                Mail::to($email)->send(new Invitation($url, $team));
            }
        }
    }

}
