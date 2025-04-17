<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
        // Include các file header meta/title/css cơ bản
        include_once("header.php"); // Giả định chứa các thẻ meta cơ bản
    ?>
    <title>Tư Vấn Sản Phẩm Theo Tình Trạng Da</title>
    <?php
        // Include các file header chứa CSS, link...
        include_once("header1.php"); // Giả định chứa các link CSS chung, font...

        // Link Bootstrap CSS (nếu chưa có trong header1.php)
        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">';
        // --- Link Bootstrap Icons ---
        echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">';

        // --- Cần đảm bảo Bootstrap CSS đã được load trước thẻ <style> này ---
    ?>

    <style>
        /* --- CSS CHO KHUNG BAO --- */
        body {
             background-color: #f0f0f0; /* Màu nền xám nhạt cho bên ngoài khung */
             padding-top: 20px; /* Khoảng cách trên cùng */
             padding-bottom: 20px; /* Khoảng cách dưới cùng */
             /* Tăng font-size cơ bản cho toàn trang nếu muốn */
             /* font-size: 16px; */
        }
        .page-frame {
            background-color: #fff0f5; /* Màu hồng nhạt (LavenderBlush) */
            padding: 20px; /* Khoảng cách bên trong khung */
            border-radius: 8px; /* Bo tròn góc */
            max-width: 1200px; /* Giới hạn chiều rộng tối đa của khung */
            margin: 0 auto; /* Căn giữa khung */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Đổ bóng nhẹ */
        }
        /* --- KẾT THÚC CSS KHUNG BAO --- */

        /* Style riêng cho form tư vấn */
        .form-wrapper {
            background-color: #ffffff; /* Nền trắng cho form bên trong khung hồng*/
            padding: 30px 40px;
            border-radius: 8px;
            max-width: 800px;
            margin: 30px auto; /* Thêm margin trên dưới cho form */
            border: 1px solid #dee2e6; /* Thêm viền nhẹ cho form */
        }
        .form-title-custom {
             color: #A75D8B; /* Màu tím hồng đậm giống tiêu đề trang checkout */
             font-weight: bold;
             margin-bottom: 20px; /* Giảm margin bottom */
             text-align: center;
             font-size: 1.7rem; /* Tăng nhẹ cỡ chữ */
             text-transform: uppercase; /* IN HOA */
        }
        .form-label {
            margin-bottom: 0.6rem; /* Tăng nhẹ khoảng cách dưới */
            font-weight: 600;
            display: block;
            font-size: 1.1rem; /* <<< TĂNG SIZE CÂU HỎI CHÍNH */
        }
        .required-star {
            color: red;
        }
        .helper-text {
            font-size: 0.95em; /* <<< TĂNG SIZE CHỮ HƯỚNG DẪN */
            color: #6c757d;
             margin-top: 0.35rem; /* Tăng nhẹ khoảng cách trên */
             display: block;
             margin-bottom: 0.85rem; /* Tăng nhẹ khoảng cách dưới */
        }
        .form-control, .form-select {
            border-color: #ced4da;
            border-radius: 4px;
            font-size: 1rem; /* <<< Đặt size chữ cho ô input/select */
        }
         /* Tăng chiều cao cho select box phù hợp */
         .form-select-sm {
            padding-top: 0.4rem;
            padding-bottom: 0.4rem;
         }
         textarea.form-control {
            line-height: 1.5; /* Đảm bảo khoảng cách dòng trong textarea */
         }

        .form-control:focus, .form-select:focus {
            border-color: #a75d8b;
            box-shadow: 0 0 0 0.25rem rgba(167, 93, 139, 0.25);
        }

        /* Style cho radio và checkbox */
        .form-check {
            margin-bottom: 0.8rem; /* Tăng nhẹ khoảng cách */
            padding-left: 1.8em; /* Tăng nhẹ padding để tránh chữ đè input */
        }
         .form-check .form-check-input {
             float: left;
             margin-left: -1.8em; /* Đồng bộ với padding-left */
             margin-top: 0.2em; /* Căn chỉnh input với dòng chữ */
         }

        .form-check-label {
            font-weight: normal;
             padding-left: 0.3em; /* Tăng nhẹ khoảng cách */
             font-size: 1rem; /* <<< TĂNG SIZE CHỮ LỰA CHỌN */
             line-height: 1.4; /* Đảm bảo khoảng cách dòng */
        }
        .form-check-input:checked {
            background-color: #A75D8B;
            border-color: #A75D8B;
        }


        /* Nút bấm */
        .button-group {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
        .btn-custom { /* Style chung cho nút nếu muốn tùy chỉnh thêm */
            padding: 10px 25px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 1.05rem; /* <<< TĂNG NHẸ SIZE NÚT */
            border: 1px solid transparent;
            transition: all 0.2s ease-in-out;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-custom-suggest { /* Class riêng cho nút gợi ý */
            background-color: #A75D8B; /* Màu tím hồng */
            color: white;
            border-color: #A75D8B;
        }
        .btn-custom-suggest:hover {
            background-color: #8e4d74; /* Tím hồng đậm hơn khi hover */
            border-color: #8e4d74;
            color: white;
        }
        /* Style cho nút Quay về trang chủ */
        .btn-outline-secondary.btn-custom {
            color: #6c757d;
            border-color: #6c757d;
        }
         .btn-outline-secondary.btn-custom:hover {
            background-color: #6c757d;
            color: white;
         }

    </style>
</head>
<body>
    <div class="page-frame">

        <?php
            // Include header chính (logo, menu...)
            include_once("header2.php");
        ?>

        <div class="container mt-4 mb-5"> <div class="form-wrapper shadow-sm">
                <h3 class="form-title-custom">TÌM SẢN PHẨM PHÙ HỢP VỚI LÀN DA CỦA BẠN</h3>
                <p class="text-center text-muted mb-4">Hãy cung cấp một số thông tin về làn da của bạn để chúng tôi có thể gợi ý những sản phẩm tốt nhất!</p>

                <form method="post" action="product_suggestions.php" name="SkinConsultForm" id="skinConsultForm">

                    <div class="mb-4">
                        <label class="form-label">1. Loại da của bạn là gì? <span class="required-star">*</span></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="skin_type" id="skinTypeOily" value="Da dầu" required>
                            <label class="form-check-label" for="skinTypeOily">Da dầu (Thường xuyên bóng nhờn, lỗ chân lông to)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="skin_type" id="skinTypeDry" value="Da khô" required>
                            <label class="form-check-label" for="skinTypeDry">Da khô (Cảm giác khô căng, dễ bong tróc)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="skin_type" id="skinTypeCombination" value="Da hỗn hợp" required>
                            <label class="form-check-label" for="skinTypeCombination">Da hỗn hợp (Dầu vùng chữ T, khô hoặc thường ở má)</label>
                        </div>
                         <div class="form-check">
                            <input class="form-check-input" type="radio" name="skin_type" id="skinTypeNormal" value="Da thường" required>
                            <label class="form-check-label" for="skinTypeNormal">Da thường (Cân bằng, ít vấn đề)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="skin_type" id="skinTypeSensitive" value="Da nhạy cảm" required>
                            <label class="form-check-label" for="skinTypeSensitive">Da nhạy cảm (Dễ bị kích ứng, mẩn đỏ)</label>
                        </div>
                         <small class="helper-text">Chọn loại da mô tả đúng nhất tình trạng da bạn.</small>
                    </div>

                     <div class="mb-4">
                         <label class="form-label">2. Vấn đề da bạn quan tâm nhất? (Có thể chọn nhiều)</label>
                         <div class="row">
                              <div class="col-md-6">
                                  <div class="form-check">
                                      <input class="form-check-input" type="checkbox" name="concerns[]" value="Mụn" id="concernAcne">
                                      <label class="form-check-label" for="concernAcne">Mụn (mụn ẩn, mụn viêm, đầu đen...)</label>
                                  </div>
                                  <div class="form-check">
                                      <input class="form-check-input" type="checkbox" name="concerns[]" value="Lỗ chân lông to" id="concernPores">
                                      <label class="form-check-label" for="concernPores">Lỗ chân lông to</label>
                                  </div>
                                   <div class="form-check">
                                      <input class="form-check-input" type="checkbox" name="concerns[]" value="Thâm mụn" id="concernPIH">
                                      <label class="form-check-label" for="concernPIH">Vết thâm sau mụn</label>
                                  </div>
                                  <div class="form-check">
                                      <input class="form-check-input" type="checkbox" name="concerns[]" value="Da không đều màu" id="concernUnevenTone">
                                      <label class="form-check-label" for="concernUnevenTone">Da không đều màu, xỉn màu</label>
                                  </div>
                                  <div class="form-check">
                                      <input class="form-check-input" type="checkbox" name="concerns[]" value="Nám, tàn nhang" id="concernPigmentation">
                                      <label class="form-check-label" for="concernPigmentation">Nám, tàn nhang, đốm nâu</label>
                                  </div>
                                  <div class="form-check">
                                      <input class="form-check-input" type="checkbox" name="concerns[]" value="Lão hóa" id="concernAging">
                                      <label class="form-check-label" for="concernAging">Lão hóa (nếp nhăn, kém săn chắc)</label>
                                  </div>
                                  <div class="form-check">
                                      <input class="form-check-input" type="checkbox" name="concerns[]" value="Da khô, thiếu ẩm" id="concernDehydrated">
                                      <label class="form-check-label" for="concernDehydrated">Da khô, thiếu ẩm</label>
                                  </div>
                              </div>
                               <div class="col-md-6">
                                   <div class="form-check">
                                      <input class="form-check-input" type="checkbox" name="concerns[]" value="Da nhạy cảm, mẩn đỏ" id="concernRedness">
                                      <label class="form-check-label" for="concernRedness">Da nhạy cảm, dễ mẩn đỏ</label>
                                  </div>
                                  <div class="form-check">
                                      <input class="form-check-input" type="checkbox" name="concerns[]" value="Làm sạch da" id="concernCleansing">
                                      <label class="form-check-label" for="concernCleansing">Làm sạch da (loại bỏ bụi bẩn, dầu thừa)</label>
                                  </div>
                                  <div class="form-check">
                                     <input class="form-check-input" type="checkbox" name="concerns[]" value="Kiềm dầu" id="concernOilControl">
                                     <label class="form-check-label" for="concernOilControl">Kiềm dầu, giảm bóng nhờn</label>
                                  </div>
                                  <div class="form-check">
                                      <input class="form-check-input" type="checkbox" name="concerns[]" value="Làm sáng da" id="concernBrightening">
                                      <label class="form-check-label" for="concernBrightening">Làm sáng da, mờ thâm sạm</label>
                                  </div>
                                  <div class="form-check">
                                     <input class="form-check-input" type="checkbox" name="concerns[]" value="Dưỡng ẩm" id="concernMoisturizing">
                                     <label class="form-check-label" for="concernMoisturizing">Cấp ẩm / Dưỡng ẩm</label>
                                  </div>
                                  <div class="form-check">
                                      <input class="form-check-input" type="checkbox" name="concerns[]" value="Chống nắng" id="concernSunProtection">
                                      <label class="form-check-label" for="concernSunProtection">Bảo vệ da khỏi ánh nắng mặt trời</label>
                                  </div>
                               </div>
                         </div>
                         <small class="helper-text">Hãy chọn những vấn đề bạn muốn cải thiện nhất.</small>
                     </div>
                    <div class="mb-4">
                        <label for="ageRange" class="form-label">3. Độ tuổi của bạn?</label>
                        <select class="form-select form-select-sm" id="ageRange" name="age_range">
                            <option value="" selected>-- Không bắt buộc --</option>
                            <option value="Dưới 18">Dưới 18</option>
                            <option value="18-24">18 - 24</option>
                            <option value="25-34">25 - 34</option>
                            <option value="35-44">35 - 44</option>
                            <option value="45-54">45 - 54</option>
                            <option value="Trên 55">Trên 55</option>
                        </select>
                         <small class="helper-text">Thông tin này giúp gợi ý sản phẩm phù hợp hơn với độ tuổi (không bắt buộc).</small>
                    </div>

                    <div class="mb-4">
                        <label for="allergies" class="form-label">4. Bạn có dị ứng hoặc muốn tránh thành phần nào không?</label>
                        <textarea class="form-control" id="allergies" name="allergies" rows="3" placeholder="Ví dụ: Cồn khô, hương liệu, paraben, BHA/AHA (nếu da quá nhạy cảm)..."></textarea>
                        <small class="helper-text">Liệt kê các thành phần bạn biết mình bị dị ứng hoặc không muốn sử dụng.</small>
                    </div>

                    <div class="button-group">
                         <button type="submit" class="btn btn-custom btn-custom-suggest">
                             <i class="bi bi-lightbulb"></i> Xem Gợi Ý Sản Phẩm
                         </button>

                         <a href="../index.php?index=1" class="btn btn-outline-secondary ms-2 btn-custom"> <i class="bi bi-house-door"></i> Trở Về Trang Chủ
                         </a>
                    </div>

                </form>
            </div></div> <?php
            // Include footer
            include_once("footer.php");
        ?>

    </div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php
        // Include các file JS khác nếu cần
        // Ví dụ: include_once('scripts.php');
    ?>
</body>
</html>