Hello {{ $recipient->name ?: 'there' }},

{{ $subjectLine }}

{{ $messageText }}

{{ $actionLabel }}: {{ $actionUrl }}

This is a transactional notification about activity on your SwiftKudi account.
Manage non-critical notification preferences in your account settings.

© {{ date('Y') }} {{ config('app.name', 'SwiftKudi') }}
