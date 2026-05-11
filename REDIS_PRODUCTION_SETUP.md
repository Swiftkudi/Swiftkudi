# Redis Setup Guide for Production Deployment

## Overview
This guide covers setting up Redis for high-performance queuing and caching in your SwiftKudi Laravel application, specifically optimized for the scalable notification system.

## Prerequisites
- Ubuntu/Debian/CentOS server
- Root or sudo access
- Laravel application deployed

## 1. Install Redis Server

### Ubuntu/Debian:
```bash
sudo apt update
sudo apt install redis-server
```

### CentOS/RHEL:
```bash
sudo yum install redis
# or for newer versions:
sudo dnf install redis
```

### Verify Installation:
```bash
redis-server --version
redis-cli ping  # Should return "PONG"
```

## 2. Configure Redis

### Edit Redis Configuration:
```bash
sudo nano /etc/redis/redis.conf
```

### Key Settings to Update:

```conf
# Network
bind 127.0.0.1 ::1  # Allow local connections only
port 6379
timeout 0
tcp-keepalive 300

# Security
# requirepass your_secure_password_here

# Memory Management
maxmemory 256mb
maxmemory-policy allkeys-lru

# Persistence
save 900 1
save 300 10
save 60 10000

# Logging
loglevel notice
logfile /var/log/redis/redis.log

# Disable dangerous commands in production
rename-command FLUSHDB ""
rename-command FLUSHALL ""
rename-command SHUTDOWN SHUTDOWN_REDIS
```

### Start and Enable Redis:
```bash
sudo systemctl start redis
sudo systemctl enable redis
sudo systemctl status redis
```

## 3. Laravel Configuration

### Update .env file:
```env
# Queue Configuration
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0

# Cache Configuration (Optional - Redis for sessions/cache)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120
```

### Install PHP Redis Extension:
```bash
# Ubuntu/Debian
sudo apt install php-redis

# CentOS/RHEL
sudo yum install php-redis
# or
sudo dnf install php-redis

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm  # Adjust version as needed
```

### Update config/queue.php:
```php
'redis' => [
    'driver' => 'redis',
    'connection' => 'default',
    'queue' => env('REDIS_QUEUE', 'default'),
    'retry_after' => 90,
    'block_for' => null,
    'after_commit' => false,
],
```

### Update config/database.php:
```php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),
    'options' => [
        'cluster' => env('REDIS_CLUSTER', 'redis'),
        'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
    ],
    'default' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_DB', 0),
    ],
    'cache' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_CACHE_DB', 1),
    ],
],
```

## 4. Queue Worker Setup

### Create Systemd Service for Queue Worker:
```bash
sudo nano /etc/systemd/system/laravel-queue.service
```

### Service Content:
```ini
[Unit]
Description=Laravel Queue Worker
After=network.target redis.service
Requires=redis.service

[Service]
User=www-data
Group=www-data
Restart=always
RestartSec=3
ExecStart=/usr/bin/php /path/to/your/laravel/app/artisan queue:work --tries=3 --timeout=90 --sleep=3 --max-jobs=1000
WorkingDirectory=/path/to/your/laravel/app
Environment=QUEUE_CONNECTION=redis

[Install]
WantedBy=multi-user.target
```

### Enable and Start Queue Service:
```bash
sudo systemctl daemon-reload
sudo systemctl enable laravel-queue
sudo systemctl start laravel-queue
sudo systemctl status laravel-queue
```

### Multiple Queue Workers (for high traffic):
```bash
# Create multiple instances
sudo cp /etc/systemd/system/laravel-queue.service /etc/systemd/system/laravel-queue-2.service
sudo cp /etc/systemd/system/laravel-queue.service /etc/systemd/system/laravel-queue-3.service

# Edit each service to use different queue names if needed
sudo systemctl enable laravel-queue-2
sudo systemctl enable laravel-queue-3
sudo systemctl start laravel-queue-2
sudo systemctl start laravel-queue-3
```

## 5. Monitoring and Maintenance

### Monitor Redis:
```bash
# Check Redis info
redis-cli info

# Monitor commands in real-time
redis-cli monitor

# Check queue length
redis-cli llen queues:default
```

### Laravel Queue Monitoring:
```bash
# Check pending jobs
php artisan queue:status

# Clear failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Monitor queue workers
ps aux | grep "queue:work"
```

### Log Rotation:
```bash
# Redis logs
sudo nano /etc/logrotate.d/redis
```
/var/log/redis/*.log {
    weekly
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 redis redis
    postrotate
        systemctl reload redis
    endscript
}
```

## 6. Performance Tuning

### Redis Memory Optimization:
```bash
# Set appropriate memory limits based on your server
# Edit /etc/redis/redis.conf
maxmemory 512mb  # Adjust based on available RAM
maxmemory-policy allkeys-lru
```

### Laravel Queue Optimization:
```bash
# Use different queues for different priorities
php artisan queue:work --queue=high,default,low

# Monitor and adjust worker count based on load
# Aim for queue length to stay under 100 during peak hours
```

## 7. Backup and Recovery

### Redis Persistence:
```bash
# Enable RDB snapshots
# Edit /etc/redis/redis.conf
save 900 1
save 300 10
save 60 10000

# Enable AOF (Append Only File) for better durability
appendonly yes
appendfsync everysec
```

### Backup Script:
```bash
#!/bin/bash
# /usr/local/bin/redis-backup.sh
DATE=$(date +%Y%m%d_%H%M%S)
redis-cli bgsave
cp /var/lib/redis/dump.rdb /var/backups/redis/dump_$DATE.rdb
find /var/backups/redis -name "dump_*.rdb" -mtime +7 -delete
```

### Cron Job for Backups:
```bash
crontab -e
# Add: 0 2 * * * /usr/local/bin/redis-backup.sh
```

## 8. Security Hardening

### Redis Security:
```bash
# Bind to localhost only
# Edit /etc/redis/redis.conf
bind 127.0.0.1

# Set password
requirepass your_secure_redis_password

# Disable dangerous commands
rename-command FLUSHDB ""
rename-command FLUSHALL ""
rename-command DEBUG ""
rename-command CONFIG ""
```

### Firewall Rules:
```bash
# Allow Redis only from localhost
sudo ufw allow from 127.0.0.1 to any port 6379
sudo ufw --force enable
```

## 9. Troubleshooting

### Common Issues:

#### Queue Jobs Not Processing:
```bash
# Check queue worker status
sudo systemctl status laravel-queue

# Check Redis connection
redis-cli ping

# Check Laravel logs
tail -f storage/logs/laravel.log
```

#### High Memory Usage:
```bash
# Monitor Redis memory
redis-cli info memory

# Check largest keys
redis-cli --bigkeys
```

#### Slow Performance:
```bash
# Check Redis latency
redis-cli --latency

# Monitor slow commands
redis-cli slowlog get 10
```

## 10. Scaling Strategies

### Horizontal Scaling:
- Use Redis Cluster for multiple Redis instances
- Load balance queue workers across multiple servers
- Use separate Redis instances for cache vs queues

### Vertical Scaling:
- Increase Redis memory limits
- Add more CPU cores for queue processing
- Use SSD storage for better I/O performance

## 11. Health Checks

### Create Health Check Script:
```bash
#!/bin/bash
# /usr/local/bin/health-check.sh

# Check Redis
if ! redis-cli ping | grep -q PONG; then
    echo "Redis is down"
    exit 1
fi

# Check queue workers
if ! pgrep -f "queue:work" > /dev/null; then
    echo "Queue workers are down"
    exit 1
fi

# Check queue length
QUEUE_LENGTH=$(redis-cli llen queues:default)
if [ "$QUEUE_LENGTH" -gt 1000 ]; then
    echo "Queue is backed up: $QUEUE_LENGTH jobs"
    exit 1
fi

echo "All services healthy"
exit 0
```

### Add to Monitoring:
```bash
# Cron job every 5 minutes
*/5 * * * * /usr/local/bin/health-check.sh
```

## 12. Migration from Database Queues

If migrating from database queues to Redis:

```bash
# Stop current queue workers
sudo systemctl stop laravel-queue

# Update .env
QUEUE_CONNECTION=redis

# Clear config cache
php artisan config:clear
php artisan config:cache

# Start new queue workers
sudo systemctl start laravel-queue

# Monitor for any failed jobs
php artisan queue:failed
```

## Checklist for Go-Live:

- [ ] Redis server installed and running
- [ ] Redis configured with proper security
- [ ] Laravel .env updated with Redis settings
- [ ] PHP Redis extension installed
- [ ] Queue workers configured and running
- [ ] Monitoring and alerting set up
- [ ] Backup strategy implemented
- [ ] Performance tested with load
- [ ] Failover plan documented

---

**Note**: This setup provides a production-ready Redis configuration optimized for Laravel queue processing and caching. Adjust memory limits and worker counts based on your specific traffic patterns and server resources.