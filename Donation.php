<?php
// Donation.php

class Donation {
    private $conn;
    private $table_name = "donacion"; // Asegúrate que el nombre de la tabla sea correcto, según tu phpMyAdmin

    // Propiedades de la donación
    public $id_donacion;
    public $id_proyecto;
    public $id_donante;
    public $monto;
    public $fecha; // <-- ¡Asegúrate que esta propiedad se llame 'fecha' para coincidir con la DB!
    public $metodo_pago; // Si tienes este campo en tu tabla DONACION, inclúyelo

    // Constructor con conexión a BD
    public function __construct($db){
        $this->conn = $db;
    }

    // Método para crear una donación
    function create(){
        // Consulta INSERT
        // Asegúrate de que los nombres de las columnas aquí coincidan EXACTAMENTE con tu tabla DONACION en phpMyAdmin
        $query = "INSERT INTO " . $this->table_name . "
                  SET
                      id_proyecto=:id_proyecto,
                      id_donante=:id_donante,
                      monto=:monto,
                      fecha=:fecha"; // <-- Asegúrate que la columna se llame 'fecha' aquí también

        // Prepara la consulta
        $stmt = $this->conn->prepare($query);

        // Sanitar los datos (esto es básico, podrías añadir más validación)
        $this->id_proyecto=htmlspecialchars(strip_tags($this->id_proyecto));
        $this->id_donante=htmlspecialchars(strip_tags($this->id_donante));
        $this->monto=htmlspecialchars(strip_tags($this->monto));
        $this->fecha=htmlspecialchars(strip_tags($this->fecha));

        // Vincular los valores
        $stmt->bindParam(":id_proyecto", $this->id_proyecto);
        $stmt->bindParam(":id_donante", $this->id_donante);
        $stmt->bindParam(":monto", $this->monto);
        $stmt->bindParam(":fecha", $this->fecha);

        // Ejecutar la consulta
        if($stmt->execute()){
            // Opcional: Actualizar el monto_recaudado en el proyecto
            $update_project_query = "UPDATE proyecto SET monto_recaudado = monto_recaudado + :monto WHERE id_proyecto = :id_proyecto";
            $update_stmt = $this->conn->prepare($update_project_query);
            $update_stmt->bindParam(":monto", $this->monto);
            $update_stmt->bindParam(":id_proyecto", $this->id_proyecto);
            $update_stmt->execute();

            return true;
        }

        return false;
    }

    // Método para leer todas las donaciones (con nombres de proyecto y donante)
    function read(){
        // Esta es la consulta que necesita la corrección
        // Se une con 'proyecto' y 'donante' para mostrar nombres en lugar de solo IDs
        $query = "SELECT
                    DA.id_donacion,
                    DA.monto,
                    DA.fecha, -- <<--- ¡CAMBIADO DE 'fecha_donacion' A 'fecha' !
                    P.nombre as project_name,
                    D.nombre as donor_name,
                    D.apellido as donor_lastname
                FROM
                    " . $this->table_name . " DA
                LEFT JOIN
                    proyecto P ON DA.id_proyecto = P.id_proyecto
                LEFT JOIN
                    donante D ON DA.id_donante = D.id_donante
                ORDER BY
                    DA.fecha DESC, DA.id_donacion DESC"; // <-- También cambiada aquí

        $stmt = $this->conn->prepare($query);
        $stmt->execute(); // <-- La línea 63 del error está aquí, pero es causada por la consulta

        return $stmt;
    }

    // Puedes añadir otros métodos como update(), delete() si son necesarios
}
?>