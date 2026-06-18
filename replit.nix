{pkgs}: {
  deps = [
    pkgs.php82Extensions.mongodb
    pkgs.php82Extensions.redis
    pkgs.php82Extensions.pdo_mysql
    pkgs.mongodb
    pkgs.redis
    pkgs.mariadb
  ];
}
