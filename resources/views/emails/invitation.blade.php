<h1>Invitation</h1>
<p>You have been invited to join {{ $invitedResource instanceof \App\Models\Team ? "team" : "design" }} called
    {{ $invitedResource->name }} by {{$inviterEmail }}
</p>
<p><a href="{{ $url }}">Accept Invitation</a></p>
