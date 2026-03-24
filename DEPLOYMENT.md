# EarnDesk Deployment Guide

This guide covers deploying EarnDesk to Render.com.

## Prerequisites

1. A [Render](https://render.com) account
2. A GitHub repository with your EarnDesk code
3. API keys for:
    - Paystack (payments)
    - Flutterwave (payments)
    - Google OAuth (social login)
    - Mail server (SMTP)

## Deployment Options

### Option 1: Using render.yaml (Blueprint)

1. Push your code to GitHub
2. In Render dashboard, go to **Blueprints**
3. Click **New Blueprint Instance**
4. Connect your GitHub repository
5. Render will automatically detect the `render.yaml` file
6. Set your environment variables in the Render dashboard
7. Deploy!

### Option 2: Manual Setup

#### Step 1: Create PostgreSQL Database

1. In Render dashboard, click **New** → **PostgreSQL**
2. Choose the **Free** plan
3. Name it `earndesk-db`
4. Note the connection details

#### Step 2: Create Web Service

1. In Render dashboard, click **New** → **Web Service**
2. Connect your GitHub repository
3. Configure:
    - **Name**: earndesk
    - **Runtime**: Docker (or PHP if available)
    - **Region**: Choose closest to your users
    - **Branch**: main (or your production branch)
    - **Instance Type**: Free (or paid for production)

4. Set environment variables:

    ```
    APP_ENV=production
    APP_DEBUG=false
    APP_KEY=base64:your-generated-key
    APP_URL=https://your-app.onrender.com

    DB_CONNECTION=pgsql
    DB_HOST=<from-database>
    DB_PORT=5432
    DB_DATABASE=<from-database>
    DB_USERNAME=<from-database>
    DB_PASSWORD=<from-database>

    CACHE_DRIVER=file
    SESSION_DRIVER=file
    QUEUE_CONNECTION=database
    ```

5. Click **Create Web Service**

## Environment Variables

### Required Variables

| Variable  | Description                                                        |
| --------- | ------------------------------------------------------------------ |
| `APP_KEY` | Laravel application key (generate with `php artisan key:generate`) |
| `APP_URL` | Your application URL                                               |
| `DB_*`    | Database connection details                                        |
| `MAIL_*`  | Email configuration                                                |

### Payment Gateways

| Variable                 | Description            |
| ------------------------ | ---------------------- |
| `PAYSTACK_PUBLIC_KEY`    | Paystack public key    |
| `PAYSTACK_SECRET_KEY`    | Paystack secret key    |
| `FLUTTERWAVE_PUBLIC_KEY` | Flutterwave public key |
| `FLUTTERWAVE_SECRET_KEY` | Flutterwave secret key |

### OAuth (Optional)

| Variable               | Description            |
| ---------------------- | ---------------------- |
| `GOOGLE_CLIENT_ID`     | Google OAuth client ID |
| `GOOGLE_CLIENT_SECRET` | Google OAuth secret    |

## Post-Deployment

### Generate Application Key

If `APP_KEY` is not set, run:

```bash
php artisan key:generate
```

### Run Migrations

Migrations run automatically on deploy. To run manually:

```bash
php artisan migrate --force
```

### Create Admin User

Connect to your database and run:

```sql
INSERT INTO users (name, email, password, role, email_verified_at, created_at, updated_at)
VALUES (
    'Admin',
    'admin@example.com',
    '$2y$10$...', -- Use bcrypt to hash your password
    'admin',
    NOW(),
    NOW(),
    NOW()
);
```

Or use Tinker:

```bash
php artisan tinker
>>> User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('password'), 'role' => 'admin']);
```

## Troubleshooting

### 500 Error on Deploy

1. Check logs in Render dashboard
2. Verify all required environment variables are set
3. Ensure database migrations completed

### Database Connection Issues

1. Verify database credentials
2. Check if database is in the same region as web service
3. Ensure SSL is configured properly

### Asset Loading Issues

1. Run `npm run build` locally and commit assets
2. Or ensure build step runs during deployment

## Monitoring

- View logs in Render dashboard under **Logs** tab
- Set up health checks at `/health` endpoint
- Configure alerts for downtime

## Scaling

For production workloads:

1. Upgrade to paid instance type
2. Add Redis for caching/sessions
3. Use external file storage (AWS S3)
4. Set up queue workers

## Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] `APP_KEY` is set and secure
- [ ] Database credentials are secure
- [ ] SSL is enabled (automatic on Render)
- [ ] Payment gateway keys are production keys
- [ ] Email credentials are secure
