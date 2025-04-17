<?php
// --- PHẦN PHP ĐỂ LẤY DỮ LIỆU ---
include_once ('../connection/connect_database.php');

// Kiểm tra kết nối CSDL
if (!$conn) {
    error_log("Lỗi kết nối CSDL: " . mysqli_connect_error());
    die("Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.");
}

// --- Xử lý lựa chọn Năm/Tháng ---
$current_year = date("Y");
$current_month = date("m");
$selected_year = intval($_GET['selected_year'] ?? $current_year);
$selected_month = intval($_GET['selected_month'] ?? $current_month); // 0 = cả năm

// --- Lấy danh sách các năm có đơn hàng ---
$years_with_orders = [];
$sl_years = "SELECT DISTINCT YEAR(ThoiDiemDatHang) as order_year FROM donhang ORDER BY order_year DESC";
$rs_years = mysqli_query($conn, $sl_years);
if ($rs_years) { while ($row = mysqli_fetch_assoc($rs_years)) { $years_with_orders[] = $row['order_year']; } mysqli_free_result($rs_years); }
else { error_log("Lỗi khi lấy danh sách năm: " . mysqli_error($conn)); }
if (empty($years_with_orders)) { $years_with_orders[] = $current_year; }
if (!in_array($selected_year, $years_with_orders)) { $selected_year = reset($years_with_orders); }

// --- Các thống kê tổng quát ---
$total_accounts = 0; $sl_accounts = "SELECT COUNT(*) as total_accounts FROM users"; $rs_accounts = mysqli_query($conn, $sl_accounts); if ($rs_accounts) { $total_accounts = mysqli_fetch_assoc($rs_accounts)['total_accounts']; mysqli_free_result($rs_accounts); } else { error_log("Lỗi khi đếm tài khoản: " . mysqli_error($conn)); }
$total_products = 0; $sl_products = "SELECT COUNT(*) as total_products FROM sanpham"; $rs_products = mysqli_query($conn, $sl_products); if ($rs_products) { $total_products = mysqli_fetch_assoc($rs_products)['total_products']; mysqli_free_result($rs_products); } else { error_log("Lỗi khi đếm sản phẩm: " . mysqli_error($conn)); }
$total_orders = 0; $sl_orders = "SELECT COUNT(*) as total_orders FROM donhang"; $rs_orders = mysqli_query($conn, $sl_orders); if ($rs_orders) { $total_orders = mysqli_fetch_assoc($rs_orders)['total_orders']; mysqli_free_result($rs_orders); } else { error_log("Lỗi khi đếm đơn hàng: " . mysqli_error($conn)); }

// --- TÍNH TỔNG SẢN PHẨM BÁN ĐƯỢC TRONG GIAI ĐOẠN ---
$total_sold_in_period = 0;
$sql_total_sold = "SELECT SUM(dhc.SoLuong) as total_sold FROM donhangchitiet dhc JOIN donhang dh ON dhc.idDH = dh.idDH WHERE YEAR(dh.ThoiDiemDatHang) = ?";
$stmt_total_sold = null; $params_total_sold = [$selected_year]; $types_total_sold = "i";
if ($selected_month != 0) { $sql_total_sold .= " AND MONTH(dh.ThoiDiemDatHang) = ?"; $params_total_sold[] = $selected_month; $types_total_sold .= "i"; }
$stmt_total_sold = mysqli_prepare($conn, $sql_total_sold);
if ($stmt_total_sold) { mysqli_stmt_bind_param($stmt_total_sold, $types_total_sold, ...$params_total_sold); mysqli_stmt_execute($stmt_total_sold); $result_total_sold = mysqli_stmt_get_result($stmt_total_sold); if ($result_total_sold) { $row_total = mysqli_fetch_assoc($result_total_sold); $total_sold_in_period = $row_total['total_sold'] ?? 0; mysqli_free_result($result_total_sold); } mysqli_stmt_close($stmt_total_sold); }
else { error_log("Lỗi khi chuẩn bị câu lệnh sql_total_sold: " . mysqli_error($conn)); }

// --- TÍNH TỔNG DOANH THU TRONG GIAI ĐOẠN ĐÃ CHỌN (SỬA THEO CỘT GiaBan TỪ HÌNH ẢNH) ---
$total_revenue_in_period = 0;
// ***** SỬA ĐỔI: Lấy giá từ bảng `sanpham` cột `GiaBan` *****
$sql_total_revenue = "SELECT SUM(dhc.SoLuong * sp.GiaBan) as total_revenue
                      FROM donhangchitiet dhc
                      JOIN donhang dh ON dhc.idDH = dh.idDH
                      JOIN sanpham sp ON dhc.idSP = sp.idSP  /* JOIN sanpham để lấy GiaBan */
                      WHERE YEAR(dh.ThoiDiemDatHang) = ?";
$stmt_total_revenue = null; $params_total_revenue = [$selected_year]; $types_total_revenue = "i";
if ($selected_month != 0) { $sql_total_revenue .= " AND MONTH(dh.ThoiDiemDatHang) = ?"; $params_total_revenue[] = $selected_month; $types_total_revenue .= "i"; }
$stmt_total_revenue = mysqli_prepare($conn, $sql_total_revenue);
if ($stmt_total_revenue) { mysqli_stmt_bind_param($stmt_total_revenue, $types_total_revenue, ...$params_total_revenue); mysqli_stmt_execute($stmt_total_revenue); $result_total_revenue = mysqli_stmt_get_result($stmt_total_revenue); if ($result_total_revenue) { $row_revenue = mysqli_fetch_assoc($result_total_revenue); $total_revenue_in_period = $row_revenue['total_revenue'] ?? 0; mysqli_free_result($result_total_revenue); } mysqli_stmt_close($stmt_total_revenue); }
else { error_log("Lỗi khi chuẩn bị câu lệnh sql_total_revenue: " . mysqli_error($conn)); }
// --- Kết thúc tính tổng doanh thu ---

// --- Xử lý dữ liệu cho BIỂU ĐỒ ---
$chart_labels = []; $chart_data_values = []; $chart_title = ""; $x_axis_title = ""; $y_axis_title = "Số lượng sản phẩm bán được";
if ($selected_month == 0) { /* Xem cả năm */ $chart_title = "Số sản phẩm bán theo tháng năm " . $selected_year; $x_axis_title = "Tháng trong năm " . $selected_year; $monthly_sales_data = array_fill(1, 12, 0); $sql_chart = "SELECT MONTH(dh.ThoiDiemDatHang) as month, SUM(dhc.SoLuong) as total_sales FROM donhangchitiet dhc JOIN donhang dh ON dhc.idDH = dh.idDH WHERE YEAR(dh.ThoiDiemDatHang) = ? GROUP BY MONTH(dh.ThoiDiemDatHang) ORDER BY month ASC"; $stmt_chart = mysqli_prepare($conn, $sql_chart); if ($stmt_chart) { mysqli_stmt_bind_param($stmt_chart, "i", $selected_year); mysqli_stmt_execute($stmt_chart); $result_chart = mysqli_stmt_get_result($stmt_chart); if ($result_chart) { while ($r = mysqli_fetch_assoc($result_chart)) { if (isset($r['month']) && $r['month'] >= 1 && $r['month'] <= 12) { $monthly_sales_data[$r['month']] = $r['total_sales']; } } mysqli_free_result($result_chart); } mysqli_stmt_close($stmt_chart); } $chart_labels = ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12']; $chart_data_values = array_values($monthly_sales_data); }
else { /* Xem tháng cụ thể */ $chart_title = "Số sản phẩm bán theo ngày trong Tháng " . $selected_month . "/" . $selected_year; $x_axis_title = "Ngày trong tháng " . $selected_month . "/" . $selected_year; $days_in_month = cal_days_in_month(CAL_GREGORIAN, $selected_month, $selected_year); $daily_sales_data = array_fill(1, $days_in_month, 0); $sql_chart = "SELECT DAY(dh.ThoiDiemDatHang) as day, SUM(dhc.SoLuong) as total_sales FROM donhangchitiet dhc JOIN donhang dh ON dhc.idDH = dh.idDH WHERE YEAR(dh.ThoiDiemDatHang) = ? AND MONTH(dh.ThoiDiemDatHang) = ? GROUP BY DAY(dh.ThoiDiemDatHang) ORDER BY day ASC"; $stmt_chart = mysqli_prepare($conn, $sql_chart); if ($stmt_chart) { mysqli_stmt_bind_param($stmt_chart, "ii", $selected_year, $selected_month); mysqli_stmt_execute($stmt_chart); $result_chart = mysqli_stmt_get_result($stmt_chart); if ($result_chart) { while ($r = mysqli_fetch_assoc($result_chart)) { if (isset($r['day']) && $r['day'] >= 1 && $r['day'] <= $days_in_month) { $daily_sales_data[$r['day']] = $r['total_sales']; } } mysqli_free_result($result_chart); } mysqli_stmt_close($stmt_chart); } $chart_labels = range(1, $days_in_month); $chart_data_values = array_values($daily_sales_data); }
$chart_labels_json = json_encode($chart_labels); $chart_data_json = json_encode($chart_data_values);

// --- Xử lý dữ liệu TOP SẢN PHẨM ---
$top_products_data = []; $top_products_title = "Top 3 sản phẩm bán chạy nhất ";
$sql_top_products = "SELECT sp.TenSP, SUM(dhc.SoLuong) as total_sold FROM donhangchitiet dhc JOIN sanpham sp ON dhc.idSP = sp.idSP JOIN donhang dh ON dhc.idDH = dh.idDH WHERE YEAR(dh.ThoiDiemDatHang) = ?";
$stmt_top = null; $params_top = [$selected_year]; $types_top = "i";
if ($selected_month == 0) { $sql_top_products .= " GROUP BY sp.idSP, sp.TenSP ORDER BY total_sold DESC LIMIT 3"; $top_products_title .= "năm " . $selected_year; $stmt_top = mysqli_prepare($conn, $sql_top_products); if ($stmt_top) { mysqli_stmt_bind_param($stmt_top, $types_top, ...$params_top); } }
else { $sql_top_products .= " AND MONTH(dh.ThoiDiemDatHang) = ? GROUP BY sp.idSP, sp.TenSP ORDER BY total_sold DESC LIMIT 3"; $top_products_title .= "tháng " . $selected_month . "/" . $selected_year; $params_top[] = $selected_month; $types_top .= "i"; $stmt_top = mysqli_prepare($conn, $sql_top_products); if ($stmt_top) { mysqli_stmt_bind_param($stmt_top, $types_top, ...$params_top); } }
if ($stmt_top) { mysqli_stmt_execute($stmt_top); $rs_top_products = mysqli_stmt_get_result($stmt_top); if ($rs_top_products) { while ($r = mysqli_fetch_assoc($rs_top_products)) { $top_products_data[] = $r; } mysqli_free_result($rs_top_products); } mysqli_stmt_close($stmt_top); }
else { error_log("Lỗi khi chuẩn bị câu lệnh sql_top_products: " . mysqli_error($conn)); }

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); // Quan trọng: Đảm bảo file này nhúng Bootstrap CSS ?>
    <title>Báo Cáo Thống Kê</title>
    <?php include_once('header2.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* --- CSS --- */
        body { background-color: #f8f9fa; }
        .stat-card { border: none; border-radius: 0.5rem; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); transition: transform 0.2s ease-in-out; background-color: #ffffff; height: 100%; }
        .stat-card:hover { transform: translateY(-5px); }
        /* Bỏ margin mặc định của card khi dùng trong row với gutter */
        .row.g-4 > .col > .stat-card { margin-bottom: 0 !important; }
        .stat-card .card-body { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1.5rem; min-height: 150px; }
        .stat-card .stat-icon { font-size: 2.5rem; margin-bottom: 0.5rem; color: #0d6efd; }
        .stat-card .stat-icon.icon-product { color: #198754; }
        .stat-card .stat-icon.icon-order { color: #ffc107; }
        .stat-card .stat-icon.icon-sales { color: #fd7e14; }
        .stat-card .card-title { font-size: 0.9rem; font-weight: 500; color: #6c757d; text-transform: uppercase; margin-bottom: 0.25rem; text-align: center; }
        .stat-card .stat-number { font-size: 1.8rem; font-weight: 700; color: #212529; text-align: center; word-break: break-all; }
        .chart-container, .table-container { background-color: #ffffff; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); margin-bottom: 1.5rem; position: relative; }
        .chart-container { height: 400px; width: 100%; }
        #monthlySalesChart { display: block; box-sizing: border-box; height: 100% !important; width: 100% !important; }
        .section-title { text-align: center; margin-bottom: 1.5rem; font-weight: 600; color: #495057; }
        .table thead th { background-color: #e9ecef; text-align: center; vertical-align: middle; font-weight: 600; }
        .table tbody td { text-align: center; vertical-align: middle; }
        .table tbody td.product-name { text-align: left; }
        .main-title { color: #343a40; margin-bottom: 1rem; font-weight: 700; text-align: center; }
        body > .container { padding-top: 20px; }
        .filter-form { background-color: #fff; padding: 1rem 1.5rem; border-radius: 0.5rem; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); margin-bottom: 2rem; display: flex; align-items: center; justify-content: center; gap: 1rem; flex-wrap: wrap; }
        .filter-form label { font-weight: 500; margin-bottom: 0; }
        .filter-form select, .filter-form button { padding: 0.375rem 0.75rem; border: 1px solid #ced4da; border-radius: 0.25rem; }
        .filter-form button { background-color: #0d6efd; color: white; cursor: pointer; border-color: #0d6efd; transition: background-color 0.2s ease; }
        .filter-form button:hover { background-color: #0b5ed7; }
        .total-revenue-display { text-align: right; font-size: 1.15rem; margin-top: 1.5rem; padding-top: 0.75rem; border-top: 1px solid #dee2e6; }
        .total-revenue-display .revenue-amount { font-weight: bold; color: #dc3545; margin-left: 8px; }
    </style>
</head>

<body>
    <?php include_once('header3.php'); ?>

    <div class="container mt-4">
        <h1 class="main-title">BÁO CÁO THỐNG KÊ</h1>

        <form method="GET" action="" class="filter-form">
             <label for="selected_year">Năm:</label>
            <select name="selected_year" id="selected_year">
                <?php foreach ($years_with_orders as $year): ?> <option value="<?php echo $year; ?>" <?php echo ($year == $selected_year) ? 'selected' : ''; ?>><?php echo $year; ?></option> <?php endforeach; ?>
            </select>
            <label for="selected_month">Tháng:</label>
            <select name="selected_month" id="selected_month">
                <option value="0" <?php echo ($selected_month == 0) ? 'selected' : ''; ?>>-- Cả năm --</option>
                <?php for ($m = 1; $m <= 12; $m++): ?> <option value="<?php echo $m; ?>" <?php echo ($m == $selected_month) ? 'selected' : ''; ?>>Tháng <?php echo $m; ?></option> <?php endfor; ?>
            </select>
            <button type="submit">Xem thống kê</button>
        </form>

        <div class="row g-4 mb-4"> <div class="col-lg-3 col-md-6">
                 <div class="card stat-card h-100">
                     <div class="card-body">
                         <div class="stat-icon"><i class="fas fa-users"></i></div>
                         <h5 class="card-title">Tổng số tài khoản</h5>
                         <p class="stat-number"><?php echo number_format($total_accounts); ?></p>
                     </div>
                 </div>
            </div>
            <div class="col-lg-3 col-md-6">
                 <div class="card stat-card h-100">
                     <div class="card-body">
                         <div class="stat-icon icon-product"><i class="fas fa-box-open"></i></div>
                         <h5 class="card-title">Tổng số sản phẩm</h5>
                         <p class="stat-number"><?php echo number_format($total_products); ?></p>
                     </div>
                 </div>
            </div>
            <div class="col-lg-3 col-md-6">
                  <div class="card stat-card h-100">
                      <div class="card-body">
                          <div class="stat-icon icon-order"><i class="fas fa-shopping-cart"></i></div>
                          <h5 class="card-title">Tổng số đơn hàng</h5>
                          <p class="stat-number"><?php echo number_format($total_orders); ?></p>
                      </div>
                  </div>
            </div>
            <div class="col-lg-3 col-md-6">
                  <div class="card stat-card h-100">
                      <div class="card-body">
                          <div class="stat-icon icon-sales"><i class="fas fa-chart-line"></i></div>
                          <h5 class="card-title">SP đã bán<br><?php echo ($selected_month == 0) ? "(Năm $selected_year)" : "(Tháng $selected_month/$selected_year)"; ?></h5>
                          <p class="stat-number"><?php echo number_format($total_sold_in_period); ?></p>
                      </div>
                  </div>
            </div>
        </div> <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="chart-container">
                    <h4 class="section-title"><?php echo htmlspecialchars($chart_title); ?></h4>
                    <canvas id="monthlySalesChart"></canvas>
                </div>
            </div>
            <div class="col-lg-5 mb-4">
                <div class="table-container">
                    <h4 class="section-title"><?php echo htmlspecialchars($top_products_title); ?></h4>
                    <div class="table-responsive">
                         <table class="table table-bordered table-hover">
                            <thead> <tr> <th style="width: 15%;">STT</th> <th>Tên sản phẩm</th> <th style="width: 30%;">Đã bán</th> </tr> </thead>
                            <tbody>
                                <?php if (!empty($top_products_data)) { $stt = 0; foreach ($top_products_data as $r) { ?> <tr> <td><?php echo ++$stt; ?></td> <td class="product-name"><?php echo htmlspecialchars($r['TenSP']); ?></td> <td><?php echo number_format($r['total_sold']); ?></td> </tr> <?php } } else { echo '<tr><td colspan="3">Không có dữ liệu.</td></tr>'; } ?>
                            </tbody>
                        </table>
                    </div> <div class="total-revenue-display">
                        <strong>Tổng doanh thu <?php echo ($selected_month == 0) ? "(Năm $selected_year)" : "(Tháng $selected_month/$selected_year)"; ?>:</strong>
                        <span class="revenue-amount">
                            <?php echo number_format($total_revenue_in_period, 0, ',', '.') . ' đ'; ?>
                        </span>
                    </div>
                     </div> </div> </div> </div> <script>
        // Javascript không đổi
        const chartLabels = <?php echo $chart_labels_json; ?>;
        const chartDataValues = <?php echo $chart_data_json; ?>;
        const ctx = document.getElementById('monthlySalesChart').getContext('2d');
        let currentChart = Chart.getChart(ctx); if (currentChart) { currentChart.destroy(); }
        const monthlyChart = new Chart(ctx, { type: 'bar', data: { labels: chartLabels, datasets: [{ label: 'Số sản phẩm bán được', data: chartDataValues, backgroundColor: 'rgba(54, 162, 235, 0.6)', borderColor: 'rgba(54, 162, 235, 1)', borderWidth: 1, borderRadius: 5, hoverBackgroundColor: 'rgba(54, 162, 235, 0.8)', }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(context) { let label = context.dataset.label || ''; if (label) { label += ': '; } if (context.parsed.y !== null) { label += context.parsed.y.toLocaleString('vi-VN') + ' sản phẩm'; } return label; } } } }, scales: { y: { beginAtZero: true, title: { display: true, text: '<?php echo addslashes($y_axis_title); ?>' } }, x: { title: { display: true, text: '<?php echo addslashes($x_axis_title); ?>' } } } } });
    </script>

    <?php
        // Đóng kết nối ở cuối file nếu cần thiết
         if ($conn) { mysqli_close($conn); }
        include_once('footer.php'); // Footer trang
    ?>
</body>
</html>