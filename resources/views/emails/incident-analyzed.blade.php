<h2>New Incident: {{ $incident->title }}</h2>

<table>
    <tr><td><strong>Severity</strong></td><td>{{ $incident->severity }}</td></tr>
    <tr><td><strong>Host</strong></td><td>{{ $incident->host }}</td></tr>
    <tr><td><strong>Rule</strong></td><td>{{ $incident->rule }}</td></tr>
    <tr><td><strong>Status</strong></td><td>{{ $incident->status }}</td></tr>
    <tr><td><strong>Opened At</strong></td><td>{{ $incident->opened_at }}</td></tr>
</table>

<h3>AI Description</h3>
<p>{{ $incident->ai_description }}</p>

<a href="{{ url('/admin/incidents/' . $incident->id) }}">View Incident</a>