Production deployment checklist for classnde

This document lists the high-priority steps to prepare and deploy the application to production.

1) Immediate (rotate secrets)
- Do not reuse any credentials that were stored in the local `.env` file in the repository workspace.
- Revoke and re-create the following credentials right away (example list found in `.env`):
  - MIDTRANS_SERVER_KEY, MIDTRANS_CLIENT_KEY
  - TWILIO_AUTH_TOKEN, TWILIO_API_KEY_SECRET, TWILIO_API_KEY_SID
  - BUNNY_API_KEY, BUNNY_STORAGE_KEY
  - Any payment gateway, CDN, SMTP, or cloud provider keys found in your `.env`
- Store new secrets in a secret manager (recommended): AWS Secrets Manager, Azure Key Vault, Google Secret Manager, or your CI/CD provider's secrets.

2) Environment variables (production)
Set the following in production environment (do not commit to repo):
- APP_ENV=production
- APP_DEBUG=false
- APP_URL=https://YOUR_DOMAIN
- LOG_CHANNEL=stack
- LOG_LEVEL=error
- DB_CONNECTION=mysql (or cloud-provided DB)
- CACHE_STORE=redis
- SESSION_DRIVER=redis
- QUEUE_CONNECTION=redis
- FILESYSTEM_DISK=s3
- MAIL_MAILER=smtp (and configure host/port/username/password via secret manager)
- Replace third-party keys with the rotated values and load via secret manager.

3) Infrastructure & services
- Database: use a managed MySQL / RDS instance. Configure read replicas/backups as needed.
- Redis: use managed Redis for cache, sessions and queues.
- Object storage: use S3-compatible storage (AWS S3, Wasabi, DigitalOcean Spaces) and set `FILESYSTEM_DISK=s3`.
- Queue workers: run `php artisan queue:work --daemon` (or use Horizon) and supervise with systemd / supervisor.
- Background jobs: ensure long-running processes have restart policy and logging.
- HTTPS: provision TLS certs (Let's Encrypt or managed certificate) and redirect HTTP->HTTPS.

4) Webhooks & security
- Whitelist production webhook endpoints in Midtrans and Twilio dashboard.
- Ensure `MIDTRANS_SERVER_KEY` is set in production so Midtrans signature verification works.
- Ensure `TWILIO_AUTH_TOKEN` is set and Twilio signature verification is enabled on `app/Http/Controllers/TwilioWebhookController.php`.
- Use secure, unique webhook endpoints and verify signatures at server-side (already implemented for Midtrans and Twilio).

Midtrans production notes
- Ensure the following environment variables are set in production (do not commit to repo):
  - MIDTRANS_SERVER_KEY=<your_production_server_key>
  - MIDTRANS_CLIENT_KEY=<your_production_client_key>
  - MIDTRANS_IS_PRODUCTION=true
  - MIDTRANS_MERCHANT_ID=<optional_merchant_id>
- In the Midtrans dashboard set the server-to-server notification URL to:
  - https://YOUR_DOMAIN/payments/midtrans-notify
  and enable notifications for transaction events. Use a secret endpoint or firewall rules if possible.
- Verify webhook signature verification in `app/Http/Controllers/PaymentController.php` — it expects Midtrans signature_key (sha512) and validates it against the server key configured above.

5) Deployment recipe (example)
- Build frontend assets (on CI or build server):
  - npm ci
  - npm run build
- PHP dependencies (on build or deploy host):
  - composer install --no-dev --optimize-autoloader
- Prepare environment on host using secrets from secret manager (do not store .env in repo)
- Migrate & seed DB (careful on live):
  - php artisan migrate --force
- Cache & optimize:
  - php artisan config:cache
  - php artisan route:cache
  - php artisan view:cache
- Queue & scheduler:
  - Ensure `php artisan schedule:run` is run every minute by cron
  - Start queue workers and supervisor configs

6) CI / CD
- Add pipeline that:
  - Runs `composer install` and `npm ci` on build stage
  - Runs tests (unit + selected feature tests) in an isolated environment
  - Builds frontend assets and packages the release
  - Injects production secrets at deploy time (never from repo)

7) Observability & Alerts
- Configure centralized logging (Sentry, Papertrail, Datadog).
- Setup alerts for 5xx rates, failed queue jobs, and slow DB queries.
- Setup daily/weekly backups for DB and object storage.

8) Smoke tests & staging
- Create a staging environment that mirrors production.
- Run end-to-end smoke tests for payment flows (Midtrans) and Twilio webhooks using test credentials.

9) Post-deploy checklist
- Verify logs for unexpected errors
- Verify webhook deliveries and Midtrans/Twilio signature verification
- Verify that session/caching are working (no file session creation)
- Run a small end-to-end purchase in sandbox mode and confirm transaction grant

Testing webhooks locally using ngrok
-----------------------------------
1) Install and run ngrok (or an equivalent tunnel) to expose your local app over HTTPS:

  ngrok http 8000

  Note the https forwarding URL, e.g. https://abcd1234.ngrok.io

2) Point Midtrans sandbox notifications to your public webhook URL:

  https://abcd1234.ngrok.io/payments/midtrans-notify

  In the Midtrans dashboard (sandbox), set the Notification URL to the above and enable notifications.

3) Use the included artisan helper to simulate a signed Midtrans webhook payload and send it to your app:

  php artisan midtrans:test-webhook --url="https://abcd1234.ngrok.io/payments/midtrans-notify" --order="test-order-123" --status=settlement --amount=1000

  The command will compute the expected `signature_key` (sha512) using `MIDTRANS_SERVER_KEY` from your configuration and POST a payload to the target URL.

4) Alternatively, trigger a sandbox payment via the UI (or create a snap token) and wait for Midtrans to send the notification to your ngrok URL. Monitor your application logs to verify handling.

Notes & resources
- Secrets are the highest priority. Rotate keys immediately if they were present in the local `.env` file.
- If you'd like, I can help create Cloud-specific deployment manifests (systemd, Docker, Kubernetes, or GitHub Actions)."
