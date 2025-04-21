<?php
// --- PHẦN PHP ĐỂ LẤY DỮ LIỆU (Giữ nguyên) ---
include_once ('../connection/connect_database.php');

if (!$conn) { error_log("Lỗi kết nối CSDL: " . mysqli_connect_error()); die("Không thể kết nối đến cơ sở dữ liệu."); }

$current_year = date("Y"); $current_month = date("m");
$selected_year = intval($_GET['selected_year'] ?? $current_year); $selected_month = intval($_GET['selected_month'] ?? $current_month);

$years_with_orders = [];
$sl_years = "SELECT DISTINCT YEAR(ThoiDiemDatHang) as order_year FROM donhang ORDER BY order_year DESC"; $rs_years = mysqli_query($conn, $sl_years); if ($rs_years) { while ($row = mysqli_fetch_assoc($rs_years)) { $years_with_orders[] = $row['order_year']; } mysqli_free_result($rs_years); } else { error_log("Lỗi lấy năm: " . mysqli_error($conn)); } if (empty($years_with_orders)) { $years_with_orders[] = $current_year; } if (!in_array($selected_year, $years_with_orders)) { if (empty($years_with_orders)) $selected_year = $current_year; else $selected_year = reset($years_with_orders); }

// (Code tính toán $total_..., $chart_labels_json, $..._chart_data_json, $top_products_data giữ nguyên như phiên bản trước)
$total_accounts = 0; $sl_accounts = "SELECT COUNT(*) as total_accounts FROM users"; $rs_accounts = mysqli_query($conn, $sl_accounts); if ($rs_accounts) { $total_accounts = mysqli_fetch_assoc($rs_accounts)['total_accounts'] ?? 0; mysqli_free_result($rs_accounts); } else { error_log("Lỗi đếm tài khoản: " . mysqli_error($conn)); }
$total_products = 0; $sl_products = "SELECT COUNT(*) as total_products FROM sanpham"; $rs_products = mysqli_query($conn, $sl_products); if ($rs_products) { $total_products = mysqli_fetch_assoc($rs_products)['total_products'] ?? 0; mysqli_free_result($rs_products); } else { error_log("Lỗi đếm sản phẩm: " . mysqli_error($conn)); }
$total_orders = 0; $sl_orders = "SELECT COUNT(*) as total_orders FROM donhang"; $rs_orders = mysqli_query($conn, $sl_orders); if ($rs_orders) { $total_orders = mysqli_fetch_assoc($rs_orders)['total_orders'] ?? 0; mysqli_free_result($rs_orders); } else { error_log("Lỗi đếm đơn hàng: " . mysqli_error($conn)); }
$total_sold_in_period = 0; $sql_total_sold = "SELECT SUM(dhc.SoLuong) as total_sold FROM donhangchitiet dhc JOIN donhang dh ON dhc.idDH = dh.idDH WHERE YEAR(dh.ThoiDiemDatHang) = ?"; $stmt_total_sold = null; $params_total_sold = [$selected_year]; $types_total_sold = "i"; if ($selected_month != 0) { $sql_total_sold .= " AND MONTH(dh.ThoiDiemDatHang) = ?"; $params_total_sold[] = $selected_month; $types_total_sold .= "i"; } $stmt_total_sold = mysqli_prepare($conn, $sql_total_sold); if ($stmt_total_sold) { mysqli_stmt_bind_param($stmt_total_sold, $types_total_sold, ...$params_total_sold); mysqli_stmt_execute($stmt_total_sold); $result_total_sold = mysqli_stmt_get_result($stmt_total_sold); if ($result_total_sold) { $row_total = mysqli_fetch_assoc($result_total_sold); $total_sold_in_period = $row_total['total_sold'] ?? 0; mysqli_free_result($result_total_sold); } else { error_log("Lỗi lấy total_sold: " . mysqli_stmt_error($stmt_total_sold));} mysqli_stmt_close($stmt_total_sold); } else { error_log("Lỗi chuẩn bị sql_total_sold: " . mysqli_error($conn)); }
$total_revenue_in_period = 0; $sql_total_revenue = "SELECT SUM(dhc.SoLuong * sp.GiaBan) as total_revenue FROM donhangchitiet dhc JOIN donhang dh ON dhc.idDH = dh.idDH JOIN sanpham sp ON dhc.idSP = sp.idSP WHERE YEAR(dh.ThoiDiemDatHang) = ?"; $stmt_total_revenue = null; $params_total_revenue = [$selected_year]; $types_total_revenue = "i"; if ($selected_month != 0) { $sql_total_revenue .= " AND MONTH(dh.ThoiDiemDatHang) = ?"; $params_total_revenue[] = $selected_month; $types_total_revenue .= "i"; } $stmt_total_revenue = mysqli_prepare($conn, $sql_total_revenue); if ($stmt_total_revenue) { mysqli_stmt_bind_param($stmt_total_revenue, $types_total_revenue, ...$params_total_revenue); mysqli_stmt_execute($stmt_total_revenue); $result_total_revenue = mysqli_stmt_get_result($stmt_total_revenue); if ($result_total_revenue) { $row_revenue = mysqli_fetch_assoc($result_total_revenue); $total_revenue_in_period = $row_revenue['total_revenue'] ?? 0; mysqli_free_result($result_total_revenue); } else { error_log("Lỗi lấy total_revenue: " . mysqli_stmt_error($stmt_total_revenue));} mysqli_stmt_close($stmt_total_revenue); } else { error_log("Lỗi chuẩn bị sql_total_revenue: " . mysqli_error($conn)); }
$total_received_in_period = 0; $sql_total_received = "SELECT SUM(SoLuong) as total_received FROM phieunhap WHERE YEAR(NgayNhap) = ?"; $stmt_total_received = null; $params_total_received = [$selected_year]; $types_total_received = "i"; if ($selected_month != 0) { $sql_total_received .= " AND MONTH(NgayNhap) = ?"; $params_total_received[] = $selected_month; $types_total_received .= "i"; } $stmt_total_received = mysqli_prepare($conn, $sql_total_received); if ($stmt_total_received) { mysqli_stmt_bind_param($stmt_total_received, $types_total_received, ...$params_total_received); mysqli_stmt_execute($stmt_total_received); $result_total_received = mysqli_stmt_get_result($stmt_total_received); if ($result_total_received) { $row_received = mysqli_fetch_assoc($result_total_received); $total_received_in_period = $row_received['total_received'] ?? 0; mysqli_free_result($result_total_received); } else { error_log("Lỗi lấy total_received: " . mysqli_stmt_error($stmt_total_received));} mysqli_stmt_close($stmt_total_received); } else { error_log("Lỗi chuẩn bị sql_total_received: " . mysqli_error($conn)); }
$total_issued_in_period = 0; $sql_total_issued = "SELECT SUM(SoLuong) as total_issued FROM phieuxuat WHERE YEAR(NgayXuat) = ?"; $stmt_total_issued = null; $params_total_issued = [$selected_year]; $types_total_issued = "i"; if ($selected_month != 0) { $sql_total_issued .= " AND MONTH(NgayXuat) = ?"; $params_total_issued[] = $selected_month; $types_total_issued .= "i"; } $stmt_total_issued = mysqli_prepare($conn, $sql_total_issued); if ($stmt_total_issued) { mysqli_stmt_bind_param($stmt_total_issued, $types_total_issued, ...$params_total_issued); mysqli_stmt_execute($stmt_total_issued); $result_total_issued = mysqli_stmt_get_result($stmt_total_issued); if ($result_total_issued) { $row_issued = mysqli_fetch_assoc($result_total_issued); $total_issued_in_period = $row_issued['total_issued'] ?? 0; mysqli_free_result($result_total_issued); } else { error_log("Lỗi lấy total_issued: " . mysqli_stmt_error($stmt_total_issued));} mysqli_stmt_close($stmt_total_issued); } else { error_log("Lỗi chuẩn bị sql_total_issued: " . mysqli_error($conn)); }

$chart_labels = []; $sold_chart_data = []; $revenue_chart_data = []; $received_chart_data = []; $issued_chart_data = []; $chart_title_suffix = "năm " . $selected_year; $x_axis_title = "";
if ($selected_month == 0) { $x_axis_title = "Tháng trong năm " . $selected_year; $monthly_data_template = array_fill(1, 12, 0); $sold_chart_data = $monthly_data_template; $revenue_chart_data = $monthly_data_template; $received_chart_data = $monthly_data_template; $issued_chart_data = $monthly_data_template; $sql_sold_month = "SELECT MONTH(dh.ThoiDiemDatHang) as month, SUM(dhc.SoLuong) as total_value FROM donhangchitiet dhc JOIN donhang dh ON dhc.idDH = dh.idDH WHERE YEAR(dh.ThoiDiemDatHang) = ? GROUP BY month ORDER BY month ASC"; $stmt_sold_month = mysqli_prepare($conn, $sql_sold_month); if ($stmt_sold_month) { mysqli_stmt_bind_param($stmt_sold_month, "i", $selected_year); mysqli_stmt_execute($stmt_sold_month); $res = mysqli_stmt_get_result($stmt_sold_month); if ($res) { while ($r = mysqli_fetch_assoc($res)) { if (isset($r['month'])) $sold_chart_data[$r['month']] = $r['total_value'] ?? 0; } mysqli_free_result($res); } mysqli_stmt_close($stmt_sold_month); } $sql_revenue_month = "SELECT MONTH(dh.ThoiDiemDatHang) as month, SUM(dhc.SoLuong * sp.GiaBan) as total_value FROM donhangchitiet dhc JOIN donhang dh ON dhc.idDH = dh.idDH JOIN sanpham sp ON dhc.idSP = sp.idSP WHERE YEAR(dh.ThoiDiemDatHang) = ? GROUP BY month ORDER BY month ASC"; $stmt_revenue_month = mysqli_prepare($conn, $sql_revenue_month); if ($stmt_revenue_month) { mysqli_stmt_bind_param($stmt_revenue_month, "i", $selected_year); mysqli_stmt_execute($stmt_revenue_month); $res = mysqli_stmt_get_result($stmt_revenue_month); if ($res) { while ($r = mysqli_fetch_assoc($res)) { if (isset($r['month'])) $revenue_chart_data[$r['month']] = $r['total_value'] ?? 0; } mysqli_free_result($res); } mysqli_stmt_close($stmt_revenue_month); } $sql_received_month = "SELECT MONTH(NgayNhap) as month, SUM(SoLuong) as total_value FROM phieunhap WHERE YEAR(NgayNhap) = ? GROUP BY month ORDER BY month ASC"; $stmt_received_month = mysqli_prepare($conn, $sql_received_month); if ($stmt_received_month) { mysqli_stmt_bind_param($stmt_received_month, "i", $selected_year); mysqli_stmt_execute($stmt_received_month); $res = mysqli_stmt_get_result($stmt_received_month); if ($res) { while ($r = mysqli_fetch_assoc($res)) { if (isset($r['month'])) $received_chart_data[$r['month']] = $r['total_value'] ?? 0; } mysqli_free_result($res); } mysqli_stmt_close($stmt_received_month); } $sql_issued_month = "SELECT MONTH(NgayXuat) as month, SUM(SoLuong) as total_value FROM phieuxuat WHERE YEAR(NgayXuat) = ? GROUP BY month ORDER BY month ASC"; $stmt_issued_month = mysqli_prepare($conn, $sql_issued_month); if ($stmt_issued_month) { mysqli_stmt_bind_param($stmt_issued_month, "i", $selected_year); mysqli_stmt_execute($stmt_issued_month); $res = mysqli_stmt_get_result($stmt_issued_month); if ($res) { while ($r = mysqli_fetch_assoc($res)) { if (isset($r['month'])) $issued_chart_data[$r['month']] = $r['total_value'] ?? 0; } mysqli_free_result($res); } mysqli_stmt_close($stmt_issued_month); } $chart_labels = ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];
} else { $chart_title_suffix = "tháng " . $selected_month . "/" . $selected_year; $x_axis_title = "Ngày trong tháng " . $selected_month . "/" . $selected_year; $days_in_month = cal_days_in_month(CAL_GREGORIAN, $selected_month, $selected_year); $daily_data_template = array_fill(1, $days_in_month, 0); $sold_chart_data = $daily_data_template; $revenue_chart_data = $daily_data_template; $received_chart_data = $daily_data_template; $issued_chart_data = $daily_data_template; $sql_sold_day = "SELECT DAY(dh.ThoiDiemDatHang) as day, SUM(dhc.SoLuong) as total_value FROM donhangchitiet dhc JOIN donhang dh ON dhc.idDH = dh.idDH WHERE YEAR(dh.ThoiDiemDatHang) = ? AND MONTH(dh.ThoiDiemDatHang) = ? GROUP BY day ORDER BY day ASC"; $stmt_sold_day = mysqli_prepare($conn, $sql_sold_day); if ($stmt_sold_day) { mysqli_stmt_bind_param($stmt_sold_day, "ii", $selected_year, $selected_month); mysqli_stmt_execute($stmt_sold_day); $res = mysqli_stmt_get_result($stmt_sold_day); if ($res) { while ($r = mysqli_fetch_assoc($res)) { if (isset($r['day'])) $sold_chart_data[$r['day']] = $r['total_value'] ?? 0; } mysqli_free_result($res); } mysqli_stmt_close($stmt_sold_day); } $sql_revenue_day = "SELECT DAY(dh.ThoiDiemDatHang) as day, SUM(dhc.SoLuong * sp.GiaBan) as total_value FROM donhangchitiet dhc JOIN donhang dh ON dhc.idDH = dh.idDH JOIN sanpham sp ON dhc.idSP = sp.idSP WHERE YEAR(dh.ThoiDiemDatHang) = ? AND MONTH(dh.ThoiDiemDatHang) = ? GROUP BY day ORDER BY day ASC"; $stmt_revenue_day = mysqli_prepare($conn, $sql_revenue_day); if ($stmt_revenue_day) { mysqli_stmt_bind_param($stmt_revenue_day, "ii", $selected_year, $selected_month); mysqli_stmt_execute($stmt_revenue_day); $res = mysqli_stmt_get_result($stmt_revenue_day); if ($res) { while ($r = mysqli_fetch_assoc($res)) { if (isset($r['day'])) $revenue_chart_data[$r['day']] = $r['total_value'] ?? 0; } mysqli_free_result($res); } mysqli_stmt_close($stmt_revenue_day); } $sql_received_day = "SELECT DAY(NgayNhap) as day, SUM(SoLuong) as total_value FROM phieunhap WHERE YEAR(NgayNhap) = ? AND MONTH(NgayNhap) = ? GROUP BY day ORDER BY day ASC"; $stmt_received_day = mysqli_prepare($conn, $sql_received_day); if ($stmt_received_day) { mysqli_stmt_bind_param($stmt_received_day, "ii", $selected_year, $selected_month); mysqli_stmt_execute($stmt_received_day); $res = mysqli_stmt_get_result($stmt_received_day); if ($res) { while ($r = mysqli_fetch_assoc($res)) { if (isset($r['day'])) $received_chart_data[$r['day']] = $r['total_value'] ?? 0; } mysqli_free_result($res); } mysqli_stmt_close($stmt_received_day); } $sql_issued_day = "SELECT DAY(NgayXuat) as day, SUM(SoLuong) as total_value FROM phieuxuat WHERE YEAR(NgayXuat) = ? AND MONTH(NgayXuat) = ? GROUP BY day ORDER BY day ASC"; $stmt_issued_day = mysqli_prepare($conn, $sql_issued_day); if ($stmt_issued_day) { mysqli_stmt_bind_param($stmt_issued_day, "ii", $selected_year, $selected_month); mysqli_stmt_execute($stmt_issued_day); $res = mysqli_stmt_get_result($stmt_issued_day); if ($res) { while ($r = mysqli_fetch_assoc($res)) { if (isset($r['day'])) $issued_chart_data[$r['day']] = $r['total_value'] ?? 0; } mysqli_free_result($res); } mysqli_stmt_close($stmt_issued_day); } $chart_labels = range(1, $days_in_month); }

$chart_labels_json = json_encode($chart_labels);
$sold_chart_data_json = json_encode(array_values($sold_chart_data));
$revenue_chart_data_json = json_encode(array_values($revenue_chart_data));
$received_chart_data_json = json_encode(array_values($received_chart_data));
$issued_chart_data_json = json_encode(array_values($issued_chart_data));

$top_products_data = []; $top_products_title = "Top 3 sản phẩm bán chạy nhất " . $chart_title_suffix; $sql_top_products = "SELECT sp.TenSP, SUM(dhc.SoLuong) as total_sold FROM donhangchitiet dhc JOIN sanpham sp ON dhc.idSP = sp.idSP JOIN donhang dh ON dhc.idDH = dh.idDH WHERE YEAR(dh.ThoiDiemDatHang) = ?"; $stmt_top = null; $params_top = [$selected_year]; $types_top = "i"; if ($selected_month == 0) { $sql_top_products .= " GROUP BY sp.idSP, sp.TenSP ORDER BY total_sold DESC LIMIT 3"; $stmt_top = mysqli_prepare($conn, $sql_top_products); if ($stmt_top) { mysqli_stmt_bind_param($stmt_top, $types_top, ...$params_top); } } else { $sql_top_products .= " AND MONTH(dh.ThoiDiemDatHang) = ? GROUP BY sp.idSP, sp.TenSP ORDER BY total_sold DESC LIMIT 3"; $params_top[] = $selected_month; $types_top .= "i"; $stmt_top = mysqli_prepare($conn, $sql_top_products); if ($stmt_top) { mysqli_stmt_bind_param($stmt_top, $types_top, ...$params_top); } } if ($stmt_top) { mysqli_stmt_execute($stmt_top); $rs_top_products = mysqli_stmt_get_result($stmt_top); if ($rs_top_products) { while ($r = mysqli_fetch_assoc($rs_top_products)) { $top_products_data[] = $r; } mysqli_free_result($rs_top_products); } else { error_log("Lỗi lấy top products: " . mysqli_stmt_error($stmt_top)); } mysqli_stmt_close($stmt_top); } else { error_log("Lỗi chuẩn bị sql_top_products: " . mysqli_error($conn)); }

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Báo Cáo Thống Kê</title>
    <?php include_once('header2.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* --- CSS (Giữ nguyên hoặc tùy chỉnh thêm) --- */
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .stat-card { border: none; border-radius: 0.75rem; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.07); transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out; background-color: #ffffff; height: 100%; margin-bottom: 1rem; border-top: 4px solid transparent; }
        .stat-card:hover { transform: translateY(-6px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1); }
        .stat-card.border-top-users { border-top-color: #0d6efd; } .stat-card.border-top-product { border-top-color: #198754; } .stat-card.border-top-order { border-top-color: #ffc107; } .stat-card.border-top-sales { border-top-color: #fd7e14; } .stat-card.border-top-received { border-top-color: #20c997; } .stat-card.border-top-issued { border-top-color: #6f42c1; }
        @media (min-width: 992px) { .row.g-4 > .col-lg-2 > .stat-card { margin-bottom: 0 !important; } }
        @media (min-width: 768px) and (max-width: 991.98px) { .row.g-4 > .col-md-4 > .stat-card { margin-bottom: 1rem; } }
        .stat-card .card-body { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1rem; min-height: 150px; }
        .stat-card .stat-icon { font-size: 2.2rem; margin-bottom: 0.75rem; }
        .stat-card.border-top-users .stat-icon { color: #0d6efd; } .stat-card.border-top-product .stat-icon { color: #198754; } .stat-card.border-top-order .stat-icon { color: #ffc107; } .stat-card.border-top-sales .stat-icon { color: #fd7e14; } .stat-card.border-top-received .stat-icon { color: #20c997; } .stat-card.border-top-issued .stat-icon { color: #6f42c1; }
        .stat-card .card-title { font-size: 0.8rem; font-weight: 600; color: #6c757d; text-transform: uppercase; margin-bottom: 0.25rem; text-align: center; }
        .stat-card .stat-number { font-size: 1.6rem; font-weight: 700; color: #343a40; text-align: center; word-break: break-all; }
        .chart-container, .table-container { background-color: #ffffff; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.07); margin-bottom: 1.5rem; position: relative; width: 100%;}
        .chart-container { min-height: 350px; }
        .chart-canvas { display: block; box-sizing: border-box; height: 300px !important; width: 100% !important; }
        /* CSS cho bảng chi tiết */
        .daily-data-table-container { max-height: 200px; overflow-y: auto; margin-top: 1rem; border-top: 1px solid #dee2e6; padding-top: 0.5rem; }
        .daily-data-table-container table thead th { position: sticky; top: 0; z-index: 1; }
        .daily-data-table-container table td { font-size: 0.87rem; color: #212529 !important; /* Màu chữ đen rõ */ }
        .daily-data-table-container table tbody tr:nth-child(odd) td { background-color: rgba(0, 0, 0, 0.02); }
        /* CSS cho bảng Top Sản phẩm */
        .table-container .table thead.table-primary th { background-color:rgb(255, 205, 233); color:rgb(97, 28, 28); border-color:rgb(240, 187, 227); font-weight: 600; }
        .table-container .table td.product-name { color:rgb(117, 12, 75); }

        .section-title { text-align: center; margin-bottom: 1.5rem; font-weight: 700; color: #495057; font-size: 1.25rem; border-bottom: 1px solid #dee2e6; padding-bottom: 0.75rem; }
        .table thead th { text-align: center; vertical-align: middle; white-space: nowrap; font-size: 0.9rem; border-bottom-width: 1px; }
        .table tbody td { text-align: center; vertical-align: middle; font-size: 0.9rem; }
        .table tbody tr:hover { background-color: #eef1f5; }
        .table tbody td.product-name { text-align: left; font-weight: 500; }
        .main-title { color: #2c3e50; margin-bottom: 1.5rem; font-weight: 700; text-align: center; }
        body > .container { padding-top: 30px; padding-bottom: 50px; }
        .filter-form { background-color: #fff; padding: 1rem 1.5rem; border-radius: 0.5rem; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); margin-bottom: 2.5rem; display: flex; align-items: center; justify-content: center; gap: 1rem; flex-wrap: wrap; border: 1px solid #e3e6eb; }
        .filter-form label { font-weight: 500; margin-bottom: 0; white-space: nowrap; }
        .filter-form select, .filter-form button { padding: 0.45rem 0.85rem; border: 1px solid #ced4da; border-radius: 0.3rem; }
        .filter-form button { background-color: #0d6efd; color: white; cursor: pointer; border-color: #0d6efd; transition: background-color 0.2s ease, box-shadow 0.2s ease; }
        .filter-form button:hover { background-color: #0b5ed7; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .total-revenue-display { text-align: right; font-size: 1.2rem; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #dee2e6; }
        .total-revenue-display strong { color: #343a40; }
        .total-revenue-display .revenue-amount { font-weight: bold; color: #c82333; margin-left: 8px; }
    </style>
</head>

<body>
    <?php include_once('header3.php'); ?>

    <div class="container mt-4">
        <h1 class="main-title">BÁO CÁO THỐNG KÊ</h1>

        <form method="GET" action="" class="filter-form">
            <label for="selected_year">Năm:</label> <select name="selected_year" id="selected_year"> <?php foreach ($years_with_orders as $year): ?> <option value="<?php echo $year; ?>" <?php echo ($year == $selected_year) ? 'selected' : ''; ?>><?php echo $year; ?></option> <?php endforeach; ?> </select>
            <label for="selected_month">Tháng:</label> <select name="selected_month" id="selected_month"> <option value="0" <?php echo ($selected_month == 0) ? 'selected' : ''; ?>>-- Cả năm --</option> <?php for ($m = 1; $m <= 12; $m++): ?> <option value="<?php echo $m; ?>" <?php echo ($m == $selected_month) ? 'selected' : ''; ?>>Tháng <?php echo $m; ?></option> <?php endfor; ?> </select>
            <button type="submit"><i class="fas fa-filter me-1"></i> Xem</button>
        </form>

        <div class="row g-4 mb-4">
            <div class="col-lg-2 col-md-4 col-6"> <div class="card stat-card h-100 border-top-users"> <div class="card-body"> <div class="stat-icon icon-users"><i class="fas fa-users"></i></div> <h5 class="card-title">Tài khoản</h5> <p class="stat-number"><?php echo number_format($total_accounts); ?></p> </div> </div> </div>
            <div class="col-lg-2 col-md-4 col-6"> <div class="card stat-card h-100 border-top-product"> <div class="card-body"> <div class="stat-icon icon-product"><i class="fas fa-box-open"></i></div> <h5 class="card-title">Sản phẩm</h5> <p class="stat-number"><?php echo number_format($total_products); ?></p> </div> </div> </div>
            <div class="col-lg-2 col-md-4 col-6"> <div class="card stat-card h-100 border-top-order"> <div class="card-body"> <div class="stat-icon icon-order"><i class="fas fa-shopping-cart"></i></div> <h5 class="card-title">Đơn hàng</h5> <p class="stat-number"><?php echo number_format($total_orders); ?></p> </div> </div> </div>
            <div class="col-lg-2 col-md-4 col-6"> <div class="card stat-card h-100 border-top-sales"> <div class="card-body"> <div class="stat-icon icon-sales"><i class="fas fa-chart-line"></i></div> <h5 class="card-title">SP Đã Bán</h5> <p class="stat-number"><?php echo number_format($total_sold_in_period); ?></p> </div> </div> </div>
            <div class="col-lg-2 col-md-4 col-6"> <div class="card stat-card h-100 border-top-received"> <div class="card-body"> <div class="stat-icon icon-received"><i class="fas fa-dolly-flatbed"></i></div> <h5 class="card-title">SP Đã Nhập</h5> <p class="stat-number"><?php echo number_format($total_received_in_period); ?></p> </div> </div> </div>
            <div class="col-lg-2 col-md-4 col-6"> <div class="card stat-card h-100 border-top-issued"> <div class="card-body"> <div class="stat-icon icon-issued"><i class="fas fa-truck-loading"></i></div> <h5 class="card-title">SP Đã Xuất</h5> <p class="stat-number"><?php echo number_format($total_issued_in_period); ?></p> </div> </div> </div>
        </div> <div class="row">
            <div class="col-lg-6 mb-4">
                 <div class="chart-container">
                     <h4 class="section-title">Doanh thu theo thời gian (<?php echo $chart_title_suffix; ?>)</h4>
                     <canvas id="revenueChart" class="chart-canvas"></canvas>
                     <?php if ($selected_month != 0): ?>
                     <div class="daily-data-table-container">
                         <table class="table table-sm table-striped table-bordered small">
                             <thead class="table-light sticky-top"> <tr><th>Ngày</th><th>Doanh thu (đ)</th></tr> </thead>
                             <tbody> <?php for ($i = 0; $i < count($chart_labels); $i++): ?> <tr> <td class="text-center"><?php echo $chart_labels[$i]; ?></td> <td class="text-end"><?php echo number_format($revenue_chart_data[$i] ?? 0, 0, ',', '.'); ?></td> </tr> <?php endfor; ?> </tbody>
                         </table>
                     </div> <?php endif; ?>
                 </div>
            </div>
            <div class="col-lg-6 mb-4">
                 <div class="chart-container">
                     <h4 class="section-title">Số lượng bán theo thời gian (<?php echo $chart_title_suffix; ?>)</h4>
                     <canvas id="soldChart" class="chart-canvas"></canvas>
                      <?php if ($selected_month != 0): ?>
                      <div class="daily-data-table-container">
                         <table class="table table-sm table-striped table-bordered small">
                             <thead class="table-light sticky-top"> <tr><th>Ngày</th><th>SL Bán</th></tr> </thead>
                             <tbody> <?php for ($i = 0; $i < count($chart_labels); $i++): ?> <tr> <td class="text-center"><?php echo $chart_labels[$i]; ?></td> <td class="text-end"><?php echo number_format($sold_chart_data[$i] ?? 0); ?></td> </tr> <?php endfor; ?> </tbody>
                         </table>
                      </div> <?php endif; ?>
                 </div>
            </div>
             <div class="col-lg-6 mb-4">
                 <div class="chart-container">
                     <h4 class="section-title">Số lượng nhập theo thời gian (<?php echo $chart_title_suffix; ?>)</h4>
                     <canvas id="receivedChart" class="chart-canvas"></canvas>
                      <?php if ($selected_month != 0): ?>
                      <div class="daily-data-table-container">
                         <table class="table table-sm table-striped table-bordered small">
                             <thead class="table-light sticky-top"> <tr><th>Ngày</th><th>SL Nhập</th></tr> </thead>
                             <tbody> <?php for ($i = 0; $i < count($chart_labels); $i++): ?> <tr> <td class="text-center"><?php echo $chart_labels[$i]; ?></td> <td class="text-end"><?php echo number_format($received_chart_data[$i] ?? 0); ?></td> </tr> <?php endfor; ?> </tbody>
                         </table>
                      </div> <?php endif; ?>
                 </div>
             </div>
              <div class="col-lg-6 mb-4">
                 <div class="chart-container">
                     <h4 class="section-title">Số lượng xuất theo thời gian (<?php echo $chart_title_suffix; ?>)</h4>
                     <canvas id="issuedChart" class="chart-canvas"></canvas>
                      <?php if ($selected_month != 0): ?>
                      <div class="daily-data-table-container">
                         <table class="table table-sm table-striped table-bordered small">
                             <thead class="table-light sticky-top"> <tr><th>Ngày</th><th>SL Xuất</th></tr> </thead>
                             <tbody> <?php for ($i = 0; $i < count($chart_labels); $i++): ?> <tr> <td class="text-center"><?php echo $chart_labels[$i]; ?></td> <td class="text-end"><?php echo number_format($issued_chart_data[$i] ?? 0); ?></td> </tr> <?php endfor; ?> </tbody>
                         </table>
                      </div> <?php endif; ?>
                 </div>
             </div>
        </div><div class="row">
             <div class="col-12 mb-4">
                 <div class="table-container">
                     <h4 class="section-title"><?php echo htmlspecialchars($top_products_title); ?></h4>
                     <div class="table-responsive">
                         <table class="table table-bordered table-hover table-sm">
                             <thead class="table-primary"> <tr> <th style="width: 10%;">STT</th> <th>Tên sản phẩm</th> <th style="width: 25%;">Đã bán</th> </tr>
                             </thead>
                             <tbody>
                                 <?php if (!empty($top_products_data)) { $stt = 0; foreach ($top_products_data as $r) { ?> <tr> <td><?php echo ++$stt; ?></td> <td class="product-name"><?php echo htmlspecialchars($r['TenSP']); ?></td> <td><?php echo number_format($r['total_sold']); ?></td> </tr> <?php } } else { echo '<tr><td colspan="3" class="text-center">Không có dữ liệu.</td></tr>'; } ?>
                             </tbody>
                         </table>
                     </div>
                      <div class="total-revenue-display">
                         <strong>Tổng doanh thu <?php echo $chart_title_suffix; ?>:</strong>
                         <span class="revenue-amount">
                             <?php echo number_format($total_revenue_in_period, 0, ',', '.') . ' đ'; ?>
                         </span>
                     </div>
                 </div> </div> </div> </div> <script>
        // --- Dữ liệu từ PHP ---
        const chartLabels = <?php echo $chart_labels_json; ?>;
        const soldData = <?php echo $sold_chart_data_json; ?>;
        const revenueData = <?php echo $revenue_chart_data_json; ?>;
        const receivedData = <?php echo $received_chart_data_json; ?>;
        const issuedData = <?php echo $issued_chart_data_json; ?>;
        const xAxisTitle = '<?php echo addslashes($x_axis_title); ?>';

        // --- Hàm tạo biểu đồ ---
        function createChart(canvasId, chartType, chartData, label, yAxisTitle, baseColorRGB, pointBackgroundColor = null) {
             const canvasElement = document.getElementById(canvasId);
             if (!canvasElement) { console.error("Canvas element not found:", canvasId); return; }
             const ctx = canvasElement.getContext('2d');
             let currentChart = Chart.getChart(ctx); if (currentChart) { currentChart.destroy(); }

             // Xác định màu sắc dựa trên baseColorRGB
             const solidBorderColor = `rgba(${baseColorRGB}, 1)`;
             const barFillColor = `rgba(${baseColorRGB}, 0.8)`; // Màu nền Bar đậm hơn (alpha 0.8)
             const lineFillColor = `rgba(${baseColorRGB}, 0.2)`; // Màu nền Line nhạt hơn (alpha 0.2)

             const datasetOptions = {
                 label: label,
                 data: chartData,
                 borderColor: solidBorderColor,
                 borderWidth: 1.5, // Độ dày viền mặc định
                 hoverBackgroundColor: solidBorderColor // Màu hover đậm
             };

             // Tùy chỉnh options dựa trên loại biểu đồ
             if (chartType === 'line') {
                 datasetOptions.fill = true;
                 datasetOptions.backgroundColor = lineFillColor; // Nền nhạt
                 datasetOptions.tension = 0.3;
                 datasetOptions.pointBackgroundColor = pointBackgroundColor || solidBorderColor;
                 datasetOptions.pointBorderColor = '#fff';
                 datasetOptions.pointHoverRadius = 7;
                 datasetOptions.pointRadius = 5;
                 datasetOptions.borderWidth = 2; // Line dày hơn Bar
             } else if (chartType === 'bar') {
                 datasetOptions.backgroundColor = barFillColor; // Nền đậm hơn
                 datasetOptions.borderRadius = 5;
                 datasetOptions.barPercentage = 0.7;
                 datasetOptions.categoryPercentage = 0.8;
             }

             new Chart(ctx, {
                 type: chartType,
                 data: { labels: chartLabels, datasets: [datasetOptions] },
                 options: {
                     responsive: true, maintainAspectRatio: false,
                     plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(context) { let label = context.dataset.label || ''; if (label) { label += ': '; } if (context.parsed.y !== null) { if (canvasId === 'revenueChart') { label += context.parsed.y.toLocaleString('vi-VN') + ' đ'; } else { label += context.parsed.y.toLocaleString('vi-VN'); } } return label; } } } },
                     scales: { y: { beginAtZero: true, title: { display: true, text: yAxisTitle }, grid: { color: '#e9ecef' } }, x: { title: { display: true, text: xAxisTitle }, grid: { display: false } } }
                 }
             });
         }

        // --- Vẽ các biểu đồ với màu sắc được yêu cầu và điều chỉnh độ đậm ---
        document.addEventListener('DOMContentLoaded', function() {
             // Doanh thu: Line Chart (Teal) - Viền đậm, nền nhạt hơn (0.2 alpha), line dày hơn
             createChart('revenueChart', 'line', revenueData, 'Doanh thu', 'Doanh thu (VNĐ)', '75, 192, 192');
             // Số lượng bán: Bar Chart (Blue) - Nền đậm hơn (0.8 alpha)
             createChart('soldChart', 'bar', soldData, 'Số lượng bán', 'Số lượng sản phẩm bán', '54, 162, 235');
             // Số lượng nhập: Bar Chart (Yellow) - Nền đậm hơn (0.8 alpha)
             createChart('receivedChart', 'bar', receivedData, 'Số lượng nhập', 'Số lượng sản phẩm nhập', '255, 206, 86');
             // Số lượng xuất: Line Chart (Red/Pink) - Viền đậm, nền nhạt hơn (0.2 alpha), line dày hơn
             createChart('issuedChart', 'line', issuedData, 'Số lượng xuất', 'Số lượng sản phẩm xuất', '255, 99, 132');
        });
    </script>

    <?php
        // Đóng kết nối
        if (isset($conn) && $conn) { mysqli_close($conn); }
        include_once('footer.php');
    ?>
</body>
</html>