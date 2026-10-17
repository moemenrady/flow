<div class="modal fade" id="chooseActionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-modal text-center p-4">

            <h5 class="mb-4 text-primary fw-bold">⚡ اختر العملية</h5>

            <button class="btn action-btn glass-btn mb-3" data-action="product">
                ➕ إضافة منتج
            </button>

            <button class="btn action-btn glass-btn mb-3" data-action="category">
                📂 إضافة صنف
            </button>

            <button class="btn action-btn glass-btn" data-action="quantity">
                📦 إضافة كمية
            </button>

        </div>
    </div>
</div>

<style>
    .glass-modal {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(15px);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        box-shadow: 0 0 25px rgba(0, 0, 0, 0.3);
    }

    .glass-btn {
        width: 100%;
        padding: 15px;
        border-radius: 12px;
        font-weight: bold;
        border: 1px solid var(--glass-border);
        background: rgba(255, 255, 255, 0.05);
        color: var(--text-main);
        transition: 0.3s;
    }

    .glass-btn:hover {
        background: var(--primary);
        color: #000;
        transform: scale(1.05);
    }
</style>
