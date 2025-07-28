<?php
// Donor.php

class Donor {
    private $conn; // Objeto de conexión PDO

    public $id_donante;
    public $nombre;
    public $apellido;
    public $email;
    public $telefono;
    public $direccion;

    // El constructor ahora recibe la conexión PDO
    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para crear un nuevo donante
    public function create() {
        // Verificar si el email ya existe
        $query_check = "SELECT id_donante FROM DONANTE WHERE email = :email LIMIT 0,1";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->bindParam(":email", $this->email);
        $stmt_check->execute();

        if ($stmt_check->rowCount() > 0) {
            // El email ya existe
            return false;
        }

        $query = "INSERT INTO DONANTE (nombre, apellido, email, telefono, direccion) VALUES (:nombre, :apellido, :email, :telefono, :direccion)";
        $stmt = $this->conn->prepare($query);

        // Limpiar y enlazar valores
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellido = htmlspecialchars(strip_tags($this->apellido));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        $this->direccion = htmlspecialchars(strip_tags($this->direccion));

        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":apellido", $this->apellido);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":direccion", $this->direccion);

        if ($stmt->execute()) {
            $this->id_donante = $this->conn->lastInsertId(); // Obtener el ID del último insert
            return true;
        }
        return false;
    }

    // Método para leer todos los donantes
    public function read() {
        $query = "SELECT id_donante, nombre, apellido, email, telefono, direccion FROM DONANTE ORDER BY nombre ASC, apellido ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt; // Retorna el PDOStatement para ser usado en manage_donors.php
    }

    // Método para actualizar un donante
    public function update() {
        // Verificar si el email ya existe para otro donante
        $query_check = "SELECT id_donante FROM DONANTE WHERE email = :email AND id_donante != :id_donante LIMIT 0,1";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->bindParam(":email", $this->email);
        $stmt_check->bindParam(":id_donante", $this->id_donante);
        $stmt_check->execute();

        if ($stmt_check->rowCount() > 0) {
            // El email ya existe para otro donante
            return false;
        }

        $query = "UPDATE DONANTE SET nombre = :nombre, apellido = :apellido, email = :email, telefono = :telefono, direccion = :direccion WHERE id_donante = :id_donante";
        $stmt = $this->conn->prepare($query);

        // Limpiar y enlazar valores
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellido = htmlspecialchars(strip_tags($this->apellido));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        $this->direccion = htmlspecialchars(strip_tags($this->direccion));
        $this->id_donante = htmlspecialchars(strip_tags($this->id_donante)); // Asegurarse de que el ID es seguro

        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':apellido', $this->apellido);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':telefono', $this->telefono);
        $stmt->bindParam(':direccion', $this->direccion);
        $stmt->bindParam(':id_donante', $this->id_donante);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Método para eliminar un donante
    public function delete() {
        // La restricción FOREIGN KEY ON DELETE SET NULL ya se encarga de esto para DONACION
        $query = "DELETE FROM DONANTE WHERE id_donante = :id_donante";
        $stmt = $this->conn->prepare($query);

        $this->id_donante = htmlspecialchars(strip_tags($this->id_donante));
        $stmt->bindParam(':id_donante', $this->id_donante);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>