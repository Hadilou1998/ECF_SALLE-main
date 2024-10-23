-- Supprimer la base si elle existe déjà
DROP DATABASE IF EXISTS db_shy;

-- Créer la base de données
CREATE DATABASE db_shy;

-- Privilèges pour l'utilisateur
GRANT ALL PRIVILEGES ON db_shy.* TO 'root'@'%';
FLUSH PRIVILEGES;