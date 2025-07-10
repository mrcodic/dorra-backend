<h1>Invitation</h1>
<p>You have been invited to join {{ $invitedResource instanceof \App\Models\Team ? "team" : "design" }} called
    {{ $invitedResource->name }} by {{ auth('sanctum')->user()->email }}
</p>
<p><a href="{{ $url }}">Accept Invitation</a></p>
