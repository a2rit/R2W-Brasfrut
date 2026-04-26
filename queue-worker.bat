cd /d C:\path\to\your\laravel\project
C:\php\php.exe artisan queue:work database --sleep=3 --tries=3 --timeout=90 >> storage\logs\queue.log 2>&1