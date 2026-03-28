# Feature: Automatic Proposal Drafting from Email

## Overview
Gmail forwards job posting emails to the server, a PHP script parses them, sends the posting + resume to Claude API, and emails back a draft proposal ready to review on the phone.

## Pipeline
```
Upwork sends job alert to Gmail
  → Gmail filter forwards to jobs@visionquest2020.net (or similar)
    → Server pipes email to process_email.php
      → PHP parses job posting (title, description, skills, budget)
        → Sends to Claude API with resume + rules
          → Emails proposal draft via claude_messenger
            → Review on phone, edit, submit on platform
```

## Components Needed

### 1. Email Address on Server
- Create `jobs@visionquest2020.net` (or dedicated address)
- Gmail filter: match Upwork job alerts, forward to this address

### 2. Mail Handling (two options)
**Option A — Pipe to script (instant):**
```
# /etc/aliases or .forward:
jobs: "|/usr/bin/php /var/www/html/claude_freelance/process_email.php"
```

**Option B — IMAP poll (simpler, on schedule):**
```php
$inbox = imap_open('{mail.yourdomain.com:993/imap/ssl}INBOX', $user, $pass);
$emails = imap_search($inbox, 'UNSEEN');
```

### 3. PHP Email Parser
- Strip HTML from email body
- Extract: job title, description, skills, budget, client info
- Handle Upwork's email format (may need regex per email template)

### 4. Claude API Integration
- Send parsed job posting + resume + rules to Claude API
- Rules include:
  - Be honest about skills (MySQL primary, not Postgres)
  - 26 years experience
  - Availability (currently April 1, 2026)
  - Rate suggestion based on their budget range
  - Flag weak fits — don't draft proposals for bad matches
  - Match tone to client (corporate vs casual)

### 5. Resume + Rules Files
- `data/resume.txt` — current resume
- `data/proposal_rules.txt` — do's and don'ts for proposals
- Both read by the script at generation time

### 6. Output via claude_messenger
```php
exec('php /path/to/claude_messenger/notify.php -s "Proposal Draft: $jobTitle" -b "$htmlProposal" -p claude_freelance');
```

## Dependencies
- Claude API key (Anthropic API, not CLI)
- Mail server with pipe or IMAP access
- claude_messenger for notifications
- Resume and rules maintained in project

## Notes
- This is an extension of the core claude_freelance project (resume in DB, paste job, get proposal)
- The email pipeline is the automated version of the manual paste workflow
- Idea originated 2026-03-28
