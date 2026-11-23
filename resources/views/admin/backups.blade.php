@extends('layouts.app', ['title' => 'Database backups'])

@section('content')
    <div class="page-heading">
        <div>
            <h1>Database backups</h1>
            <p>Create and download a copy of the SQLite database. Backups are stored outside the public web directory.</p>
        </div>
        <form method="POST" action="{{ route('admin.backups.store') }}">
            @csrf
            <button class="button" type="submit">Create backup now</button>
        </form>
    </div>

    <section class="panel" style="margin-bottom: 18px;">
        <h2>Recovery</h2>
        <p class="rule-note">
            Restore is intentionally available only from the server console. Run
            <code>php artisan app:restore-laundry-database BACKUP_FILENAME</code>.
            The command requires an explicit confirmation, creates a safety copy of the current database, and then restores the selected backup.
            Database backups support SQLite only.
        </p>
    </section>

    <section class="panel">
        <h2>Saved backups</h2>
        @if ($backups->isEmpty())
            <div class="empty-state">No backups yet. Create one before performing maintenance.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Backup file</th><th>Created</th><th>Size</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach ($backups as $backup)
                            <tr>
                                <td><code>{{ $backup['file']->getFilename() }}</code></td>
                                <td>{{ \Illuminate\Support\Carbon::createFromTimestamp($backup['file']->getMTime())->format('M j, Y g:i A') }}</td>
                                <td>{{ $backup['size'] }}</td>
                                <td>
                                    <a class="button secondary small" href="{{ route('admin.backups.download', $backup['file']->getFilename()) }}">Download</a>
                                    <form method="POST" action="{{ route('admin.backups.destroy', $backup['file']->getFilename()) }}" style="display: inline;" onsubmit="return confirm('Permanently delete this backup? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button danger small" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
