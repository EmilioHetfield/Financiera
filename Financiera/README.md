docker run -d \
  --name apache-php \
  -p 8081:80 \
  -v /Users/gmendoza/Documents/Proyectos/Financiera/efloresrTest/Financiera:/var/www/html \
  php:8.2-apache







gmendoza@MacBook-Air-de-Gabriel ~ % docker run -d \                            
  --name phpmyadmin \
  -e PMA_HOST=mariadb \
  -p 8080:80 \                         
  --link mariadb \
  phpmyadmin/phpmyadmin

gmendoza@MacBook-Air-de-Gabriel ~ % docker run -d \
  --name mariadb \   
  --network globaltec_net \
  -e MYSQL_ROOT_PASSWORD=rootpass \
  -e MYSQL_DATABASE=droopyst_testFinanciera \
  -e MYSQL_USER=droopyst_test \
  -e MYSQL_PASSWORD=M3nd0z@2020. \
  mariadb:10.5



gmendoza@MacBook-Air-de-Gabriel ~ % 
docker run -d \
   --name phpmyadmin \
  --network globaltec_net \
  -e PMA_HOST=mariadb \
  -p 8080:80 \                         
  --link mariadb \
  phpmyadmin/phpmyadmin
