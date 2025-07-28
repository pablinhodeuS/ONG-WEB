<?php
// Project.php

class Project {
    // Conexión a la base de datos y nombre de la tabla
    private $conn;
    private $table_name = "PROYECTO"; // Asegúrate de que este sea el nombre de tu tabla de proyectos

    // Propiedades del objeto (corregidas para coincidir con la DB y el formulario)
    public $id_proyecto;
    public $name;          // Mapea a 'nombre' en la DB
    public $description;   // Mapea a 'descripcion' en la DB
    public $target_amount; // Mapea a 'meta_financiera' en la DB
    public $current_amount;// Mapea a 'monto_recaudado' en la DB
    public $start_date;    // Mapea a 'fecha_inicio' en la DB
    public $end_date;      // Mapea a 'fecha_fin' en la DB
    public $estado;        // ¡Nueva propiedad para el campo 'estado' en la DB!

    // Constructor con $db como conexión a la base de datos
    public function __construct($db){
        $this->conn = $db;
    }

    // Método para crear un nuevo proyecto
    public function create(){
        // Consulta INSERT - Incluyendo 'estado'
        $query = "INSERT INTO " . $this->table_name . "
                  SET
                    nombre=:name,
                    descripcion=:description,
                    meta_financiera=:target_amount,
                    monto_recaudado=:current_amount,
                    fecha_inicio=:start_date,
                    fecha_fin=:end_date,
                    estado=:estado"; // ¡Añadido 'estado'!

        // Preparar la declaración
        $stmt = $this->conn->prepare($query);

        // Limpiar los datos
        $this->name=htmlspecialchars(strip_tags($this->name));
        $this->description=htmlspecialchars(strip_tags($this->description));
        $this->target_amount=htmlspecialchars(strip_tags($this->target_amount));
        $this->current_amount=htmlspecialchars(strip_tags($this->current_amount));
        $this->start_date=htmlspecialchars(strip_tags($this->start_date));
        $this->end_date=htmlspecialchars(strip_tags($this->end_date));
        $this->estado=htmlspecialchars(strip_tags($this->estado)); // ¡Limpiar 'estado'!


        // Vincular los valores
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":target_amount", $this->target_amount);
        $stmt->bindParam(":current_amount", $this->current_amount);
        $stmt->bindParam(":start_date", $this->start_date);
        $stmt->bindParam(":end_date", $this->end_date);
        $stmt->bindParam(":estado", $this->estado); // ¡Vincular 'estado'!

        // Ejecutar la consulta
        if($stmt->execute()){
            return true;
        }

        return false;
    }

    // Método para leer (listar) todos los proyectos
    public function read(){
        $query = "SELECT
                    id_proyecto, nombre, descripcion, meta_financiera, monto_recaudado, fecha_inicio, fecha_fin, estado
                  FROM
                    " . $this->table_name . "
                  ORDER BY
                    fecha_inicio DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Método para leer un solo proyecto por ID
    public function readOne(){
        $query = "SELECT
                    id_proyecto, nombre, descripcion, meta_financiera, monto_recaudado, fecha_inicio, fecha_fin, estado
                  FROM
                    " . $this->table_name . "
                  WHERE
                    id_proyecto = ?
                  LIMIT
                    0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_proyecto);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Asignar valores a las propiedades del objeto desde los nombres de columna de la DB
        if ($row) {
            $this->id_proyecto = $row['id_proyecto'];
            $this->name = $row['nombre'];
            $this->description = $row['descripcion'];
            $this->target_amount = $row['meta_financiera'];
            $this->current_amount = $row['monto_recaudado'];
            $this->start_date = $row['fecha_inicio'];
            $this->end_date = $row['fecha_fin'];
            $this->estado = $row['estado']; // ¡Asignar 'estado'!
        }
    }

    // Método para actualizar un proyecto
    public function update(){
        $query = "UPDATE
                    " . $this->table_name . "
                  SET
                    nombre=:name,
                    descripcion=:description,
                    meta_financiera=:target_amount,
                    monto_recaudado=:current_amount,
                    fecha_inicio=:start_date,
                    fecha_fin=:end_date,
                    estado=:estado    -- ¡Añadido 'estado'!
                  WHERE
                    id_proyecto = :id_proyecto";

        $stmt = $this->conn->prepare($query);

        // Limpiar los datos
        $this->name=htmlspecialchars(strip_tags($this->name));
        $this->description=htmlspecialchars(strip_tags($this->description));
        $this->target_amount=htmlspecialchars(strip_tags($this->target_amount));
        $this->current_amount=htmlspecialchars(strip_tags($this->current_amount));
        $this->start_date=htmlspecialchars(strip_tags($this->start_date));
        $this->end_date=htmlspecialchars(strip_tags($this->end_date));
        $this->estado=htmlspecialchars(strip_tags($this->estado)); // ¡Limpiar 'estado'!
        $this->id_proyecto=htmlspecialchars(strip_tags($this->id_proyecto));

        // Vincular los valores
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':target_amount', $this->target_amount);
        $stmt->bindParam(':current_amount', $this->current_amount);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':estado', $this->estado); // ¡Vincular 'estado'!
        $stmt->bindParam(':id_proyecto', $this->id_proyecto);

        // Ejecutar la consulta
        if($stmt->execute()){
            return true;
        }

        return false;
    }

    // Método para eliminar un proyecto
    public function delete(){
        $query = "DELETE FROM " . $this->table_name . " WHERE id_proyecto = ?";

        $stmt = $this->conn->prepare($query);
        $this->id_proyecto=htmlspecialchars(strip_tags($this->id_proyecto));
        $stmt->bindParam(1, $this->id_proyecto);

        if($stmt->execute()){
            return true;
        }

        return false;
    }
}
?>