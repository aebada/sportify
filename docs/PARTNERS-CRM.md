# Potential Partners CRM (Sportify Plus)

Admin catalog of **media, clubs, leagues, associations, academies, agencies, brands, tech, facilities, and communities** for Sportify Plus partnership outreach.

## Admin URL

- List / filter / invite: `/admin/partners`
- Classic CRM contacts: `/admin/crm`
- Requires permission `crm.access` (admin / marketing roles)

## Migrate & seed

```bash
cd /path/to/sportify-web
cp -n .env.example .env   # if needed
php sportify migrate
php sportify seed-partners
```

Or from admin: **Import seeds** on `/admin/partners`.

### Seed files (merged automatically)

| File | Contents |
|------|----------|
| `database/seeds/partners-media.json` | Football / sports press, TV, digital |
| `database/seeds/partners-clubs.json` | Bundesliga, 2.BL, 3. Liga sample, top EU clubs |
| `database/seeds/partners-leagues.json` | DFL, DFB, UEFA/FIFA, regional associations |
| `database/seeds/partners-ecosystem.json` | Academies, agencies, brands, tech, facilities, communities |

Any extra `database/seeds/partners-*.json` from research agents is also imported.

## Invite all (official Sportify partners)

From address: `MAIL_FROM_ADDRESS` (default **partners@sportifyplus.de**).

```bash
# Count / dry-run (no DB status changes for dry-run candidates beyond report)
php sportify invite-partners --dry-run

# Queue verified emails (no SMTP blast if MAIL_* empty)
php sportify invite-partners --limit=100

# Send only when SMTP is configured AND you pass --send
php sportify invite-partners --send --limit=25
```

Admin buttons:

1. **Dry-run invite** — report only  
2. **Invite all (queue)** — mark verified leads queued + log (safe default)  
3. **Send verified (batch 25)** — only shown when `MAIL_HOST` + `MAIL_USERNAME` are set  

Invite status values: `pending` → `queued` → `sent` → `accepted` (or `failed` / `skipped`).

## Mail env

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=partners@sportifyplus.de
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=partners@sportifyplus.de
MAIL_FROM_NAME="Sportify Plus"
```

Never commit real passwords. Without mail config, invites stay **queued**.

## Ethics

- Public press / partnership / impressum emails only  
- Each row should include `source_url` when possible  
- `email_confidence`: `verified` | `needs_research` | `unverified`  
- Invite-all defaults to **verified only**

## Ported patterns

- MunichTech EXPO outreach CRM: category filters, invite batching, from-mailbox, send logs  
- AI Pass / MunichTech: seed JSON + admin invite-all with dry-run / rate-safe defaults  
