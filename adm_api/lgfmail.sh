#!/bin/bash
su www-data -c "php /var/www/adm_api/lgfmail.php" >> /var/log/lgfmail.log

