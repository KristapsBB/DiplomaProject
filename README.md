Minimum system requirements:
  php: 8.3.6
  apache2: 2.4

Before the first run, you must:
  1) run the migrations/init.sql script in phpMyAdmin to initialize the database
  2) run the migrations/migration-20240427072053.sql script in phpMyAdmin to initialize the `users` table
  3) run the migrations/migration-20240510224039.sql script in phpMyAdmin to initialize the `tenders` table

Configuring Apache:
```bash
  sudo mkdir /var/www/diploma-project && cd /var/www/diploma-project
  sudo cp ./diploma-project.conf /etc/apache2/sites-available/diploma-project11.conf
  sudo a2ensite diploma-project.conf
  sudo systemctl restart apache2s
  sudo chown -R :www-data /var/www/diploma-project
  xdg-open http://diploma-project.localhost

```

Login details to the admin panel:
  login: admin
  password: 321
