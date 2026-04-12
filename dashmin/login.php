<?php
session_start();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="pt">

<head>

    <meta charset="utf-8">
    <title>DASHMIN - Bootstrap Admin Template</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", sans-serif;
        }

        body {
            background: #f4f6f9;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            
        }

        .login-container {
            width: 380px;
            background: #fff;
            padding: 40px 35px;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);

        }

        .login-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .logo {
            display: flex;
            align-items: center;
            font-size: 28px;
            font-weight: 700;
            color: #1e88e5;
        }

        .logo i {
            margin-right: 8px;
        }

        .login-header h2 {
            font-weight: 600;
            font-size: 28px;
            color: #333;
        }

        .input-group {
            margin-bottom: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        }

        .input-group input {
            width: 100%;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            background: #f8f9fb;
            font-size: 14px;
            transition: 0.3s;
        }

        .input-group input:focus {
            border-color: #1e88e5;
            background: #fff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
        }

        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            margin-bottom: 80px;

        }

        .options a {
            text-decoration: none;
            color: #1e88e5;

        }

        .options a:hover {
            text-decoration: underline;
        }

        button {
            width: 100%;
            padding: 15px;
            border-radius: 8px;
            border: none;
            background: #1e88e5;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #1565c0;
        }

        .signup {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .signup a {
            color: #1e88e5;
            text-decoration: none;
            font-weight: 500;
        }

        .signup a:hover {
            text-decoration: underline;
        }

       .alert {
    position: fixed;
    top: 20px;
    min-width: 250px;
    padding: 15px;
    border-radius: 8px;
    z-index: 9999;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    opacity: 1;
    transition: opacity 0.5s ease, transform 0.5s ease;
}

.alert.hide {
    opacity: 0;
    transform: translateY(-20px);
}

.danger {
    background: white;
    color:#ff4d4d ;
}

.success {
    background: #4CAF50;
    color: white;
}
    </style>
</head>

<body>
 <?php if (!empty($_SESSION['alerta'])): ?>
    <div id="alerta" class="alert <?= $_SESSION['alerta']['tipo'] ?>">
        <?= htmlspecialchars($_SESSION['alerta']['msg']) ?>
    </div>
    <?php unset($_SESSION['alerta']); ?>
<?php endif; ?>

    <div class="login-container">

        <div class="login-header">
            <div class="logo">
                <i class="fa-solid fa-building-columns"></i> SGE-ECP
            </div>
            <h2>Entrar</h2>
        </div>

        <form action="funcao_login.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="input-group">
                <input type="username" name="username" placeholder="Nome de utilizador" required>
            </div>

            <div class="input-group" style="position:relative;">
                <input type="password" name="senha" id="senha" placeholder="Password" required>

                <i class="fa-solid fa-eye" id="toggleSenha" style="position:absolute; right:15px; top:50%; transform:translateY(-50%);
       cursor:pointer; color:#666;"></i>
            </div>

            <div class="options">
                <label>
                    <input type="checkbox">Lembrar-me
                </label>
                
            </div>

            <button type="submit">Entrar</button>

        </form>

    </div>

</body>
<script>
    const toggleSenha = document.getElementById("toggleSenha");
    const senha = document.getElementById("senha");

    toggleSenha.addEventListener("click", function () {

        const tipo = senha.getAttribute("type") === "password" ? "text" : "password";
        senha.setAttribute("type", tipo);

        this.classList.toggle("fa-eye");
        this.classList.toggle("fa-eye-slash");

    });
</script>

</html>