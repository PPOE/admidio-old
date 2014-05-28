#!/bin/bash
su www-data -c "php /var/www/adm_api/registration.php" >> /var/log/lgfmail.log
su www-data -c "php /var/www/adm_api/accsync.php" >> /var/log/lgfmail.log
su www-data -c "php /var/www/adm_api/lgfmail.php" >> /var/log/lgfmail.log

