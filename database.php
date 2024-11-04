<?php
class Database {
    private $conn;
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "chatbox";

    public function __construct() {
        // Tạo kết nối đến cơ sở dữ liệu
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);

        // Kiểm tra kết nối
        if ($this->conn->connect_error) {
            die("Kết nối thất bại: " . $this->conn->connect_error);
        }
    }

    // Phương thức tạo (Create)
    public function create($table, $data) {
        $keys = implode(", ", array_keys($data));
        $values = implode(", ", array_fill(0, count($data), '?'));

        $stmt = $this->conn->prepare("INSERT INTO $table ($keys) VALUES ($values)");

        $types = str_repeat('s', count($data)); // Xác định loại dữ liệu (string)
        $stmt->bind_param($types, ...array_values($data));

        if ($stmt->execute()) {
            $stmt->close();
            return $this->conn->insert_id; // Trả về ID của bản ghi vừa chèn
        } else {
            $stmt->close();
            return false;
        }
    }

    // Phương thức đọc (Read)
    public function read($table, $conditions = [], $orderBy = [], $limit = null) {
        $sql = "SELECT * FROM $table";

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = ?";
            }
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY " . $orderBy[0] . " " . $orderBy[1];

        if ($limit !== null) {
            $sql .= " LIMIT $limit";
        }

        $stmt = $this->conn->prepare($sql);

        if (!empty($conditions)) {
            $types = str_repeat('s', count($conditions)); // Xác định loại dữ liệu (string)
            $stmt->bind_param($types, ...array_values($conditions));
        }

        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function executeQuery($sql)
    {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Phương thức cập nhật (Update)
    public function update($table, $data, $conditions) {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "$key = ?";
        }
        $sql = "UPDATE $table SET " . implode(", ", $set);

        $where = [];
        foreach ($conditions as $key => $value) {
            $where[] = "$key = ?";
        }
        $sql .= " WHERE " . implode(" AND ", $where);

        $stmt = $this->conn->prepare($sql);

        $types = str_repeat('s', count($data) + count($conditions));
        $stmt->bind_param($types, ...array_merge(array_values($data), array_values($conditions)));

        if ($stmt->execute()) {
            $result =  $stmt->affected_rows; // Trả về số lượng bản ghi bị ảnh hưởng
            $stmt->close();
            return $result;
        } else {
            $stmt->close();
            return false;
        }

    }

    // Phương thức xóa (Delete)
    public function delete($table, $conditions) {
        $sql = "DELETE FROM $table";

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = ?";
            }
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $stmt = $this->conn->prepare($sql);

        if (!empty($conditions)) {
            $types = str_repeat('s', count($conditions)); // Xác định loại dữ liệu (string)
            $stmt->bind_param($types, ...array_values($conditions));
        }

        if ($stmt->execute()) {
            return $stmt->affected_rows; // Trả về số lượng bản ghi bị ảnh hưởng
        } else {
            return "Lỗi: " . $stmt->error;
        }

        $stmt->close();
    }

    // Phương thức tìm bản ghi đầu tiên (Find First)
    public function findFirst($table, $conditions = []) {
        $sql = "SELECT * FROM $table";

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = ?";
            }
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " LIMIT 1"; // Chỉ lấy bản ghi đầu tiên

        $stmt = $this->conn->prepare($sql);

        if (!empty($conditions)) {
            $types = str_repeat('s', count($conditions)); // Xác định loại dữ liệu (string)
            $stmt->bind_param($types, ...array_values($conditions));
        }

        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc(); // Trả về bản ghi đầu tiên
    }

    // Phương thức đóng kết nối
    public function close() {
        $this->conn->close();
    }
}
?>
