# GitHub Actions Deployment Setup

This repository includes a GitHub Actions workflow that automatically builds and deploys your Next.js application to a production server via FTP.

## Required GitHub Secrets

To use this deployment workflow, you need to set up the following secrets in your GitHub repository:

### Go to: Repository Settings → Secrets and variables → Actions → New repository secret

1. **FTP_SERVER**: Your FTP server hostname or IP address
   - Example: `ftp.yourserver.com` or `192.168.1.100`

2. **FTP_USERNAME**: Your FTP username
   - Example: `your-ftp-username`

3. **FTP_PASSWORD**: Your FTP password
   - Example: `your-secure-password`

### Optional Environment Variables (if your app needs them):

4. **DATABASE_URL**: Your production database connection string
5. **NEXTAUTH_SECRET**: Your NextAuth secret key
6. **NEXTAUTH_URL**: Your production URL
7. **AWS_ACCESS_KEY_ID**: If using AWS S3
8. **AWS_SECRET_ACCESS_KEY**: If using AWS S3
9. Any other environment variables your app requires

## Workflow Configuration

### Trigger Branch
The workflow is currently configured to trigger on pushes to the `main` branch. To change this:

```yaml
on:
  push:
    branches:
      - main  # Change this to your production branch (e.g., production, master)
```

### Server Directory
Update the `server-dir` in the FTP deployment step to match your server's web directory:

```yaml
server-dir: /public_html/  # Common paths: /, /public_html/, /var/www/html/, /htdocs/
```

### Files Included in Deployment

The workflow deploys:
- ✅ Built Next.js application (`.next/` folder)
- ✅ Production `node_modules` 
- ✅ Configuration files (`package.json`, `next.config.ts`, etc.)
- ✅ Public assets (`public/` folder)
- ✅ Source code (optional, for dynamic imports or server-side needs)

### Files Excluded from Deployment

- Development dependencies
- Git files
- Environment files (`.env*`)
- Documentation
- GitHub workflow files
- Cache files

## Manual Deployment

You can also trigger the deployment manually:
1. Go to the "Actions" tab in your GitHub repository
2. Select "Build and Deploy to Production"
3. Click "Run workflow"

## Production Server Requirements

Your production server should have:
- Node.js 20+ installed
- PM2 or similar process manager (recommended)
- Proper permissions for the FTP user

### Starting the Application on Server

After deployment, SSH into your server and run:

```bash
cd /path/to/your/deployed/app
npm start  # or use PM2: pm2 start "npm start" --name "your-app"
```

## Troubleshooting

### Common Issues:

1. **FTP Connection Failed**: Verify FTP credentials and server settings
2. **Build Failed**: Check if all environment variables are set correctly
3. **Missing Files**: Review the file copying steps in the workflow
4. **Permission Denied**: Ensure FTP user has write permissions to the target directory

### Debugging:

- Check the Actions tab for detailed logs
- Verify secrets are properly set
- Test FTP connection manually using an FTP client

## Security Notes

- Never commit credentials to your repository
- Use strong, unique passwords for FTP accounts
- Consider using SFTP instead of FTP for better security
- Regularly rotate FTP passwords
- Limit FTP user permissions to only necessary directories
