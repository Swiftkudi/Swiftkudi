@extends('layouts.app')

@section('title', 'Create Product - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('digital-products.my-products') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 flex items-center gap-2 transition-colors">
                <i class="fas fa-arrow-left"></i> Back to My Products
            </a>
        </div>

        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 sm:p-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Product</h1>
                <p class="mt-1 text-gray-500 dark:text-gray-400">List your digital or physical product on the marketplace</p>
            </div>

            <form method="POST" action="{{ route('digital-products.store') }}" enctype="multipart/form-data" id="productForm">
                @csrf

                <!-- Product Type -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Product Type *</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center justify-center p-4 border-2 border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-800 rounded-xl cursor-pointer transition-all product-type-option" data-value="digital">
                            <input type="radio" name="product_type" value="digital" checked class="sr-only">
                            <div class="text-center">
                                <i class="fas fa-file-download text-2xl mb-2 text-indigo-600 dark:text-indigo-400"></i>
                                <div class="font-semibold text-gray-900 dark:text-white">Digital</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Downloadable files</div>
                            </div>
                        </label>
                        <label class="flex items-center justify-center p-4 border-2 border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-800 rounded-xl cursor-pointer transition-all product-type-option" data-value="physical">
                            <input type="radio" name="product_type" value="physical" class="sr-only">
                            <div class="text-center">
                                <i class="fas fa-box text-2xl mb-2 text-indigo-600 dark:text-indigo-400"></i>
                                <div class="font-semibold text-gray-900 dark:text-white">Physical</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Shippable items</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Title -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Product Title *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                        class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        placeholder="e.g., Premium WordPress Theme">
                    @error('title')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Description *</label>
                    <textarea name="description" rows="5" required
                        class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        placeholder="Describe your product in detail...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category & Tags -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Category</label>
                        <select name="category_id" id="categorySelect" class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            <option value="">Select Category</option>
                            <!-- Digital categories (shown by default) -->
                            <optgroup label="Digital Products" id="digitalCategories">
                                @foreach($digitalCategories ?? [] as $category)
                                    <option value="{{ $category->id }}" data-type="digital" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </optgroup>
                            <!-- Physical categories -->
                            <optgroup label="Physical Products" id="physicalCategories">
                                @foreach($physicalCategories ?? [] as $category)
                                    <option value="{{ $category->id }}" data-type="physical" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </optgroup>
                            <option value="other" {{ old('category_id') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Tags</label>
                        <input type="text" name="tags" value="{{ old('tags') }}"
                            class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            placeholder="Comma-separated tags">
                    </div>
                </div>

                <!-- Pricing -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Price (₦) *</label>
                        <input type="number" name="price" value="{{ old('price', 0) }}" min="0" step="0.01" required
                            class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Sale Price (₦)</label>
                        <input type="number" name="sale_price" value="{{ old('sale_price') }}" min="0" step="0.01"
                            class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            placeholder="Optional discount price">
                    </div>
                </div>

                <!-- Free Product -->
                <div class="mb-6 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl border border-indigo-100 dark:border-indigo-800">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_free" value="1" {{ old('is_free') ? 'checked' : '' }} id="isFreeCheckbox"
                            class="rounded text-indigo-600 focus:ring-indigo-500 w-5 h-5">
                        <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">This is a free product</span>
                    </label>
                </div>

                <!-- License Type (Digital Only) -->
                <div class="mb-6 license-section">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">License Type *</label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="flex flex-col items-center p-3 border-2 border-gray-200 dark:border-dark-700 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-800 transition-all license-option" data-value="1">
                            <input type="radio" name="license_type" value="1" checked class="sr-only">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Personal</span>
                            <span class="text-xs text-gray-500 mt-1">Single user</span>
                        </label>
                        <label class="flex flex-col items-center p-3 border-2 border-gray-200 dark:border-dark-700 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-800 transition-all license-option" data-value="2">
                            <input type="radio" name="license_type" value="2" class="sr-only">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Commercial</span>
                            <span class="text-xs text-gray-500 mt-1">Multiple users</span>
                        </label>
                        <label class="flex flex-col items-center p-3 border-2 border-gray-200 dark:border-dark-700 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-800 transition-all license-option" data-value="3">
                            <input type="radio" name="license_type" value="3" class="sr-only">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Extended</span>
                            <span class="text-xs text-gray-500 mt-1">Unlimited</span>
                        </label>
                    </div>
                </div>

                <!-- Physical Product Fields -->
                <div class="mb-6 physical-fields hidden">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Stock Quantity</label>
                            <input type="number" name="stock_quantity" value="{{ old('stock_quantity', 1) }}" min="0"
                                class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                placeholder="Available stock">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">SKU</label>
                            <input type="text" name="sku" value="{{ old('sku') }}"
                                class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                placeholder="Product SKU">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Shipping Info</label>
                        <textarea name="shipping_info" rows="2"
                            class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            placeholder="Shipping details, delivery time, etc.">{{ old('shipping_info') }}</textarea>
                    </div>
                </div>

                <!-- Version & Requirements (Digital Only) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 license-section">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Version</label>
                        <input type="text" name="version" value="{{ old('version', '1.0') }}"
                            class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            placeholder="e.g., 1.0">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Requirements</label>
                        <input type="text" name="requirements" value="{{ old('requirements') }}"
                            class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            placeholder="e.g., PHP 7.4+, WordPress 5.0+">
                    </div>
                </div>

                <!-- Thumbnail Upload -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Thumbnail Image</label>
                    <div class="border-2 border-dashed border-gray-300 dark:border-dark-700 rounded-xl p-6 text-center hover:border-indigo-500 transition-colors cursor-pointer" id="thumbnailDropZone">
                        <input type="file" name="thumbnail" id="thumbnailInput" accept="image/*" class="hidden">
                        <div id="thumbnailPreview" class="hidden mb-4">
                            <img src="" alt="Preview" class="max-h-32 mx-auto rounded-lg">
                        </div>
                        <div id="thumbnailPlaceholder">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                            <p class="text-gray-600 dark:text-gray-400">Click to upload or drag and drop</p>
                            <p class="text-xs text-gray-500 mt-1">PNG, JPG up to 2MB</p>
                        </div>
                    </div>
                    @error('thumbnail')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- File Upload (Digital Only) -->
                <div class="mb-6 license-section">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Product File *</label>
                    <div class="border-2 border-dashed border-gray-300 dark:border-dark-700 rounded-xl p-6 text-center hover:border-indigo-500 transition-colors cursor-pointer" id="fileDropZone">
                        <input type="file" name="file" id="fileInput" class="hidden">
                        <div id="filePreview" class="hidden mb-4">
                            <i class="fas fa-file text-4xl text-indigo-500"></i>
                            <p class="text-gray-700 dark:text-gray-300 mt-2" id="fileName"></p>
                        </div>
                        <div id="filePlaceholder">
                            <i class="fas fa-file-upload text-4xl text-gray-400 mb-3"></i>
                            <p class="text-gray-600 dark:text-gray-400">Click to upload or drag and drop</p>
                            <p class="text-xs text-gray-500 mt-1">ZIP, RAR, PDF up to 50MB</p>
                        </div>
                    </div>
                    @error('file')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Changelog -->
                <div class="mb-6 license-section">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Changelog</label>
                    <textarea name="changelog" rows="3"
                        class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        placeholder="What's new in this version?">{{ old('changelog') }}</textarea>
                </div>

                <!-- Submit -->
                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-gray-200 dark:border-dark-700">
                    <a href="{{ route('digital-products.my-products') }}" class="px-6 py-3 border border-gray-200 dark:border-dark-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-dark-800 transition-colors font-medium text-center">
                        Cancel
                    </a>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:shadow-lg hover:shadow-indigo-500/30 transition-all font-medium">
                        <i class="fas fa-plus mr-2"></i> Create Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Product Type Selection
    document.querySelectorAll('.product-type-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.product-type-option').forEach(o => {
                o.classList.remove('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-900/20');
                o.classList.add('border-gray-200', 'dark:border-dark-700', 'bg-white', 'dark:bg-dark-800');
            });
            this.classList.remove('border-gray-200', 'dark:border-dark-700', 'bg-white', 'dark:bg-dark-800');
            this.classList.add('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-900/20');
            
            const type = this.dataset.value;
            const licenseSections = document.querySelectorAll('.license-section');
            const physicalFields = document.querySelectorAll('.physical-fields');
            
            if (type === 'digital') {
                licenseSections.forEach(s => s.classList.remove('hidden'));
                physicalFields.forEach(s => s.classList.add('hidden'));
                document.getElementById('fileInput').required = true;
            } else {
                licenseSections.forEach(s => s.classList.add('hidden'));
                physicalFields.forEach(s => s.classList.remove('hidden'));
                document.getElementById('fileInput').required = false;
            }
        });
    });
    
    // Initialize first option
    document.querySelector('.product-type-option[data-value="digital"]').click();

    // License Type Selection
    document.querySelectorAll('.license-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.license-option').forEach(o => {
                o.classList.remove('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-900/20');
                o.classList.add('border-gray-200', 'dark:border-dark-700');
            });
            this.classList.remove('border-gray-200', 'dark:border-dark-700');
            this.classList.add('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-900/20');
        });
    });
    
    // Initialize first license option
    document.querySelector('.license-option[data-value="1"]').click();

    // Thumbnail Upload
    const thumbnailDropZone = document.getElementById('thumbnailDropZone');
    const thumbnailInput = document.getElementById('thumbnailInput');
    const thumbnailPreview = document.getElementById('thumbnailPreview');
    const thumbnailPlaceholder = document.getElementById('thumbnailPlaceholder');

    thumbnailDropZone.addEventListener('click', () => thumbnailInput.click());
    
    thumbnailDropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        thumbnailDropZone.classList.add('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-900/20');
    });
    
    thumbnailDropZone.addEventListener('dragleave', () => {
        thumbnailDropZone.classList.remove('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-900/20');
    });
    
    thumbnailDropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        thumbnailDropZone.classList.remove('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-900/20');
        if (e.dataTransfer.files.length) {
            thumbnailInput.files = e.dataTransfer.files;
            showThumbnailPreview(e.dataTransfer.files[0]);
        }
    });
    
    thumbnailInput.addEventListener('change', (e) => {
        if (e.target.files.length) {
            showThumbnailPreview(e.target.files[0]);
        }
    });

    function showThumbnailPreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            thumbnailPreview.querySelector('img').src = e.target.result;
            thumbnailPreview.classList.remove('hidden');
            thumbnailPlaceholder.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }

    // File Upload
    const fileDropZone = document.getElementById('fileDropZone');
    const fileInput = document.getElementById('fileInput');
    const filePreview = document.getElementById('filePreview');
    const filePlaceholder = document.getElementById('filePlaceholder');
    const fileName = document.getElementById('fileName');

    fileDropZone.addEventListener('click', () => fileInput.click());
    
    fileDropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        fileDropZone.classList.add('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-900/20');
    });
    
    fileDropZone.addEventListener('dragleave', () => {
        fileDropZone.classList.remove('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-900/20');
    });
    
    fileDropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        fileDropZone.classList.remove('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-900/20');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            showFilePreview(e.dataTransfer.files[0]);
        }
    });
    
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length) {
            showFilePreview(e.target.files[0]);
        }
    });

    function showFilePreview(file) {
        fileName.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
        filePreview.classList.remove('hidden');
        filePlaceholder.classList.add('hidden');
    }

    // Free product toggle
    document.getElementById('isFreeCheckbox').addEventListener('change', function() {
        const priceInput = document.querySelector('input[name="price"]');
        const salePriceInput = document.querySelector('input[name="sale_price"]');
        if (this.checked) {
            priceInput.value = '0';
            priceInput.readOnly = true;
            salePriceInput.readOnly = true;
        } else {
            priceInput.readOnly = false;
            salePriceInput.readOnly = false;
        }
    });
</script>
@endpush
@endsection
