@extends('layouts.app_page')

@section('title', 'بدء عملية بيع')

@section('content')


    <div class="sale-container">
        <div class="products-list">
            @foreach ($products as $product)
                <form class="invoiceForm" action="" method="POST">
                    @csrf
                    <input type="hidden" name="items" class="itemsInput">
                    <button type="submit" class="product-item" data-id="{{ $product->id }}">
                        {{ $product->name }}
                    </button>
                </form>
            @endforeach
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let selectedProducts = [];
            const itemsContainer = document.querySelector("#itemsListContainer");
            const invoiceForm = document.querySelector("#itemsForm");
            const hiddenItems = document.querySelector("#hiddenItems");

            // إنشاء Snackbar
            let snackbar = document.createElement("div");
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

            let clearBtn = document.createElement("span");
            clearBtn.textContent = "❌";
            clearBtn.style.cursor = "pointer";
            clearBtn.style.float = "right";
            clearBtn.style.marginBottom = "10px";
            snackbar.appendChild(clearBtn);

            clearBtn.addEventListener("click", () => {
                selectedProducts = [];
                updateSnackbarUI();
            });

            let list = document.createElement("div");
            snackbar.appendChild(list);

            let confirmBtn = document.createElement("button");
            confirmBtn.textContent = "✅ تأكيد المشتريات";
            confirmBtn.style.marginTop = "10px";
            confirmBtn.className = "btn btn-success btn-sm";
            snackbar.appendChild(confirmBtn);

            function updateSnackbarUI() {
                list.innerHTML = "";
                if (selectedProducts.length === 0) {
                    snackbar.style.display = "none";
                    return;
                }

                selectedProducts.forEach(p => {
                    const prodName = document.querySelector(`.product-item[data-id="${p.product_id}"]`)
                        .textContent;
                    const div = document.createElement("div");
                    div.style.display = "flex";
                    div.style.justifyContent = "space-between";
                    div.style.alignItems = "center";
                    div.style.marginBottom = "5px";

                    let nameSpan = document.createElement("span");
                    nameSpan.textContent = `${prodName} × ${p.qty}`;

                    let minusBtn = document.createElement("button");
                    minusBtn.textContent = "➖";
                    minusBtn.className = "btn btn-sm btn-warning";
                    minusBtn.style.marginLeft = "10px";

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

            // التعامل مع أزرار المنتجات
            document.querySelectorAll(".product-item").forEach(btn => {
                btn.addEventListener("click", function(e) {
                    e.preventDefault();
                    const id = parseInt(this.dataset.id);
                    const existing = selectedProducts.find(p => p.product_id === id);
                    if (existing) {
                        existing.qty += 1;
                    } else {
                        selectedProducts.push({
                            product_id: id,
                            qty: 1
                        });
                    }
                    updateSnackbarUI();
                });
            });

            confirmBtn.addEventListener("click", function() {
                if (selectedProducts.length === 0) return;

                // تحويل المنتجات المختارة مباشرة لهيئة JSON
                const items = encodeURIComponent(JSON.stringify(selectedProducts.map(p => ({
                    id: p.product_id,
                    qty: p.qty
                }))));

                // فتح صفحة الفاتورة مباشرة
                const url = "{{ route('invoice.create') }}" + "?items=" + items;
                window.location.href = url;

                // مسح الـ Snackbar
                selectedProducts = [];
                updateSnackbarUI();
            });

        });
    </script>
    <script>
document.addEventListener("DOMContentLoaded", () => {

    let scanBuffer = "";
    let scanTimer = null;
    const SCAN_TIMEOUT = 120; // وقت قليل للتمييز بين الكتابة العادية ومسح الباركود
    const MIN_LENGTH = 4;

    function safeAudio(src) {
        const audio = new Audio(src);
        audio.onerror = () => console.warn("Audio not found:", src);
        return audio;
    }

    const sounds = {
    };

    // -----------------------------------------
    // Snackbar للمنتجات المختارة
    // -----------------------------------------
    let selectedProducts = [];

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
    confirmBtn.className = "btn btn-success btn-sm";
    snackbar.appendChild(confirmBtn);

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
            const prodBtn = document.querySelector(`.product-item[data-id="${p.product_id}"]`);
            const prodName = prodBtn ? prodBtn.textContent : `منتج #${p.product_id}`;
            const div = document.createElement("div");
            div.style.display = "flex";
            div.style.justifyContent = "space-between";
            div.style.alignItems = "center";
            div.style.marginBottom = "5px";

            const nameSpan = document.createElement("span");
            nameSpan.textContent = `${prodName} × ${p.qty}`;

            const minusBtn = document.createElement("button");
            minusBtn.textContent = "➖";
            minusBtn.className = "btn btn-sm btn-warning";
            minusBtn.style.marginLeft = "10px";

            minusBtn.addEventListener("click", () => {
                if (p.qty > 1) {
                    p.qty -= 1;
                } else {
                    selectedProducts = selectedProducts.filter(item => item.product_id !== p.product_id);
                }
                updateSnackbarUI();
            });

            div.appendChild(nameSpan);
            div.appendChild(minusBtn);
            list.appendChild(div);
        });

        snackbar.style.display = "block";
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

    // -----------------------------------------
    // التعامل مع أزرار المنتجات العادية
    // -----------------------------------------
    document.querySelectorAll(".product-item").forEach(btn => {
        btn.addEventListener("click", function(e) {
            e.preventDefault();
            const id = parseInt(this.dataset.id);
            addProductToSnackbar(id);
        });
    });

    // -----------------------------------------
    // قراءة المسح بالكود (Barcode / Keyboard)
    // -----------------------------------------
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
                headers: { "Accept": "application/json" }
            });
            const data = await res.json();

            if (data.type === "single") {
                addProductToSnackbar(data.product.id);
                sounds.success.play();
            } else {
                sounds.error.play();
                showSnackbar(data.message || "❌ المنتج غير موجود", "error");
            }

        } catch (err) {
            console.error(err);
            sounds.error.play();
            showSnackbar("فشل الاتصال بالسيرفر", "error");
        }
    }

    function addProductToSnackbar(productId) {
        const existing = selectedProducts.find(p => p.product_id === productId);
        if (existing) {
            existing.qty += 1;
        } else {
            selectedProducts.push({ product_id: productId, qty: 1 });
        }
        updateSnackbarUI();
    }

    function showSnackbar(message, type = "success") {
        const old = document.querySelector(".snackbar.temp-js");
        if (old) old.remove();

        const el = document.createElement("div");
        el.className = "snackbar temp-js";
        el.style.cssText = `
            position:fixed;
            bottom:20px;
            right:20px;
            padding:12px 18px;
            border-radius:8px;
            color:#fff;
            font-weight:600;
            z-index:99999;
            background:${type === "error" ? "#e74c3c" : "#28a745"};
        `;
        el.innerText = message;

        document.body.appendChild(el);
        setTimeout(() => el.remove(), 3000);
    }

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
            /* 👈 أبيض صريح */
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
            /* مسافة مناسبة بين الكروت */
            margin: 20px 0;
        }

.product-item {
    background: #fff;
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 12px;
    padding: 8px 10px;          /* قلل padding */
    min-width: 100px;           /* قلل العرض الأدنى */
    min-height: 55px;           /* قلل الارتفاع */
    font-size: 13px;            /* صغر الخط */
    font-weight: 600;
    color: #333;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    transition: all 0.25s ease;
    cursor: pointer;

    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

/* Hover effect */
.product-item:hover {
    transform: translateY(-3px) scale(1.02); /* أصغر قليلًا */
    border-color: #ff8884;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.10);
    color: #ff5550;
}

/* شاشات أكبر من 992px */
@media (min-width: 992px) {
    .product-item {
        min-width: 120px;
        min-height: 65px;
        font-size: 14px;
    }
}

/* شاشات صغيرة (موبايل) */
@media (max-width: 576px) {
    .product-item {
        min-width: 48%;
        min-height: 55px;
        font-size: 12px;
        padding: 8px 10px;
    }
}


        @keyframes fadeSlideIn {
            to {
                opacity: 1;
                transform: translateY(0);
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
