
    <?php
    session_start();
    include 'config.php';
    include 'functions/dashboard_queries.php';

    if (!isset($_SESSION['user'])) {
        header("Location: pages-login.html");
        exit();
    }

    $user_info = $_SESSION['user'];
    $dashboardData = getDashboardData($conn);

    if ($dashboardData === false) {
        // Manejar el error
        die("Error al cargar los datos del dashboard");
    }

    $totals = $dashboardData['totals'];
    $notifications = $dashboardData['notifications'];
    $worker_loans = $dashboardData['worker_loans'];
    $client_loans = $dashboardData['client_loans'];

    $sql = "SELECT * FROM loan_information";
    $result = $conn->query($sql);
    ?>

    <?php include '../Financiera/views/head.php'; ?>

    <?php include '../Financiera/views/header.php'; ?>

    <?php include '../Financiera/views/nav_menu.php'; ?>
    <!-- ======= Main ======= -->
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Notificaciones</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Notificaciones</li>
                </ol>
            </nav>
        </div>
        <section class="section dashboard">
            <div class="row">
                <div class="col-lg-8">
                    <div class="row">
                        <h2>Todas las Notificaciones</h2>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Cantidad</th>
                                    <th>Nombre</th>
                                    <th>Fecha</th>
                                    <th>Más Información</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($result) && $result && $result->rowCount() > 0): ?>
                                    <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row["loan_type"]); ?></td>
                                            <td><?php echo htmlspecialchars($row["requested_amount"]); ?></td>
                                            <td><?php echo htmlspecialchars($row["first_name"] . " " . $row["last_name"]); ?></td>
                                            <td><?php echo htmlspecialchars($row["form_fill_date"]); ?></td>
                                            <td>
                                                <?php if ($row['loan_status'] === 'No Aceptado'): ?>
                                                    <form method="post" action="accept_loan.php">
                                                        <input type="hidden" name="loan_id" 
                                                            value="<?php echo htmlspecialchars($row['id']); ?>">
                                                        <button type="submit" class="btn btn-primary">Aceptar Préstamo</button>
                                                    </form>
                                                <?php else: ?>
                                                    <p>Préstamo aceptado</p>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5">No se encontraron registros</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>
    </body>
    <?php include '../Financiera/views/footer.php'; ?>

    </html>