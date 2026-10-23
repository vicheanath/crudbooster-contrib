-- Separate database for the test suite so `composer test:mysql` never touches the playground data.
CREATE DATABASE IF NOT EXISTS crudbooster_test;
GRANT ALL PRIVILEGES ON crudbooster_test.* TO 'crudbooster'@'%';
