CREATE DATABASE IF NOT EXISTS impacto_proteccion;
USE impacto_proteccion;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('administrador', 'vendedor', 'asistente') NOT NULL DEFAULT 'asistente',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS activity_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(150) NOT NULL,
  details TEXT NULL,
  ip_address VARCHAR(45) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (user_id),
  FOREIGN KEY (user_id) REFERENCES admins(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  image_url VARCHAR(255) NOT NULL DEFAULT 'https://via.placeholder.com/600x400?text=Protecci%C3%B3n',
  category_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS pending_actions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  action_type VARCHAR(100) NOT NULL,
  entity_type VARCHAR(100) NOT NULL,
  entity_id INT DEFAULT NULL,
  payload TEXT NOT NULL,
  status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  reviewer_id INT DEFAULT NULL,
  reviewer_note TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reviewed_at TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES admins(id) ON DELETE SET NULL,
  FOREIGN KEY (reviewer_id) REFERENCES admins(id) ON DELETE SET NULL
);

INSERT INTO admins (email, password_hash, role) VALUES
('admin@impacto.com', '$2y$10$zUX1zcoPzAYVEKmT4.7a4.i.C3l/eUrsEdkcgzsCPIdFT02vz3taW', 'administrador'),
('daniel@gmail.com', '$2y$10$QzcqXcVVRZ4nCQLMzcOvkePN8iWrMfXyfisMGC7dl2SDkkvAlPAry', 'administrador');

INSERT INTO categories (name, slug) VALUES
('Cascos', 'cascos'),
('Guantes', 'guantes'),
('Chaquetas', 'chaquetas'),
('Rodilleras', 'rodilleras'),
('Botas', 'botas');

INSERT INTO products (name, description, price, image_url, category_id) VALUES
('Casco Integral Pro', 'Casco con visor panorámico y protección reforzada para carretera.', 159.90, 'https://images.unsplash.com/photo-1517649763962-0c623066013b?auto=format&fit=crop&w=600&q=80', 1),
('Guantes Alpinestar', 'Guantes de cuero con protección dorsal y agarre mejorado.', 89.50, 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=600&q=80', 2),
('Chaqueta de Cuero', 'Chaqueta resistente con protecciones en hombros y codos.', 199.00, 'https://images.unsplash.com/photo-1517649763962-0c623066013b?auto=format&fit=crop&w=600&q=80', 3),
('Rodilleras Sport', 'Rodilleras ajustables con refuerzo lateral y cierre de velcro.', 74.99, 'https://images.unsplash.com/photo-1517649763962-0c623066013b?auto=format&fit=crop&w=600&q=80', 4),
('Botas Touring', 'Botas de alta protección y suela resistente para viajes largos.', 129.90, 'https://images.unsplash.com/photo-1517649763962-0c623066013b?auto=format&fit=crop&w=600&q=80', 5);
