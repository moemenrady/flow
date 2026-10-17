<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content glass-modal text-white" style="background: #0d121d !important; border: 1px solid rgba(0, 242, 254, 0.3); border-radius: 20px;">

            <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                    <span style="font-size: 1.4rem;">➕</span> إضافة منتج / مشروب جديد
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>

            <form action="{{ route('products.store') }}" method="POST">
                @csrf

                <div class="modal-body py-4">

                    {{-- اختيار نوع المنتج --}}
                    <div class="p-3 mb-4 rounded-3" style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(0, 242, 254, 0.2);">
                        <label class="form-label fw-bold text-info mb-2">📌 نوع الصنف في الكافيه:</label>
                        <div class="d-flex gap-4 flex-wrap">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_produced" id="type_produced" value="1" checked onchange="toggleProductType(true)">
                                <label class="form-check-label text-white fw-semibold" for="type_produced" role="button">
                                    ☕ مشروب / طعام بريسبي (يحضر من خامات المخزن)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_produced" id="type_direct" value="0" onchange="toggleProductType(false)">
                                <label class="form-check-label text-white fw-semibold" for="type_direct" role="button">
                                    🥫 منتج مباشر جاهز (كانز، مياه، شيبس، شوكولاتة...)
                                </label>
                            </div>
                        </div>
                        <div id="recipeNotice" class="mt-2 text-muted small" style="color: #94a3b8 !important;">
                            💡 عند اختيار (بريسبي)، سيتم نقلك مباشرة بعد الحفظ لتحديد مقادير الخامات، وسيتم احتساب التكلفة تلقائياً.
                        </div>
                    </div>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label text-light fw-bold">اسم المنتج / المشروب <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control glass-input" placeholder="مثال: لاتيه، كابتشينو، شاي كرك..." required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-light fw-bold">معرف المنتج / الباركود <span class="text-muted small">(اختياري)</span></label>
                            <input type="number" name="product_id" class="form-control glass-input" placeholder="يتم توليده تلقائياً إذا ترك فارغاً">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-light fw-bold">القسم / التصنيف <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select glass-input" required>
                                <option value="" disabled selected>-- اختر القسم --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-light fw-bold">سعر البيع للعميل (ج.م) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="price" class="form-control glass-input" placeholder="0.00" required>
                        </div>

                        <div class="col-md-4 direct-only-fields" style="display: none;">
                            <label class="form-label text-light fw-bold">سعر التكلفة (ج.م)</label>
                            <input type="number" step="0.01" min="0" name="cost" id="inputCost" class="form-control glass-input" value="0">
                        </div>

                        <div class="col-md-4 direct-only-fields" style="display: none;">
                            <label class="form-label text-light fw-bold">الكمية الأولية بالمخزن</label>
                            <input type="number" min="0" name="quantity" id="inputQuantity" class="form-control glass-input" value="0">
                        </div>

                        <div class="col-md-4 direct-only-fields" style="display: none;">
                            <label class="form-label text-light fw-bold">الحد الأدنى للتنبيه</label>
                            <input type="number" min="0" name="min_quantity" class="form-control glass-input" value="5">
                        </div>

                    </div>

                </div>

                <div class="modal-footer border-top border-secondary border-opacity-25 pt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); border:none; color:#000;">
                        💾 حفظ المنتج والمتابعة
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
    function toggleProductType(isProduced) {
        const directFields = document.querySelectorAll('.direct-only-fields');
        const notice = document.getElementById('recipeNotice');
        if (isProduced) {
            directFields.forEach(el => el.style.display = 'none');
            notice.style.display = 'block';
        } else {
            directFields.forEach(el => el.style.display = 'block');
            notice.style.display = 'none';
        }
    }
</script>
