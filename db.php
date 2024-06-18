<?php
ini_set("display_errors", 1);

/**
 * @return \PDO
 * @throws \PDOException
 */
function getPDO(): \PDO
{
  $dbname = 'simple-login';
  $username = 'root';
  $password = '';
  $options = [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
  ];

  $pdo = new \PDO("mysql:host=localhost;port=3306;dbname={$dbname}", $username, $password, $options);

  return $pdo;
}

return getPDO();
