# Railway Setup Guide for Betogram

## Current Status
The Betogram application has been updated to support Railway deployment with the following improvements:

- **HTTPS Enabled**: App automatically forces HTTPS on production, fixing mixed-content issues
- **Database Config**: Updated to support both MySQL and SQLite with Railway's auto-generated environment variables
- **Docker Setup**: Added proper Docker configuration with automatic database migrations on startup
- **SQLite Default**: App defaults to SQLite if no other database is configured

## Required Setup on Railway

### Option 1: Use SQLite (Simplest - No Additional Setup)
If you just deployed the latest version, the app will automatically use SQLite database. This requires:
- Railway to build and deploy the Docker image (currently in progress)
- Migrations run automatically on startup

**No additional configuration needed** - just wait for the deployment to complete.

### Option 2: Use MySQL/PostgreSQL on Railway (Recommended for Production)

If you prefer a traditional database setup:

1. **Go to your Railway Dashboard**
   - https://railway.app/dashboard

2. **Add a Database Plugin**
   - Click on your Betogram project
   - Click "Add Service"
   - Choose "MySQL" or "PostgreSQL"
   - Connect it to your Betogram service

3. **Railway Automatically Sets Environment Variables**
   - For MySQL: `MYSQLHOST`, `MYSQLPORT`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLDATABASE`
   - For PostgreSQL: `PGHOST`, `PGPORT`, `PGUSER`, `PGPASSWORD`, `PGDATABASE`
   - For unified hosting: `DATABASE_URL`

4. **Your app will automatically use these variables**
   - `config/database.php` now parses `DATABASE_URL` when present
   - if `MYSQLHOST` is set and no `DB_CONNECTION` exists, the app will default to `mysql`
   - if `PGHOST` is set and no `DB_CONNECTION` exists, the app will default to `pgsql`

## Testing Registration Flow

Once deployed, you can test:

1. **Registration** (Create new user account)
   - Go to https://betogram-production.up.railway.app/register
   - Fill in the form with test data
   - Click "Create Betogram Account"
   - Should redirect to home page on success

2. **Logout**
   - Click logout from the home page
   - Should redirect to login page

3. **Login with New Credentials**
   - Go to https://betogram-production.up.railway.app/login
   - Enter the email and password from registration
   - Should log in successfully and show home page

## Troubleshooting

### Still Getting "Server error" 500?
1. Docker image is still building - wait 2-3 minutes
2. Check Railway deployment status in dashboard
3. Once deployed, refresh the page

### Database Issues?
- If using SQLite: Check that `/app/database/database.sqlite` has permissions
- If using MySQL/PostgreSQL: Verify credentials are correctly set in Railway variables
- Railway auto-generates these when you add a database plugin

## Files Modified
- `Dockerfile`: Added migration runner and proper Apache configuration
- `config/database.php`: Added support for Railway environment variables
- `app/Providers/AppServiceProvider.php`: Forces HTTPS on production

## Environment Variables Reference
The app reads these environment variables (in order of preference):

**Database Configuration:**
- `DB_CONNECTION`: sqlite, mysql, or pgsql (defaults to 'sqlite')
- `DB_HOST` or `MYSQLHOST` or `PGHOST`
- `DB_PORT` or `MYSQLPORT` or `PGPORT`
- `DB_DATABASE` or `MYSQLDATABASE` or `PGDATABASE`
- `DB_USERNAME` or `MYSQLUSER` or `PGUSER`
- `DB_PASSWORD` or `MYSQLPASSWORD` or `PGPASSWORD`

**App Configuration:**
- `APP_ENV`: local, production (should be 'production' on Railway)
- `APP_DEBUG`: true/false (should be 'false' on production)
- `APP_URL`: https://betogram-production.up.railway.app (auto-forced to HTTPS)
- `FLUTTERWAVE_PUBLIC_KEY`: Your Flutterwave public API key
- `FLUTTERWAVE_SECRET_KEY`: Your Flutterwave secret API key
- `FLUTTERWAVE_ENVIRONMENT`: `staging` or `production`

## Next Steps
1. Wait for Docker build to complete (2-5 minutes)
2. Test registration flow on the live site
3. If successful, test logout and login
4. If issues persist, add MySQL/PostgreSQL database from Railway dashboard
