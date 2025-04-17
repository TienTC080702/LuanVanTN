<?php
// Kết nối database
$conn = mysqli_connect("localhost", "root", "", "CuaHangMyPham");
if (!$conn) {
    die("Kết nối database thất bại: " . mysqli_connect_error());
}

// Lấy từ khóa tìm kiếm nếu có
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Truy vấn sản phẩm
if ($search) {
    $sl_sanpham = "SELECT * FROM sanpham WHERE AnHien=1 AND TenSP LIKE '%$search%' ORDER BY idSP DESC";
    $sl_count = "SELECT COUNT(idSP) as total FROM sanpham WHERE AnHien=1 AND TenSP LIKE '%$search%'";
} else {
    $sl_sanpham = "SELECT * FROM sanpham WHERE AnHien=1 ORDER BY idSP DESC";
    $sl_count = "SELECT COUNT(idSP) as total FROM sanpham WHERE AnHien=1";
}

// Lấy tổng số sản phẩm
$result_count = mysqli_query($conn, $sl_count);
$row = mysqli_fetch_assoc($result_count);
$total_records = $row['total'] ?? 0;

$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 11;
$total_page = ($total_records > 0) ? ceil($total_records / $limit) : 1;

if ($current_page > $total_page) {
    $current_page = $total_page;
} else if ($current_page < 1) {
    $current_page = 1;
}

$start = ($current_page - 1) * $limit;

// Truy vấn sản phẩm có phân trang
$rs_sanpham = mysqli_query($conn, $sl_sanpham . " LIMIT $start, $limit");

?>

<!-- Hiển thị danh sách sản phẩm -->
<div class="row text-center" style="margin-top:40px">
    <div id="productlist">
        <?php
        if (mysqli_num_rows($rs_sanpham) > 0) {
            while ($r = $rs_sanpham->fetch_assoc()) {
                if ($r['idSP'] == 1) continue;
        ?>
                <div class="col-md-3 col-sm-6 col-xs-6" style="margin-bottom:10px">
                    <div class="item">
                        <div class="prod-box">
                            <span class="prod-block">
                                <a href="MoTa.php?idSP=<?php echo $r['idSP']; ?>" class="hover-item"></a>
                                <span class="prod-image-block">
                                    <span class="prod-image-box">
                                        <img class="prod-image" src="../images/<?php echo $r['urlHinh']; ?>" alt="">
                                    </span>
                                </span>
                                <span class="productname"><?php echo $r['TenSP']; ?></span>
                                <span class="category">
                                    <span class="pricein168"><?php echo number_format($r['GiaBan'], 0); ?> VNĐ</span>
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
        <?php 
            }
        } else {
            echo "<p style='text-align:center; font-size:18px; color:red;'>Sản phẩm không tồn tại</p>";
        } 
        ?>
    </div>
</div>

<!-- Hiển thị phân trang -->
<div class="example">
    <div class="row" align="center">
        <div class="pagination">
            <?php
            echo "<ul class=\"pagination\">";
            if ($current_page > 1 && $total_page > 1) {
                echo '<li><a href="index.php?page=' . ($current_page - 1) . '">Prev</a> </li>';
            }
            for ($i = 1; $i <= $total_page; $i++) {
                if ($i == $current_page) {
                    echo '<li><span style="background-color: #00aced;">' . $i . '</span> </li>';
                } else {
                    echo '<li><a href="index.php?page=' . $i . '">' . $i . '</a> </li>';
                }
            }
            if ($current_page < $total_page && $total_page > 1) {
                echo '<li><a href="index.php?page=' . ($current_page + 1) . '">Next</a> </li>';
            }
            echo "</ul>";
            ?>
        </div>
    </div>
</div>

<?php
// Đóng kết nối database
mysqli_close($conn);
?>
