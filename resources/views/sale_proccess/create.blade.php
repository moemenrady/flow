@extends('layouts.app_page')

@section('title', 'بدء عملية بيع')

@section('content')

    <div class="sale-container">

        <div id="back-to-categories"
            style="display: none; cursor: pointer; margin-bottom: 20px; color: #ff5550; font-weight: bold; font-size: 14px;">
            <i class="fas fa-arrow-right"></i> ⬅️ العودة للأصناف
        </div>

        <div id="pos-display" class="products-list">
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            // ==========================================
            // 1. المتغيرات الأساسية والبيانات
            // ==========================================
            let selectedProducts = [];
            const categoriesData = @json($categories); // جلب الأقسام والمنتجات من السيرفر

            const posDisplay = document.querySelector("#pos-display");
            const backBtn = document.querySelector("#back-to-categories");

            // إعدادات الباركود
            let scanBuffer = "";
            let scanTimer = null;
            const SCAN_TIMEOUT = 120;
            const MIN_LENGTH = 4;

            function safeAudio(src) {
                const audio = new Audio(src);
                audio.onerror = () => console.warn("Audio not found:", src);
                return audio;
            }

            const sounds = {
                // success: safeAudio('مسار_صوت_النجاح.mp3'),
                // error: safeAudio('مسار_صوت_الخطأ.mp3')
            };

            // ==========================================
            // 2. إنشاء وتجهيز الـ Snackbar
            // ==========================================
            const snackbar = document.createElement("div");
            snackbar.id = "selectedProductsSnackbar";
            snackbar.style.position = "fixed";
            snackbar.style.bottom = "20px";
            snackbar.style.right = "20px";
            snackbar.style.background = "#333";
            snackbar.style.color = "#fff";
            snackbar.style.padding = "15px";
            snackbar.style.borderRadius = "12px";
            snackbar.style.boxShadow = "0 4px 12px rgba(0,0,0,0.3)";
            snackbar.style.zIndex = "99999";
            snackbar.style.display = "none";
            snackbar.style.minWidth = "250px";
            document.body.appendChild(snackbar);

            const clearBtn = document.createElement("span");
            clearBtn.textContent = "❌";
            clearBtn.style.cursor = "pointer";
            clearBtn.style.float = "right";
            clearBtn.style.marginBottom = "10px";
            snackbar.appendChild(clearBtn);

            const list = document.createElement("div");
            snackbar.appendChild(list);

            const confirmBtn = document.createElement("button");
            confirmBtn.textContent = "✅ تأكيد المشتريات";
            confirmBtn.style.marginTop = "10px";
            confirmBtn.className = "btn btn-success btn-sm w-100";
            snackbar.appendChild(confirmBtn);

            // ==========================================
            // 3. دوال عرض الأقسام والمنتجات
            // ==========================================
            function renderCategories() {
                backBtn.style.display = "none";
                posDisplay.innerHTML = "";

                categoriesData.forEach(cat => {
                    const btn = document.createElement("button");
                    btn.className = "product-item category-item";
                    btn.innerHTML = `📁 ${cat.name}`;
                    btn.onclick = (e) => {
                        e.preventDefault();
                        renderProducts(cat.id);
                    };
                    posDisplay.appendChild(btn);
                });
            }

            function renderProducts(categoryId) {
                const category = categoriesData.find(c => c.id == categoryId);
                if (!category) return;

                backBtn.style.display = "block";
                posDisplay.innerHTML = "";

                if (category.products.length === 0) {
                    posDisplay.innerHTML =
                        "<p style='color:#888; width:100%; text-align:center;'>لا توجد منتجات في هذا الصنف</p>";
                    return;
                }

                category.products.forEach(prod => {
                    const btn = document.createElement("button");
                    btn.className = "product-item";
                    btn.dataset.id = prod.id;
                    btn.innerHTML = prod.name;
                    btn.onclick = (e) => {
                        e.preventDefault();
                        addProductToSnackbar(prod.id, prod.name);
                    };
                    posDisplay.appendChild(btn);
                });
            }

            backBtn.addEventListener("click", renderCategories);

            // ==========================================
            // 4. تحديث الـ Snackbar والتعامل مع المشتريات
            // ==========================================
            clearBtn.addEventListener("click", () => {
                selectedProducts = [];
                updateSnackbarUI();
            });

            function updateSnackbarUI() {
                list.innerHTML = "";
                if (selectedProducts.length === 0) {
                    snackbar.style.display = "none";
                    return;
                }

                selectedProducts.forEach(p => {
                    const div = document.createElement("div");
                    div.style.display = "flex";
                    div.style.justifyContent = "space-between";
                    div.style.alignItems = "center";
                    div.style.marginBottom = "5px";

                    const nameSpan = document.createElement("span");
                    nameSpan.textContent = `${p.name} × ${p.qty}`; // الاعتماد على الاسم المحفوظ

                    const minusBtn = document.createElement("button");
                    minusBtn.textContent = "➖";
                    minusBtn.className = "btn btn-sm btn-warning";
                    minusBtn.style.marginLeft = "10px";
                    minusBtn.style.padding = "2px 8px";

                    minusBtn.addEventListener("click", () => {
                        if (p.qty > 1) {
                            p.qty -= 1;
                        } else {
                            selectedProducts = selectedProducts.filter(item => item.product_id !== p
                                .product_id);
                        }
                        updateSnackbarUI();
                    });

                    div.appendChild(nameSpan);
                    div.appendChild(minusBtn);
                    list.appendChild(div);
                });

                snackbar.style.display = "block";
            }

            function addProductToSnackbar(productId, productName = null) {
                // البحث عن الاسم إذا لم يتم تمريره (في حالة الباركود)
                if (!productName) {
                    for (let cat of categoriesData) {
                        let prod = cat.products.find(x => x.id == productId);
                        if (prod) {
                            productName = prod.name;
                            break;
                        }
                    }
                }

                const existing = selectedProducts.find(p => p.product_id === productId);
                if (existing) {
                    existing.qty += 1;
                } else {
                    selectedProducts.push({
                        product_id: productId,
                        name: productName || `منتج #${productId}`,
                        qty: 1
                    });
                }
                updateSnackbarUI();
            }

            confirmBtn.addEventListener("click", () => {
                if (selectedProducts.length === 0) return;

                const items = encodeURIComponent(JSON.stringify(selectedProducts.map(p => ({
                    id: p.product_id,
                    qty: p.qty
                }))));
                const url = "{{ route('invoice.create') }}" + "?items=" + items;
                window.location.href = url;

                selectedProducts = [];
                updateSnackbarUI();
            });

            // ==========================================
            // 5. قراءة المسح بالباركود
            // ==========================================
            document.addEventListener("keydown", (e) => {
                if (["Shift", "Control", "Alt"].includes(e.key)) return;

                if (e.key.length === 1) {
                    scanBuffer += e.key;
                }

                clearTimeout(scanTimer);

                scanTimer = setTimeout(() => {
                    if (scanBuffer.length >= MIN_LENGTH) {
                        handleScan(scanBuffer);
                    }
                    scanBuffer = "";
                }, SCAN_TIMEOUT);
            });

            async function handleScan(code) {
                try {
                    const res = await fetch(`{{ route('products.searchid') }}?query=${code}`, {
                        headers: {
                            "Accept": "application/json"
                        }
                    });
                    const data = await res.json();

                    if (data.type === "single") {
                        addProductToSnackbar(data.product.id, data.product.name);
                        if (sounds.success) sounds.success.play();
                    } else {
                        if (sounds.error) sounds.error.play();
                        showTempSnackbar(data.message || "❌ المنتج غير موجود", "error");
                    }
                } catch (err) {
                    console.error(err);
                    if (sounds.error) sounds.error.play();
                    showTempSnackbar("فشل الاتصال بالسيرفر", "error");
                }
            }

            function showTempSnackbar(message, type = "success") {
                const old = document.querySelector(".snackbar.temp-js");
                if (old) old.remove();

                const el = document.createElement("div");
                el.className = "snackbar temp-js";
                el.style.cssText = `
                    position:fixed; bottom:20px; right:20px;
                    padding:12px 18px; border-radius:8px;
                    color:#fff; font-weight:600; z-index:99999;
                    background:${type === "error" ? "#e74c3c" : "#28a745"};
                `;
                el.innerText = message;
                document.body.appendChild(el);
                setTimeout(() => el.remove(), 3000);
            }

            // تشغيل العرض الأول للأصناف عند تحميل الصفحة
            renderCategories();
        });
    </script>

@endsection

@section('style')
    <style>
        body {
            margin: 0;
            font-family: "Cairo", sans-serif;
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sale-container {
            background: #ffffff;
            padding: 30px;
            border-radius: 20px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.8s ease;
        }

        .products-list {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 14px;
            margin: 20px 0;
        }

        .product-item {
            background: #fff;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            padding: 8px 10px;
            min-width: 100px;
            min-height: 55px;
            font-size: 13px;
            font-weight: 600;
            color: #333;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
            transition: all 0.25s ease;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        /* تنسيق إضافي لتمييز الأقسام قليلاً (اختياري، يمكنك تركه أبيض) */
        .category-item {
            background: #fafafa;
        }

        .product-item:hover {
            transform: translateY(-3px) scale(1.02);
            border-color: #ff8884;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.10);
            color: #ff5550;
        }

        #back-to-categories:hover {
            text-decoration: underline;
        }

        @media (min-width: 992px) {
            .product-item {
                min-width: 120px;
                min-height: 65px;
                font-size: 14px;
            }
        }

        @media (max-width: 576px) {
            .product-item {
                min-width: 48%;
                min-height: 55px;
                font-size: 12px;
                padding: 8px 10px;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection
