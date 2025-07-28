<?php
// db_connection.php
// Esta versión utiliza PDO y la clase Database que es esperada por Project.php y Donor.php

class Database {
    private $host = "localhost"; // La dirección de tu servidor de base de datos
    private $db_name = "organizacion_social"; // El nombre de tu base de datos
    private $username = "root"; // Tu nombre de usuario de MySQL (por defecto en XAMPP es root)
    private $password = ""; // Tu contraseña de MySQL (por defecto en XAMPP es vacía)
    public $conn;

    /**
     * Obtiene la conexión a la base de datos
     * @return PDO|null Objeto PDO de conexión o null si hay un error
     */
    public function getConnection(){
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                                    $this->username,
                                    $this->password);
            $this->conn->exec("set names utf8");
            // Configurar el modo de error de PDO para lanzar excepciones
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // En un entorno de desarrollo, puedes mostrar el error detallado
            error_log("Error de conexión a base de datos (PDO): " . $exception->getMessage());
            // En producción, es mejor solo loggear y mostrar un mensaje genérico
            die("Error de conexión a la base de datos. Por favor, inténtalo más tarde.");
        }

        return $this->conn;
    }
}
?>