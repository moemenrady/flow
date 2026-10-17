<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content glass-modal">

            <div class="modal-header modal-header-primary">
                <h5 class="modal-title">📂 إدارة الأصناف</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- 1. إضافة صنف جديد (Create) --}}
                <div class="glass-box mb-4">
                    <h6 class="section-title">➕ إضافة صنف جديد</h6>

                    <form action="{{ route('categories.store') }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="name" class="form-control glass-input"
                                placeholder="اسم الصنف الجديد..." required>

                            <button class="btn btn-glow-primary" type="submit">
                                <i class="fas fa-plus me-1"></i> حفظ
                            </button>
                        </div>
                    </form>
                </div>

                {{-- 2. قائمة وعمليات الأصناف (Read + Update + Delete) --}}
                <h6 class="section-title">📋 الأصناف الحالية</h6>

                <div class="table-responsive categories-scroll">
                    <table class="table table-dark table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 10%">#</th>
                                <th style="width: 60%">اسم الصنف</th>
                                <th style="width: 30%" class="text-center">العمليات</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($categories as $category)
                                <tr>
                                    <td class="text-muted">{{ $loop->iteration }}</td>

                                    <td>
                                        {{-- وضع القراءة (Read Mode) --}}
                                        <span id="category-text-{{ $category->id }}"
                                            class="fw-bold category-display text-white">
                                            {{ $category->name }}
                                        </span>

                                        {{-- وضع التعديل (Edit Mode - مخفي افتراضياً) --}}
                                        <form id="category-form-{{ $category->id }}"
                                            action="{{ route('categories.update', $category->id) }}" method="POST"
                                            class="d-none m-0">
                                            @csrf
                                            @method('PUT')
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="name"
                                                    class="form-control glass-input py-1" value="{{ $category->name }}"
                                                    required>
                                                <button type="submit" class="btn btn-success btn-sm px-3"
                                                    title="حفظ التعديل">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-secondary btn-sm px-3"
                                                    onclick="toggleEditMode({{ $category->id }})" title="إلغاء">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </td>

                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2"
                                            id="action-buttons-{{ $category->id }}">
                                            {{-- زر التعديل (Update) --}}
                                            <button class="btn btn-warning btn-sm rounded-circle category-action-btn"
                                                onclick="toggleEditMode({{ $category->id }})" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            {{-- زر الحذف (Delete) --}}
                                            <form action="{{ route('categories.destroy', $category->id) }}"
                                                method="POST" class="m-0"
                                                onsubmit="return confirm('هل أنت متأكد من حذف هذا الصنف؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger btn-sm rounded-circle category-action-btn"
                                                    title="حذف">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        ❌ لا توجد أصناف مضافة حالياً
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>

            <div class="modal-footer border-0">
                <button class="btn btn-outline-light w-100" data-bs-dismiss="modal">
                    إغلاق النافذة
                </button>
            </div>

        </div>
    </div>
</div>

<style>
    .glass-modal {
        background: rgba(16, 20, 30, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 18px;
        color: #f0f4f8;
    }

    .modal-header-primary {
        background: linear-gradient(135deg, #00f2fe, #4facfe);
        color: #000;
        font-weight: bold;
    }

    .glass-box {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 14px;
        padding: 15px;
    }

    .section-title {
        color: #00f2fe;
        margin-bottom: 10px;
        font-weight: 700;
    }

    .glass-input {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: white !important;
    }

    .glass-input:focus {
        border-color: #00f2fe !important;
        box-shadow: 0 0 10px rgba(0, 242, 254, 0.3) !important;
        background: rgba(255, 255, 255, 0.08) !important;
    }

    .btn-glow-primary {
        background: linear-gradient(135deg, #00f2fe, #4facfe);
        border: none;
        color: #000;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .btn-glow-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 242, 254, 0.4);
        color: #000;
    }

    .category-action-btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s;
    }

    .category-action-btn:hover {
        transform: scale(1.1);
    }

    .categories-scroll {
        max-height: 280px;
        overflow-y: auto;
    }

    /* تخصيص السكرول بار ليناسب التصميم المظلم */
    .categories-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .categories-scroll::-webkit-scrollbar-thumb {
        background: rgba(0, 242, 254, 0.2);
        border-radius: 4px;
    }

    .categories-scroll::-webkit-scrollbar-thumb:hover {
        background: rgba(0, 242, 254, 0.5);
    }
</style>

{{-- سكربت التنقل بين العرض والتعديل السريع --}}
<script>
    function toggleEditMode(id) {
        const textSpan = document.getElementById(`category-text-${id}`);
        const editForm = document.getElementById(`category-form-${id}`);
        const actionButtons = document.getElementById(`action-buttons-${id}`);

        if (editForm.classList.contains('d-none')) {
            // تحويل إلى وضع التعديل
            textSpan.classList.add('d-none');
            editForm.classList.remove('d-none');
            actionButtons.classList.add('opacity-50', 'pe-none'); // تعطيل أزرار الحذف مؤقتاً
        } else {
            // العودة لوضع القراءة
            textSpan.classList.remove('d-none');
            editForm.classList.add('d-none');
            actionButtons.classList.remove('opacity-50', 'pe-none');
        }
    }
</script>
