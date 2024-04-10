<?php

// Connessione al database
$servername = "localhost";
$username = "program";
$password = "777";
$dbname = "esempio_webservice";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica della connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

//echo $_SERVER['REQUEST_URI'];

$array = explode('/',$_SERVER['REQUEST_URI']);
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    if (count($array) == 3 && $array[2] != '') {
        // Se è specificato un ID nella richiesta GET
        $id = $array[2];
        $sql = "SELECT * FROM dati WHERE id = $id";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo json_encode($row);
        } else {
            echo "Nessun risultato trovato con ID $id";
        }
    } elseif (count($array) == 3 && $array[2] == '') {
        // Se non è specificato un ID nella richiesta GET
        $sql = "SELECT * FROM dati";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $rows = array();
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            echo json_encode($rows);
        } else {
            echo "Nessun risultato trovato nella tabella.";
        }
    } else {
        // Se il metodo HTTP non è GET
        http_response_code(405); // Metodo non consentito
        echo "Metodo non consentito";
    }
} elseif ($method == 'POST') {
    // Esegui l'inserimento dei dati
    $data = json_decode(file_get_contents("php://input"), true);

    // Verifica se i dati sono stati inviati correttamente
    if (!empty($data)) {
        // Esegui l'inserimento nel database
        $sql = "INSERT INTO dati (campo1, campo2, campo3) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $data['campo1'], $data['campo2'], $data['campo3']);

        if ($stmt->execute()) {
            echo "Dati inseriti con successo.";
        } else {
            echo "Errore durante l'inserimento dei dati.";
        }
    } else {
        echo "Dati non validi.";
    }
} elseif ($method == 'PUT') {
    // Esegui l'aggiornamento dei dati
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Verifica se l'ID è stato fornito
    if (count($array) == 3 && $array[2] != '') {
        $id = $array[2];
        
        // Esegui l'aggiornamento nel database
        $sql = "UPDATE dati SET campo1=?, campo2=?, campo3=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $data['campo1'], $data['campo2'], $data['campo3'], $id);

        if ($stmt->execute()) {
            echo "Dati aggiornati con successo.";
        } else {
            echo "Errore durante l'aggiornamento dei dati.";
        }
    } else {
        echo "ID non specificato.";
    }
} elseif ($method == 'DELETE') {
    // Esegui la cancellazione dei dati
    if (count($array) == 3 && $array[2] != '') {
        $id = $array[2];
        
        // Esegui la cancellazione nel database
        $sql = "DELETE FROM dati WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "Dati cancellati con successo.";
        } else {
            echo "Errore durante la cancellazione dei dati.";
        }
    } else {
        echo "ID non specificato.";
    }
} else {
    // Se il metodo HTTP non è supportato
    http_response_code(405); // Metodo non consentito
    echo "Metodo non consentito";
}

$conn->close();

?>
