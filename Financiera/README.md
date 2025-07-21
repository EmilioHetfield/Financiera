docker run -d \
  --name apache-php \
  -p 8081:80 \
  -v /Users/gmendoza/Documents/Proyectos/Financiera/efloresrTest/Financiera:/var/www/html \
  php:8.2-apache
