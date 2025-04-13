    <?php
    session_start();
    require '../../config/database.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username']);
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $phone = trim($_POST['phone']);

        if (empty($username) || empty($email) || empty($password)) {
            $_SESSION['error'] = 'Vui lòng nhập đầy đủ họ tên, email và mật khẩu.';
            header('Location: /your_project_root/?tab=register');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Email không hợp lệ.';
            header('Location: /your_project_root/?tab=register');
            exit;
        }

        if (strlen($username) > 255 || strlen($email) > 255 || strlen($phone) > 20) {
            $_SESSION['error'] = 'Dữ liệu nhập quá dài.';
            header('Location: /your_project_root/?tab=register');
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Mật khẩu phải có ít nhất 6 ký tự.';
            header('Location: /your_project_root/?tab=register');
            exit;
        }

        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM user WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $_SESSION['error'] = 'Email đã được sử dụng.';
                header('Location: /your_project_root/?tab=register');
                exit;
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO user (username, email, password, phone, create_at) VALUES (?, ?, ?, ?, NOW())');
            $stmt->execute([$username, $email, $hashed_password, $phone ?: null]);

            // Đăng nhập tự động sau khi đăng ký
            $stmt = $pdo->prepare('SELECT * FROM user WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];

            // Chuyển hướng đến Dashboard
            header('Location: /your_project_root/pages/dashboard.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Đã có lỗi xảy ra. Vui lòng thử lại sau.';
            header('Location: /your_project_root/?tab=register');
            exit;
        }
    } else {
        header('Location: /your_project_root/?tab=register');
        exit;
    }
    ?>