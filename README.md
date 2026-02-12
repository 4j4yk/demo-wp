# Minimal WordPress Custom Code Repo Template

This repo template is intentionally small:

- WordPress core is **not** in git.
- Only `./plugins` and `./themes` are in git.
- GitHub Actions deploys those folders to the server.

## How It Works

1. EC2 runs WordPress + MariaDB with Docker Compose.
2. The server uses bind mounts:
   - `/opt/wp-demo/deploy/plugins` -> `wp-content/plugins`
   - `/opt/wp-demo/deploy/themes` -> `wp-content/themes`
3. On every push to `main`, GitHub Actions rsyncs your local `plugins/` and `themes/` to those server paths.

## Repo Setup

Copy these into your real GitHub repo root:

- `plugins/`
- `themes/`
- `.github/workflows/deploy.yml` (from `aws-wp-minimal/.github/workflows/deploy.yml`)

Then add secrets:

- `SSH_HOST`
- `SSH_USER` (usually `ubuntu`)
- `SSH_PRIVATE_KEY`

Push to `main` to trigger deployment.
