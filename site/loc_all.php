<?php
// !!! CẢNH BÁO: SQL INJECTION trong biến $search !!!
// Nên dùng mysqli_real_escape_string cho mọi biến đưa vào query
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Query đếm tổng số sản phẩm
$result_count_query = "SELECT count(idSP) AS total FROM sanpham WHERE AnHien=1 AND TenSP LIKE '%$search%'";
$result_count = mysqli_query($conn, $result_count_query);

if (!$result_count) {
     error_log("Query failed for product count: " . mysqli_error($conn));
     echo "<p>Lỗi khi đếm số lượng sản phẩm.</p>";
     $total_records = 0;
} else {
    $row = mysqli_fetch_assoc($result_count);
    $total_records = $row['total'] ?? 0;
    mysqli_free_result($result_count);
}

// Tính toán phân trang
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 11; // Số sản phẩm mỗi trang (Bạn có thể thay đổi số này)
$total_page = ($limit > 0 && $total_records > 0) ? ceil($total_records / $limit) : 1;

// Đảm bảo $current_page hợp lệ
$current_page = max(1, min($current_page, $total_page)); // Đảm bảo luôn >= 1 và <= total_page

$start = ($current_page - 1) * $limit;

// Query lấy sản phẩm cho trang hiện tại
// !!! CẢNH BÁO: SQL INJECTION & NÊN DÙNG PREPARED STATEMENTS !!!
$result_query = "SELECT * FROM sanpham WHERE AnHien=1 AND TenSP LIKE '%$search%' ORDER BY idSP DESC LIMIT $start, $limit";
$result = mysqli_query($conn, $result_query);

?>

<div class="row text-center" style="margin-top:40px">
    <div id="productlist">
        <?php
        // Kiểm tra query lấy sản phẩm thành công và có sản phẩm nào không
        if ($result && mysqli_num_rows($result) > 0) {
            while ($r = $result->fetch_assoc()) {
                // Bỏ qua sản phẩm có idSP = 1 (Logic gốc)
                if ($r['idSP'] == 1) continue;
        ?>
                <div class="col-md-3 col-sm-6 col-xs-6" style="margin-bottom:10px">
                    <div class="item">
                        <div class="prod-box">
                            <span class="prod-block">
                                <a href="MoTa.php?idSP=<?php echo $r['idSP']; ?>" class="hover-item"></a>
                                <span class="prod-image-block">
                                    <span class="prod-image-box">
                                        <img class="prod-image img-responsive"
                                             src="../images/<?php echo htmlspecialchars($r['urlHinh'] ?? 'no_image_available.png'); // Thêm ảnh mặc định nếu urlHinh rỗng ?>"
                                             alt="<?php echo htmlspecialchars($r['TenSP']); ?>">
                                    </span>
                                </span>
                                <span class="productname dislay-block limit limit-product" title="<?php echo htmlspecialchars($r['TenSP']); ?>">
                                    <?php echo htmlspecialchars($r['TenSP']); ?>
                                </span>

                                <span class="category dislay-block ">
                                    <span class="pricein168 dislay-block limit">
                                        <?php
                                        // KIỂM TRA VÀ HIỂN THỊ GIÁ KHUYẾN MÃI
                                        // *** Sử dụng tên cột chính xác 'GiaKhuyenmai' (m thường) ***
                                        if (isset($r['GiaKhuyenmai']) && is_numeric($r['GiaKhuyenmai']) && $r['GiaKhuyenmai'] > 0 && $r['GiaKhuyenmai'] < $r['GiaBan']) {
                                            // Có giá khuyến mãi
                                            echo '<span class="discount-price" style="color: red; font-weight: bold; margin-right: 10px;">';
                                            echo number_format($r['GiaKhuyenmai'], 0) . ' VNĐ'; // Sử dụng GiaKhuyenmai
                                            echo '</span>';
                                            echo '<strike class="original-price" style="color: #999;">';
                                            echo number_format($r['GiaBan'], 0) . ' VNĐ';
                                            echo '</strike>';
                                        } else {
                                            // Không có giá khuyến mãi
                                            echo '<span class="money" style="font-weight: bold;">';
                                            echo number_format($r['GiaBan'], 0) . ' VNĐ';
                                            echo '</span>';
                                        }
                                        ?>
                                    </span>
                                </span>
                                </span>
                             <a href="GioHang.php?action=add&idSP=<?php echo $r['idSP']; ?>" class="addcartbtn" title="Thêm vào giỏ">
                               <img src="../images/xe.png" alt="Thêm vào giỏ">
                            </a>
                             <a href="MoTa.php?idSP=<?php echo $r['idSP']; ?>" class="btn btn-default buyproduct"><strong>Xem chi tiết</strong></a>
                        </div>
                    </div>
                </div>
        <?php
            } // end while
            mysqli_free_result($result); // Giải phóng kết quả sản phẩm
        } else {
            // Hiển thị thông báo nếu không có sản phẩm nào
             if (!empty($search)) {
                 echo "<div class='col-xs-12'><p>Không tìm thấy sản phẩm nào khớp với từ khóa: '<strong>".htmlspecialchars($search)."</strong>'.</p></div>";
             } else {
                  echo "<div class='col-xs-12'><p>Chưa có sản phẩm nào trong danh mục này.</p></div>";
             }
        }
        ?>
    </div>
</div>

<div class="example">
    <div class="row text-center">
        <div class="pagination-container">
            <?php
            if ($total_page > 1) {
                echo "<ul class=\"pagination\">";
                // Nút Previous
                if ($current_page > 1) {
                    echo '<li><a href="index.php?index=1&page=' . ($current_page - 1) . ($search ? '&search='.urlencode($search) : '') . '">&laquo; Prev</a></li>';
                } else {
                     echo '<li class="disabled"><span>&laquo; Prev</span></li>';
                }
                // Các nút số trang
                $max_pages_to_show = 7;
                $start_page = max(1, $current_page - floor($max_pages_to_show / 2));
                $end_page = min($total_page, $start_page + $max_pages_to_show - 1);
                 if ($end_page - $start_page + 1 < $max_pages_to_show) {
                      $start_page = max(1, $end_page - $max_pages_to_show + 1);
                 }
                 if ($start_page > 1) {
                     echo '<li><a href="index.php?index=1&page=1'.($search ? '&search='.urlencode($search) : '').'">1</a></li>';
                     if ($start_page > 2) { echo '<li class="disabled"><span>...</span></li>'; }
                 }
                for ($i = $start_page; $i <= $end_page; $i++) {
                    if ($i == $current_page) { echo '<li class="active"><span>' . $i . '</span></li>'; }
                    else { echo '<li><a href="index.php?index=1&page=' . $i . ($search ? '&search='.urlencode($search) : '') . '">' . $i . '</a></li>'; }
                }
                 if ($end_page < $total_page) {
                      if ($end_page < $total_page - 1) { echo '<li class="disabled"><span>...</span></li>'; }
                      echo '<li><a href="index.php?index=1&page=' . $total_page . ($search ? '&search='.urlencode($search) : '') . '">' . $total_page . '</a></li>';
                 }
                // Nút Next
                if ($current_page < $total_page) {
                    echo '<li><a href="index.php?index=1&page=' . ($current_page + 1) . ($search ? '&search='.urlencode($search) : '') . '">Next &raquo;</a></li>';
                } else {
                     echo '<li class="disabled"><span>Next &raquo;</span></li>';
                }
                echo "</ul>";
            }
            ?>
        </div>
    </div>
</div>