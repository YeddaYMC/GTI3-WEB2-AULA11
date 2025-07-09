<?php
// Headers globais
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-API-KEY");

// Incluir arquivos base
include_once '../config/database.php';
include_once '../auth_check.php';

// Obter conexão com o banco
$database = new Database();
$db = $database->getConnection();

// Obter o método da requisição (GET, POST, etc.)
$method = $_SERVER['REQUEST_METHOD'];

// Obter o ID da URL, se existir (graças ao .htaccess)
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

switch ($method) {
    case 'GET':
        if ($id) {
            $query = "SELECT id, nome, email, telefone FROM clientes WHERE id = :id LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
        } else if (isset($_GET['busca']) && !empty($_GET['busca'])) {
            $busca = '%' . $_GET['busca'] . '%';
            $query = "SELECT id, nome, email, telefone FROM clientes WHERE nome LIKE :busca ORDER BY nome ASC";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':busca', $busca);
        } else {
            $query = "SELECT id, nome, email, telefone FROM clientes ORDER BY nome ASC";
            $stmt = $db->prepare($query);
        }
        
        $stmt->execute();
        $result = $id ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            $etag = md5(json_encode($result));
            header('Etag: ' . $etag);

            if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) {
                http_response_code(304);
                exit();
            }

            http_response_code(200);
            echo json_encode($result);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Nenhum cliente encontrado."]);
        }
        break;

    case 'POST':
        verificarApiKey();
        $data = json_decode(file_get_contents("php://input"));

        // Valida que os dados foram recebidos e que os campos obrigatórios não estão vazios
        if ($data && !empty($data->nome) && !empty($data->email)) {
            $query = "INSERT INTO clientes (nome, email, telefone) VALUES (:nome, :email, :telefone)";
            $stmt = $db->prepare($query);

            // Garante que o telefone seja nulo se não for fornecido, em vez de causar um erro.
            $telefone = $data->telefone ?? null;
            $stmt->bindParam(":nome", $data->nome);
            $stmt->bindParam(":email", $data->email);
            $stmt->bindParam(":telefone", $telefone);

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(["message" => "Cliente foi criado com sucesso."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Não foi possível criar o cliente. O e-mail já pode existir."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Dados incompletos. Nome e e-mail são obrigatórios."]);
        }
        break;

    case 'PUT':
        verificarApiKey();
        $data = json_decode(file_get_contents("php://input"));

        if ($id && $data && !empty($data->nome) && !empty($data->email)) {
            $query = "UPDATE clientes SET nome = :nome, email = :email, telefone = :telefone WHERE id = :id";
            $stmt = $db->prepare($query);

            $telefone = $data->telefone ?? null;
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nome', $data->nome);
            $stmt->bindParam(':email', $data->email);
            $stmt->bindParam(':telefone', $telefone);

            if ($stmt->execute() && $stmt->rowCount() > 0) {
                http_response_code(200);
                echo json_encode(["message" => "Cliente foi atualizado."]);
            } else {
                http_response_code(304);
                echo json_encode(["message" => "Nenhum dado foi modificado ou não foi possível atualizar o cliente."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Dados incompletos ou ID do cliente não fornecido na URL."]);
        }
        break;

    case 'DELETE':
        verificarApiKey();
        if ($id) {
            $query = "DELETE FROM clientes WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute() && $stmt->rowCount() > 0) {
                http_response_code(200);
                echo json_encode(["message" => "Cliente foi deletado."]);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Não foi possível deletar. Cliente não encontrado."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "ID do cliente não fornecido na URL."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Método não permitido."]);
        break;
}
?>
