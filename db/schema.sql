CREATE DATABASE adnu_cevas;
USE adnu_cevas;
CREATE TABLE users (
      id INT AUTO_INCREMENT PRIMARY KEY,
      full_name VARCHAR(255) NOT NULL,
      email VARCHAR(255) NOT NULL UNIQUE,
      password VARCHAR(255) NOT NULL,
      id_number VARCHAR(50) NOT NULL UNIQUE,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE certificates (
      id INT AUTO_INCREMENT PRIMARY KEY,
      student_id VARCHAR(50) NOT NULL,
      certificate_name VARCHAR(255) NOT NULL,
      date_issued DATE NOT NULL,
      hash VARCHAR(255) NOT NULL,
      FOREIGN KEY (student_id) REFERENCES users(id_number) ON DELETE CASCADE
); 