1. Run:

```
composer install
```

2. Copy example.env and name .env.

3. Run:

```
php artisan key:generate
```

4. Add Wazuh info in .env:

```
WAZUH_BASE_URL=
WAZUH_USERNAME=
WAZUH_PASSWORD=
WAZUH_VERIFY_SSL=false
```

5. Add AI API key/keys and email credentials or disable later on AI generation and email notifications in dashboard settings:

```
MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

ADMIN_EMAIL=admin@yourdomain.com

ANTHROPIC_API_KEY=
COHERE_API_KEY=
ELEVENLABS_API_KEY=
GEMINI_API_KEY=
MISTRAL_API_KEY=
OLLAMA_API_KEY=
OPENAI_API_KEY=
JINA_API_KEY=
VOYAGEAI_API_KEY=
XAI_API_KEY=
```

6. Migrate DB with seeders for user:

```
php artisan migrate --seed
```

Or create user manually:

```
php artisan make:filament-user
```

7. Login into dashboard go to settings tab and adjust settings. For more AI providers and models add them in ```app/Enums/AiProvider```.

8. In Wazuh ossec.conf add:

```
<ossec_config>
  <integration>
    <name>custom-webhook</name>
    <hook_url>http://your-server-ip/api/wazuh/webhook</hook_url>
    <level>3</level>
    <alert_format>json</alert_format>
  </integration>
</ossec_config>
```

then restart Wazuh.

9. To start storing incidents run:

```
php artisan queue:work
```

or

```
sudo apt install supervisor
// on linux
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

add this to the config

```
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=your-user
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
```

and run:

```
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```